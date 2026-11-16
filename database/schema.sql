-- =====================================================================
-- Cek Hatespeech: skema database MySQL 8 (juga jalan di MariaDB 10.5+)
-- Jalankan sekali, misalnya:  mysql -u root -p < database/schema.sql
-- atau tempel di phpMyAdmin / HeidiSQL (Laragon) / TablePlus.
--
-- Dua role di tabel users:
--   mahasiswa : membuat laporan dan hanya melihat laporannya sendiri
--   satgas    : melihat semua laporan, mengubah status, memberi catatan
-- Hak akses dicek di server aplikasi (lib/auth.ts, lib/data.ts).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS cek_hatespeech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cek_hatespeech;

-- ---------- 1. Pengguna ----------
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role           ENUM('mahasiswa', 'satgas') NOT NULL DEFAULT 'mahasiswa',
  nama           VARCHAR(80)  NOT NULL,
  nim            VARCHAR(12)  NULL UNIQUE,
  prodi          VARCHAR(80)  NULL,
  angkatan       SMALLINT UNSIGNED NULL,
  kontak         VARCHAR(30)  NULL,
  email          VARCHAR(120) NOT NULL UNIQUE,
  password_hash  VARCHAR(100) NOT NULL,
  dibuat         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- 2. Master pasal ----------
CREATE TABLE IF NOT EXISTS pasal (
  kode     VARCHAR(10)  PRIMARY KEY,
  nama     VARCHAR(120) NOT NULL,
  judul    VARCHAR(160) NOT NULL,
  isi      TEXT NOT NULL,
  ancaman  VARCHAR(200) NOT NULL,
  catatan  TEXT NOT NULL
) ENGINE=InnoDB;

INSERT INTO pasal (kode, nama, judul, isi, ancaman, catatan) VALUES
('28-2', 'UU ITE Pasal 28 ayat (2) jo. Pasal 45A ayat (2)', 'Menyebarkan kebencian berdasarkan identitas',
 'Dengan sengaja dan tanpa hak menyebarkan informasi elektronik yang menghasut, mengajak atau memengaruhi orang lain sehingga menimbulkan rasa kebencian atau permusuhan terhadap individu atau kelompok berdasarkan ras, kebangsaan, etnis, warna kulit, agama, kepercayaan, jenis kelamin atau disabilitas.',
 'Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar',
 'Menurut Putusan MK No. 105/PUU-XXII/2024, berlaku bila dilakukan dengan sengaja, di muka umum, dan menimbulkan risiko nyata diskriminasi, permusuhan atau kekerasan.'),
('29', 'UU ITE Pasal 29 jo. Pasal 45B', 'Ancaman kekerasan atau menakut-nakuti',
 'Mengirimkan informasi elektronik berisi ancaman kekerasan atau upaya menakut-nakuti yang ditujukan secara pribadi kepada korban.',
 'Penjara paling lama 4 tahun dan/atau denda paling banyak Rp750 juta', ''),
('27a', 'UU ITE Pasal 27A jo. Pasal 45 ayat (4)', 'Menyerang kehormatan atau nama baik',
 'Dengan sengaja menyerang kehormatan atau nama baik orang lain dengan cara menuduhkan suatu hal, dengan maksud agar diketahui umum, melalui sistem elektronik.',
 'Penjara paling lama 2 tahun dan/atau denda paling banyak Rp400 juta', 'Delik aduan: hanya diproses jika korban sendiri yang mengadu.'),
('kampus', 'Permendikbudristek No. 55 Tahun 2024', 'Pencegahan dan penanganan kekerasan di perguruan tinggi',
 'Perguruan tinggi wajib mencegah dan menangani kekerasan di lingkungan kampus, termasuk perundungan, kekerasan psikis, diskriminasi dan intoleransi, melalui satuan tugas.',
 'Diputuskan pimpinan perguruan tinggi berdasarkan hasil pemeriksaan Satgas', '')
ON DUPLICATE KEY UPDATE nama = VALUES(nama), judul = VALUES(judul), isi = VALUES(isi),
  ancaman = VALUES(ancaman), catatan = VALUES(catatan);

-- ---------- 3. Laporan ----------
CREATE TABLE IF NOT EXISTS laporan (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode              VARCHAR(20) NOT NULL UNIQUE,
  pelapor_id        INT UNSIGNED NOT NULL,
  peran             ENUM('korban', 'saksi') NOT NULL,
  tanggal_kejadian  DATE NOT NULL,
  tempat            VARCHAR(80) NOT NULL,
  frekuensi         VARCHAR(30) NULL,
  terduga_pelaku    VARCHAR(120) NULL,
  kronologi         TEXT NOT NULL,
  status            ENUM('terkirim', 'diverifikasi', 'ditindaklanjuti', 'selesai', 'ditolak') NOT NULL DEFAULT 'terkirim',
  prioritas         ENUM('tinggi', 'sedang', 'normal') NOT NULL DEFAULT 'normal',
  dibuat            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diubah            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_laporan_pelapor FOREIGN KEY (pelapor_id) REFERENCES users (id) ON DELETE CASCADE,
  INDEX idx_laporan_status (status, dibuat)
) ENGINE=InnoDB;

-- ---------- 4. Bentuk perundungan (1 laporan : banyak bentuk) ----------
CREATE TABLE IF NOT EXISTS laporan_bentuk (
  laporan_id  INT UNSIGNED NOT NULL,
  bentuk      VARCHAR(60) NOT NULL,
  PRIMARY KEY (laporan_id, bentuk),
  CONSTRAINT fk_bentuk_laporan FOREIGN KEY (laporan_id) REFERENCES laporan (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- 5. Bukti teks + hasil AI ----------
CREATE TABLE IF NOT EXISTS bukti (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  laporan_id       INT UNSIGNED NOT NULL,
  isi_teks         TEXT NOT NULL,
  ai_label         ENUM('hate', 'tinjau', 'abusive', 'aman') NULL,
  skor_hatespeech  DECIMAL(6, 4) NULL,
  skor_abusive     DECIMAL(6, 4) NULL,
  temuan           JSON NULL,
  model            VARCHAR(80) NULL,
  dibuat           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bukti_laporan FOREIGN KEY (laporan_id) REFERENCES laporan (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- 6. Lampiran tangkapan layar (disimpan sebagai BLOB, dibuka lewat /api/lampiran/:id) ----------
CREATE TABLE IF NOT EXISTS lampiran (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  laporan_id  INT UNSIGNED NOT NULL,
  nama_file   VARCHAR(120) NOT NULL,
  mime        VARCHAR(30)  NOT NULL,
  ukuran      INT UNSIGNED NOT NULL,
  data        MEDIUMBLOB NOT NULL,
  dibuat      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lampiran_laporan FOREIGN KEY (laporan_id) REFERENCES laporan (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- 7. Pasal yang mungkin terkait (banyak-ke-banyak) ----------
CREATE TABLE IF NOT EXISTS laporan_pasal (
  laporan_id  INT UNSIGNED NOT NULL,
  pasal_kode  VARCHAR(10) NOT NULL,
  alasan      VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (laporan_id, pasal_kode),
  CONSTRAINT fk_lp_laporan FOREIGN KEY (laporan_id) REFERENCES laporan (id) ON DELETE CASCADE,
  CONSTRAINT fk_lp_pasal FOREIGN KEY (pasal_kode) REFERENCES pasal (kode)
) ENGINE=InnoDB;

-- ---------- 8. Riwayat status (jejak audit penanganan) ----------
CREATE TABLE IF NOT EXISTS riwayat_status (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  laporan_id  INT UNSIGNED NOT NULL,
  status      VARCHAR(20) NOT NULL,
  catatan     VARCHAR(1000) NOT NULL DEFAULT '',
  oleh        INT UNSIGNED NULL,
  dibuat      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_riwayat_laporan FOREIGN KEY (laporan_id) REFERENCES laporan (id) ON DELETE CASCADE,
  CONSTRAINT fk_riwayat_oleh FOREIGN KEY (oleh) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Akun Satgas TIDAK dibuat lewat halaman Daftar. Buat dengan:
--   npm run buat-satgas -- --nama "Nama Petugas" --email satgas@contoh.ac.id --sandi "kata-sandi-kuat"
