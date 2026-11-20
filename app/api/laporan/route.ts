import { NextResponse } from 'next/server';
import type { ResultSetHeader } from 'mysql2/promise';
import { dbSiap, transaksi } from '@/lib/db';
import { penggunaSaatIni } from '@/lib/auth';
import { deteksiHatespeech, type HasilAI } from '@/lib/model';
import { BENTUK, FREKUENSI, MAKS_KARAKTER, TEMPAT } from '@/lib/kampus';
import { hitungPrioritas, pilihPasal } from '@/lib/pasal';

export const maxDuration = 30;

const MAKS_GAMBAR = 3;
const MAKS_UKURAN = 1024 * 1024; // 1 MB per gambar setelah diperkecil di browser; 3 gambar tetap di bawah batas kiriman Vercel (~4,5 MB)
const MIME_BOLEH = ['image/jpeg', 'image/png', 'image/webp'];

function buatKode(): string {
  // tanggal menurut WIB, karena server Vercel berjalan dalam UTC
  const tgl = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta' }).format(new Date()).replace(/-/g, '');
  const acak = Array.from(crypto.getRandomValues(new Uint8Array(4)), (b) => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[b % 32]).join('');
  return `LP-${tgl}-${acak}`;
}

const gagal = (galat: string, status = 400) => NextResponse.json({ ok: false, galat }, { status });

/** Mengenali gambar dari isi berkas (bukan dari nama/tipe yang dikirim browser). */
function tipeGambar(b: Buffer): string | null {
  if (b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff) return 'image/jpeg';
  if (b.subarray(0, 8).equals(Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]))) return 'image/png';
  if (b.subarray(0, 4).toString('ascii') === 'RIFF' && b.subarray(8, 12).toString('ascii') === 'WEBP') return 'image/webp';
  return null;
}

/**
 * Menyimpan laporan baru dari formulir (multipart/form-data: field "data" berisi JSON, field "gambar" berisi berkas).
 * Hasil AI dihitung ulang di server agar tidak bisa dipalsukan dari browser.
 */
export async function POST(req: Request) {
  if (!dbSiap) return gagal('Database belum terhubung.', 503);
  const user = await penggunaSaatIni();
  if (!user) return gagal('Sesi berakhir. Masuk lagi untuk mengirim laporan.', 401);
  if (user.role !== 'mahasiswa') return gagal('Hanya akun mahasiswa yang dapat mengirim laporan.', 403);

  const form = await req.formData().catch(() => null);
  if (!form) return gagal('Data laporan tidak terbaca.');
  let m: Record<string, unknown>;
  try { m = JSON.parse(String(form.get('data') ?? '{}')); } catch { return gagal('Data laporan tidak terbaca.'); }

  // ---- validasi ----
  const str = (k: string) => (typeof m[k] === 'string' ? (m[k] as string).trim() : '');
  const peran = str('peran');
  const tanggal = str('tanggal');
  const tempatPilih = str('tempat');
  const tempat = tempatPilih === 'Lainnya' ? str('tempatLain') : tempatPilih;
  const frekuensi = str('frekuensi');
  const bentuk = (Array.isArray(m.bentuk) ? m.bentuk : []).filter((b): b is string => (BENTUK as readonly string[]).includes(b as string));
  const pelaku = str('pelaku').slice(0, 120);
  const kronologi = str('kronologi');
  const bukti = str('bukti');

  if (!['korban', 'saksi'].includes(peran)) return gagal('Pilih kamu melapor sebagai korban atau saksi.');
  if (!/^\d{4}-\d{2}-\d{2}$/.test(tanggal) || new Date(tanggal) > new Date()) return gagal('Tanggal kejadian tidak valid.');
  if (!(TEMPAT as readonly string[]).includes(tempatPilih) || !tempat || tempat.length > 80) return gagal('Pilih tempat atau aplikasi kejadian.');
  if (!(FREKUENSI as readonly string[]).includes(frekuensi)) return gagal('Frekuensi tidak valid.');
  if (!bentuk.length) return gagal('Pilih minimal satu bentuk perundungan.');
  if (!kronologi || kronologi.length > 1500) return gagal('Kronologi wajib diisi, maksimal 1.500 karakter.');
  if (bukti.length > MAKS_KARAKTER) return gagal('Teks bukti terlalu panjang.');
  if (m.setuju !== true) return gagal('Centang pernyataan kebenaran laporan.');

  const gambar: { nama: string; mime: string; data: Buffer }[] = [];
  for (const f of form.getAll('gambar').slice(0, MAKS_GAMBAR)) {
    if (!(f instanceof File)) continue;
    if (f.size > MAKS_UKURAN) return gagal(`Gambar ${f.name} terlalu besar (maksimal 1 MB).`);
    const data = Buffer.from(await f.arrayBuffer());
    const mime = tipeGambar(data);
    if (!mime || !MIME_BOLEH.includes(mime)) return gagal(`${f.name} bukan gambar JPG, PNG atau WebP.`);
    gambar.push({ nama: f.name.slice(0, 120), mime, data });
  }
  if (!bukti && !gambar.length) return gagal('Tempel teks bukti atau lampirkan tangkapan layar.');

  // ---- pemeriksaan AI (boleh gagal; laporan tetap tersimpan) ----
  let ai: HasilAI | null = null;
  if (bukti) {
    try { ai = await deteksiHatespeech(bukti); } catch { ai = null; }
  }
  const pasal = pilihPasal(ai, bentuk);
  const kode = buatKode();

  // ---- simpan dalam satu transaksi ----
  try {
    await transaksi(async (c) => {
      const [lap] = await c.query<ResultSetHeader>(
        `INSERT INTO laporan (kode, pelapor_id, peran, tanggal_kejadian, tempat, frekuensi, terduga_pelaku, kronologi, prioritas)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [kode, user.id, peran, tanggal, tempat, frekuensi, pelaku || null, kronologi, hitungPrioritas(ai, bentuk)],
      );
      const id = lap.insertId;
      await c.query('INSERT INTO laporan_bentuk (laporan_id, bentuk) VALUES ?', [bentuk.map((b) => [id, b])]);
      if (bukti) {
        await c.query(
          'INSERT INTO bukti (laporan_id, isi_teks, ai_label, skor_hatespeech, skor_abusive, temuan, model) VALUES (?, ?, ?, ?, ?, ?, ?)',
          [id, bukti, ai?.label ?? null, ai?.hs ?? null, ai?.ab ?? null, JSON.stringify(ai?.temuan ?? []), ai?.model ?? null],
        );
      }
      for (const g of gambar) {
        await c.query('INSERT INTO lampiran (laporan_id, nama_file, mime, ukuran, data) VALUES (?, ?, ?, ?, ?)', [id, g.nama, g.mime, g.data.length, g.data]);
      }
      await c.query('INSERT INTO laporan_pasal (laporan_id, pasal_kode, alasan) VALUES ?', [pasal.map((p) => [id, p.kode, p.alasan.join('; ').slice(0, 500)])]);
      await c.query('INSERT INTO riwayat_status (laporan_id, status, catatan, oleh) VALUES (?, ?, ?, ?)', [id, 'terkirim', 'Laporan dikirim pelapor.', user.id]);
    });
  } catch (e) {
    console.error('Gagal menyimpan laporan', e);
    return gagal('Laporan gagal disimpan. Coba lagi beberapa saat lagi.', 500);
  }

  return NextResponse.json({ ok: true, kode, aiTersedia: Boolean(ai) || !bukti });
}
