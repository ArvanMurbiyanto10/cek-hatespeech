/**
 * Data tetap aplikasi: kampus tujuan laporan, pengembang, dan pasal yang mungkin terkait.
 * Kontak Satgas diisi HANYA dari kanal resmi Telkom University Purwokerto. String kosong tidak ditampilkan.
 */

export const KAMPUS = {
  nama: 'Telkom University Purwokerto',
  tujuan: 'Satgas PPKPT Telkom University Purwokerto',
  tujuanLengkap: 'Satuan Tugas Pencegahan dan Penanganan Kekerasan di Perguruan Tinggi (Satgas PPKPT)',
  email: '', // contoh: 'satgasppkpt@...'  (isi dari kanal resmi)
  whatsapp: '', // contoh: '08xx-xxxx-xxxx'    (isi dari kanal resmi)
  tempat: '', // contoh: 'Gedung ..., lantai ...'
} as const;

export const PENGEMBANG = {
  judul: 'Sistem Cerdas Pelaporan dan Identifikasi Kalimat Ujaran Kebencian Berbasis Web Merujuk UU ITE',
  nama: 'Arvan Murbiyanto',
  nim: '2311102074',
  prodi: 'Teknik Informatika',
  kampus: 'Telkom University Purwokerto',
  pembimbing: '',
  tahun: '2026',
} as const;

export const MAKS_KARAKTER = 2000;

export type KodePasal = '28-2' | '29' | '27a' | 'kampus';

export interface Pasal {
  nama: string;
  judul: string;
  isi: string;
  ancaman: string;
  catatan: string;
  tautan: string;
}

/** Teks sama dengan halaman /dasar-hukum dan tabel `pasal` di database (database/schema.sql). */
export const PASAL: Record<KodePasal, Pasal> = {
  '28-2': {
    nama: 'UU ITE Pasal 28 ayat (2) jo. Pasal 45A ayat (2)',
    judul: 'Menyebarkan kebencian berdasarkan identitas',
    isi: 'Dengan sengaja dan tanpa hak menyebarkan informasi elektronik yang menghasut, mengajak atau memengaruhi orang lain sehingga menimbulkan rasa kebencian atau permusuhan terhadap individu atau kelompok berdasarkan ras, kebangsaan, etnis, warna kulit, agama, kepercayaan, jenis kelamin atau disabilitas.',
    ancaman: 'Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar',
    catatan: 'Menurut Putusan MK No. 105/PUU-XXII/2024, berlaku bila dilakukan dengan sengaja, di muka umum, dan menimbulkan risiko nyata diskriminasi, permusuhan atau kekerasan.',
    tautan: '/dasar-hukum#pasal-28-2',
  },
  '29': {
    nama: 'UU ITE Pasal 29 jo. Pasal 45B',
    judul: 'Ancaman kekerasan atau menakut-nakuti',
    isi: 'Mengirimkan informasi elektronik berisi ancaman kekerasan atau upaya menakut-nakuti yang ditujukan secara pribadi kepada korban.',
    ancaman: 'Penjara paling lama 4 tahun dan/atau denda paling banyak Rp750 juta',
    catatan: '',
    tautan: '/dasar-hukum#pasal-29',
  },
  '27a': {
    nama: 'UU ITE Pasal 27A jo. Pasal 45 ayat (4)',
    judul: 'Menyerang kehormatan atau nama baik',
    isi: 'Dengan sengaja menyerang kehormatan atau nama baik orang lain dengan cara menuduhkan suatu hal, dengan maksud agar diketahui umum, melalui sistem elektronik.',
    ancaman: 'Penjara paling lama 2 tahun dan/atau denda paling banyak Rp400 juta',
    catatan: 'Delik aduan: hanya diproses jika korban sendiri yang mengadu.',
    tautan: '/dasar-hukum#pasal-27a',
  },
  kampus: {
    nama: 'Permendikbudristek No. 55 Tahun 2024',
    judul: 'Pencegahan dan penanganan kekerasan di perguruan tinggi',
    isi: 'Perguruan tinggi wajib mencegah dan menangani kekerasan di lingkungan kampus, termasuk perundungan, kekerasan psikis, diskriminasi dan intoleransi, melalui satuan tugas.',
    ancaman: 'Diputuskan pimpinan perguruan tinggi berdasarkan hasil pemeriksaan Satgas',
    catatan: '',
    tautan: '/dasar-hukum#aturan-kampus',
  },
};

export const TEMPAT = [
  'Grup WhatsApp',
  'Chat pribadi (WhatsApp, Line, Telegram)',
  'Instagram',
  'TikTok',
  'X (Twitter)',
  'Discord',
  'Di kampus secara langsung',
  'Lainnya',
] as const;

export const BENTUK = [
  'Hinaan atau ejekan',
  'Ujaran kebencian (suku, agama, ras)',
  'Ancaman',
  'Pengucilan',
  'Foto atau data pribadi disebar',
  'Kekerasan fisik',
  'Lainnya',
] as const;

export const FREKUENSI = ['Sekali', '2 sampai 3 kali', 'Lebih dari 3 kali', 'Terus berlangsung'] as const;

/** Status laporan, urut sesuai alur penanganan Satgas. */
export const STATUS = {
  terkirim: { label: 'Terkirim', info: 'Laporan diterima sistem dan menunggu diperiksa Satgas.' },
  diverifikasi: { label: 'Diverifikasi', info: 'Satgas sudah memeriksa kelengkapan laporan.' },
  ditindaklanjuti: { label: 'Ditindaklanjuti', info: 'Satgas sedang menangani laporan, misalnya memanggil pihak terkait.' },
  selesai: { label: 'Selesai', info: 'Penanganan laporan sudah selesai.' },
  ditolak: { label: 'Ditolak', info: 'Laporan tidak dapat ditindaklanjuti. Lihat catatan Satgas.' },
} as const;

export type StatusLaporan = keyof typeof STATUS;
export const DAFTAR_STATUS = Object.keys(STATUS) as StatusLaporan[];
