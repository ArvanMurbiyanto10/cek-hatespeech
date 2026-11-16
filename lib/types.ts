import type { StatusLaporan } from './kampus';

export type Role = 'mahasiswa' | 'satgas';

export interface Profil {
  id: string;
  role: Role;
  nama: string;
  nim: string | null;
  prodi: string | null;
  angkatan: number | null;
  kontak: string | null;
  email: string | null;
  dibuat: string;
}

export interface Laporan {
  id: string;
  kode: string;
  pelapor_id: string;
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
  id: string;
  laporan_id: string;
  isi_teks: string;
  ai_label: 'hate' | 'tinjau' | 'abusive' | 'aman' | null;
  skor_hatespeech: number | null;
  skor_abusive: number | null;
  temuan: { kata: string; tipe: string; kode: string }[];
  model: string | null;
  dibuat: string;
}

export interface Lampiran {
  id: string;
  laporan_id: string;
  path: string;
  dibuat: string;
}

export interface LaporanPasal {
  laporan_id: string;
  pasal_kode: string;
  alasan: string;
}

export interface Riwayat {
  id: number;
  laporan_id: string;
  status: string;
  catatan: string;
  oleh: string | null;
  dibuat: string;
}

/** Laporan lengkap untuk halaman detail dan PDF. */
export interface LaporanLengkap extends Laporan {
  pelapor: Pick<Profil, 'nama' | 'nim' | 'prodi' | 'angkatan' | 'kontak' | 'email'> | null;
  bukti: Bukti[];
  lampiran: (Lampiran & { url: string | null })[];
  laporan_pasal: LaporanPasal[];
  riwayat_status: Riwayat[];
}
