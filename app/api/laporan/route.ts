import { NextResponse } from 'next/server';
import { supabaseServer } from '@/lib/supabase/server';
import { supabaseSiap } from '@/lib/supabase/konfigurasi';
import { deteksiHatespeech, type HasilAI } from '@/lib/model';
import { BENTUK, FREKUENSI, MAKS_KARAKTER, TEMPAT } from '@/lib/kampus';
import { hitungPrioritas, pilihPasal } from '@/lib/pasal';

export const maxDuration = 30;

interface Masukan {
  id: string;
  peran: string;
  tanggal: string;
  tempat: string;
  tempatLain?: string;
  frekuensi: string;
  bentuk: string[];
  pelaku?: string;
  kronologi: string;
  bukti?: string;
  lampiran: string[];
  setuju: boolean;
}

const UUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

function buatKode(): string {
  const d = new Date();
  const tgl = `${d.getFullYear()}${String(d.getMonth() + 1).padStart(2, '0')}${String(d.getDate()).padStart(2, '0')}`;
  const acak = Array.from(crypto.getRandomValues(new Uint8Array(4)), (b) => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[b % 32]).join('');
  return `LP-${tgl}-${acak}`;
}

function gagal(galat: string, status = 400) {
  return NextResponse.json({ ok: false, galat }, { status });
}

/**
 * Menyimpan laporan baru. Semua query memakai sesi pelapor, jadi aturan RLS di database tetap berlaku.
 * Hasil AI dihitung ulang di server agar tidak bisa dipalsukan dari browser.
 */
export async function POST(req: Request) {
  if (!supabaseSiap) return gagal('Database belum terhubung. Hubungi pengelola aplikasi.', 503);
  const sb = await supabaseServer();
  const { data: auth } = await sb.auth.getUser();
  if (!auth.user) return gagal('Sesi berakhir. Masuk lagi untuk mengirim laporan.', 401);
  const uid = auth.user.id;

  const { data: profil } = await sb.from('profiles').select('role').eq('id', uid).maybeSingle();
  if (profil?.role !== 'mahasiswa') return gagal('Hanya akun mahasiswa yang dapat mengirim laporan.', 403);

  const m = (await req.json().catch(() => null)) as Masukan | null;
  if (!m) return gagal('Data laporan tidak terbaca.');

  // ---- validasi ----
  const kronologi = (m.kronologi ?? '').trim();
  const bukti = (m.bukti ?? '').trim();
  const bentuk = (Array.isArray(m.bentuk) ? m.bentuk : []).filter((b) => (BENTUK as readonly string[]).includes(b));
  const tempat = m.tempat === 'Lainnya' ? (m.tempatLain ?? '').trim() : m.tempat;
  const lampiran = (Array.isArray(m.lampiran) ? m.lampiran : []).slice(0, 3);
  if (!UUID.test(m.id ?? '')) return gagal('ID laporan tidak valid.');
  if (!['korban', 'saksi'].includes(m.peran)) return gagal('Pilih kamu melapor sebagai korban atau saksi.');
  if (!/^\d{4}-\d{2}-\d{2}$/.test(m.tanggal ?? '') || new Date(m.tanggal) > new Date()) return gagal('Tanggal kejadian tidak valid.');
  if (!(TEMPAT as readonly string[]).includes(m.tempat) || !tempat) return gagal('Pilih tempat atau aplikasi kejadian.');
  if (!(FREKUENSI as readonly string[]).includes(m.frekuensi)) return gagal('Frekuensi tidak valid.');
  if (!bentuk.length) return gagal('Pilih minimal satu bentuk perundungan.');
  if (!kronologi || kronologi.length > 1500) return gagal('Kronologi wajib diisi, maksimal 1.500 karakter.');
  if (bukti.length > MAKS_KARAKTER) return gagal('Teks bukti terlalu panjang.');
  if (!bukti && !lampiran.length) return gagal('Tempel teks bukti atau lampirkan tangkapan layar.');
  if (lampiran.some((p) => typeof p !== 'string' || !p.startsWith(`${uid}/${m.id}/`))) return gagal('Lampiran tidak valid.');
  if (m.setuju !== true) return gagal('Centang pernyataan kebenaran laporan.');

  // ---- pemeriksaan AI (boleh gagal; laporan tetap tersimpan) ----
  let ai: HasilAI | null = null;
  if (bukti) {
    try {
      ai = await deteksiHatespeech(bukti);
    } catch {
      ai = null;
    }
  }
  const pasal = pilihPasal(ai, bentuk);
  const kode = buatKode();

  // ---- simpan ----
  const { error: e1 } = await sb.from('laporan').insert({
    id: m.id,
    kode,
    pelapor_id: uid,
    peran: m.peran,
    tanggal_kejadian: m.tanggal,
    tempat,
    frekuensi: m.frekuensi,
    bentuk,
    terduga_pelaku: (m.pelaku ?? '').trim().slice(0, 120) || null,
    kronologi,
    prioritas: hitungPrioritas(ai, bentuk),
  });
  if (e1) return gagal(`Laporan gagal disimpan: ${e1.message}`, 500);

  const anak = await Promise.all([
    bukti
      ? sb.from('bukti').insert({
          laporan_id: m.id,
          isi_teks: bukti,
          ai_label: ai?.label ?? null,
          skor_hatespeech: ai?.hs ?? null,
          skor_abusive: ai?.ab ?? null,
          temuan: ai?.temuan ?? [],
          model: ai?.model ?? null,
        })
      : Promise.resolve({ error: null }),
    lampiran.length ? sb.from('lampiran').insert(lampiran.map((path) => ({ laporan_id: m.id, path }))) : Promise.resolve({ error: null }),
    sb.from('laporan_pasal').insert(pasal.map((p) => ({ laporan_id: m.id, pasal_kode: p.kode, alasan: p.alasan.join('; ') }))),
    sb.from('riwayat_status').insert({ laporan_id: m.id, status: 'terkirim', catatan: 'Laporan dikirim pelapor.', oleh: uid }),
  ]);
  const galatAnak = anak.find((r) => r.error)?.error;
  if (galatAnak) {
    await sb.from('laporan').delete().eq('id', m.id); // batalkan agar tidak ada laporan setengah jadi
    return gagal(`Laporan gagal disimpan: ${galatAnak.message}`, 500);
  }

  return NextResponse.json({ ok: true, kode, aiTersedia: Boolean(ai) || !bukti });
}
