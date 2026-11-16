import 'server-only';
import { bacaJson, pilih, satu } from './db';
import type { Bukti, Lampiran, Laporan, LaporanLengkap, LaporanPasal, Pengguna, Riwayat } from './types';

/** Kolom ringkas laporan + daftar bentuk (digabung dari tabel laporan_bentuk). */
const KOLOM_LAPORAN = `l.id, l.kode, l.pelapor_id, l.peran, l.tanggal_kejadian, l.tempat, l.frekuensi, l.terduga_pelaku,
  l.kronologi, l.status, l.prioritas, l.dibuat, l.diubah,
  (SELECT GROUP_CONCAT(b.bentuk ORDER BY b.bentuk SEPARATOR '||') FROM laporan_bentuk b WHERE b.laporan_id = l.id) AS bentuk_gabung`;

type BarisLaporan = Omit<Laporan, 'bentuk'> & { bentuk_gabung: string | null };

function rapikan(b: BarisLaporan): Laporan {
  const { bentuk_gabung, ...sisa } = b;
  return {
    ...sisa,
    dibuat: new Date(sisa.dibuat).toISOString(),
    diubah: new Date(sisa.diubah).toISOString(),
    bentuk: bentuk_gabung ? bentuk_gabung.split('||') : [],
  };
}

/** Laporan milik seorang mahasiswa (untuk halaman Laporan saya). */
export async function laporanMilik(uid: number): Promise<Laporan[]> {
  const baris = await pilih<BarisLaporan>(`SELECT ${KOLOM_LAPORAN} FROM laporan l WHERE l.pelapor_id = ? ORDER BY l.dibuat DESC`, [uid]);
  return baris.map(rapikan);
}

/** Semua laporan beserta nama pelapor (hanya dipanggil untuk role Satgas). */
export async function semuaLaporan(): Promise<(Laporan & { pelapor_nama: string; pelapor_nim: string | null })[]> {
  const baris = await pilih<BarisLaporan & { pelapor_nama: string; pelapor_nim: string | null }>(
    `SELECT ${KOLOM_LAPORAN}, u.nama AS pelapor_nama, u.nim AS pelapor_nim
     FROM laporan l JOIN users u ON u.id = l.pelapor_id ORDER BY l.dibuat DESC`,
  );
  return baris.map((b) => ({ ...rapikan(b), pelapor_nama: b.pelapor_nama, pelapor_nim: b.pelapor_nim }));
}

/**
 * Satu laporan lengkap. Hak akses: Satgas boleh melihat semua; mahasiswa hanya laporannya sendiri.
 * Mengembalikan null bila tidak ada atau tidak berhak (keduanya tampil sebagai "tidak ditemukan").
 */
export async function ambilLaporanLengkap(kode: string, user: Pick<Pengguna, 'id' | 'role'>): Promise<LaporanLengkap | null> {
  const baris = await satu<BarisLaporan>(`SELECT ${KOLOM_LAPORAN} FROM laporan l WHERE l.kode = ?`, [kode]);
  if (!baris) return null;
  if (user.role !== 'satgas' && baris.pelapor_id !== user.id) return null;
  const lap = rapikan(baris);

  const [pelapor, bukti, lampiran, pasal, riwayat] = await Promise.all([
    satu<LaporanLengkap['pelapor']>('SELECT nama, nim, prodi, angkatan, kontak, email FROM users WHERE id = ?', [lap.pelapor_id]),
    pilih<Bukti>('SELECT id, laporan_id, isi_teks, ai_label, skor_hatespeech, skor_abusive, temuan, model, dibuat FROM bukti WHERE laporan_id = ? ORDER BY id', [lap.id]),
    pilih<Omit<Lampiran, 'url'>>('SELECT id, laporan_id, nama_file, mime, ukuran FROM lampiran WHERE laporan_id = ? ORDER BY id', [lap.id]),
    pilih<LaporanPasal>('SELECT laporan_id, pasal_kode, alasan FROM laporan_pasal WHERE laporan_id = ?', [lap.id]),
    pilih<Riwayat>(
      'SELECT r.id, r.laporan_id, r.status, r.catatan, r.oleh, u.nama AS oleh_nama, r.dibuat FROM riwayat_status r LEFT JOIN users u ON u.id = r.oleh WHERE r.laporan_id = ? ORDER BY r.dibuat, r.id',
      [lap.id],
    ),
  ]);

  const URUT = ['28-2', '29', '27a', 'kampus'];
  return {
    ...lap,
    pelapor: pelapor ?? { nama: '-', nim: null, prodi: null, angkatan: null, kontak: null, email: '-' },
    bukti: bukti.map((b) => ({ ...b, temuan: bacaJson(b.temuan, []), dibuat: new Date(b.dibuat).toISOString() })),
    lampiran: lampiran.map((l) => ({ ...l, url: `/api/lampiran/${l.id}` })),
    laporan_pasal: pasal.sort((a, b) => URUT.indexOf(a.pasal_kode) - URUT.indexOf(b.pasal_kode)),
    riwayat_status: riwayat.map((r) => ({ ...r, dibuat: new Date(r.dibuat).toISOString() })),
  };
}
