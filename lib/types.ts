import type { StatusLaporan } from './kampus';

export type Role = 'mahasiswa' | 'satgas';

export interface Pengguna {
  id: number;
  role: Role;
  nama: string;
  nim: string | null;
  prodi: string | null;
  angkatan: number | null;
  kontak: string | null;
  email: string;
  dibuat: string;
}

export interface Laporan {
  id: number;
  kode: string;
  pelapor_id: number;
  peran: 'korban' | 'saksi';
  tanggal_kejadian: string;
  tempat: string;
  frekuensi: string | null;
  bentuk: string[];
  terduga_pelaku: string | null;
  kronologi: string;
  status: StatusLaporan;
  prioritas: 'tinggi' | 'sedang' | 'normal';
  dibuat: string;
  diubah: string;
}

export interface Bukti {
  id: number;
  laporan_id: number;
  isi_teks: string;
  ai_label: 'hate' | 'tinjau' | 'abusive' | 'aman' | null;
  skor_hatespeech: number | null;
  skor_abusive: number | null;
  temuan: { kata: string; tipe: string; kode: string }[];
  model: string | null;
  dibuat: string;
}

export interface Lampiran {
  id: number;
  laporan_id: number;
  nama_file: string;
  mime: string;
  ukuran: number;
  /** Rute yang mengecek hak akses sebelum mengirim gambar. */
  url: string;
}

export interface LaporanPasal {
  laporan_id: number;
  pasal_kode: string;
  alasan: string;
}

export interface Riwayat {
  id: number;
  laporan_id: number;
  status: string;
  catatan: string;
  oleh: number | null;
  oleh_nama: string | null;
  dibuat: string;
}

/** Laporan lengkap untuk halaman detail dan PDF. */
export interface LaporanLengkap extends Laporan {
  pelapor: Pick<Pengguna, 'nama' | 'nim' | 'prodi' | 'angkatan' | 'kontak' | 'email'>;
  bukti: Bukti[];
  lampiran: Lampiran[];
  laporan_pasal: LaporanPasal[];
  riwayat_status: Riwayat[];
}
