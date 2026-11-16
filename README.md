# Cek Hatespeech

Aplikasi web pelaporan perundungan dan ujaran kebencian untuk mahasiswa Telkom University Purwokerto.
Bukti teks dinilai model **XLM-RoBERTa** (`nahiar/hatespeech-abusive-xlm-roberta-v1`), pasal UU ITE yang mungkin
terkait dipilih otomatis, dan laporan disimpan di **MySQL** untuk ditangani **Satgas PPKPT**.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Aplikasi web | Next.js 15 (App Router), React 19, TypeScript |
| Database | MySQL 8 (atau MariaDB 10.5+), driver `mysql2` dengan query berparameter |
| Login | bcrypt untuk kata sandi, sesi JWT (HS256) di cookie httpOnly (`jose`) |
| Model AI | FastAPI "RuangAman API" di Google Colab, diakses lewat Cloudflare Tunnel |
| PDF laporan | jsPDF (dibuat di browser) |
| Hosting | Vercel + MySQL cloud |

## Dua role

- **Mahasiswa**: daftar dan masuk, membuat laporan (data diri terisi dari akun), memantau status, mengunduh PDF.
- **Satgas PPKPT**: melihat semua laporan di dasbor (urut prioritas), mengubah status dan prioritas, menulis catatan,
  melihat rekap. Akun Satgas hanya dibuat lewat skrip, tidak bisa didaftarkan dari halaman Daftar.

Hak akses dicek di server: mahasiswa hanya bisa membuka laporan dan gambar miliknya sendiri (orang lain mendapat 404),
halaman `/satgas` dan aksi ubah status hanya untuk role Satgas.

## Struktur

```
app/                    halaman (beranda, tentang, dasar-hukum, masuk, daftar, lapor, laporan, satgas)
app/api/auth/*          daftar dan masuk
app/api/periksa         pratinjau pemeriksaan AI
app/api/laporan         menyimpan laporan baru dalam satu transaksi (AI dihitung ulang di server)
app/api/lampiran/[id]   mengirim gambar lampiran setelah mengecek hak akses
lib/db.ts               koneksi MySQL dan transaksi
lib/auth.ts, sesi.ts    login, sesi, pengguna saat ini
lib/data.ts             query laporan (dengan pengecekan hak akses)
lib/kamus.ts            kamus kata untuk menyorot kata dan memperkirakan sasaran
lib/model.ts            pemanggilan model AI
lib/pasal.ts            pemilihan pasal yang mungkin terkait dan prioritas laporan
lib/pdf.ts              pembuat PDF laporan
database/schema.sql     tabel MySQL dan data pasal
scripts/buat-satgas.mjs membuat akun Satgas
```

## Tabel database

`users`, `laporan`, `laporan_bentuk`, `bukti`, `lampiran` (gambar sebagai MEDIUMBLOB), `pasal`, `laporan_pasal`,
`riwayat_status`. Relasi lengkap ada di `database/schema.sql`.

## Menjalankan di laptop (Laragon)

1. Nyalakan MySQL di Laragon (Start All).
2. Buat database dan tabel: buka HeidiSQL dari Laragon, lalu jalankan isi `database/schema.sql`
   (atau `mysql -u root < database/schema.sql`).
3. Salin `.env.example` menjadi `.env.local`, isi:
   ```
   DATABASE_URL=mysql://root:@127.0.0.1:3306/cek_hatespeech
   DB_SSL=false
   SESSION_SECRET=<64 karakter acak>
   API_URL=https://xxxx.trycloudflare.com/predict
   ```
   `SESSION_SECRET` bisa dibuat dengan `node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"`.
4. Pasang dan jalankan:
   ```
   npm install
   npm run buat-satgas -- --nama "Nama Petugas" --email satgas@contoh.ac.id --sandi "kata-sandi-kuat"
   npm run dev
   ```
   Buka http://localhost:3000. Daftar sebagai mahasiswa dari halaman Daftar; masuk sebagai Satgas dengan akun dari skrip.

## Deploy ke Vercel

Vercel tidak bisa memakai MySQL di laptop, jadi database harus di layanan MySQL cloud yang bisa diakses dari internet
(misalnya TiDB Cloud Serverless atau Aiven for MySQL; keduanya punya paket gratis). Lalu:

1. Jalankan `database/schema.sql` di database cloud tersebut.
2. Vercel: **Settings -> General -> Framework Preset** pilih **Next.js**.
3. **Settings -> Environment Variables**: isi `DATABASE_URL`, `DB_SSL=true` (dan `DB_CA` bila layanan memberi sertifikat CA),
   `SESSION_SECRET`, `API_URL`.
4. Buat akun Satgas di database cloud: jalankan `npm run buat-satgas` dengan `DATABASE_URL` cloud di `.env.local`.
5. Push ke `main`; Vercel membangun ulang otomatis.

## Catatan

- Kontak resmi Satgas diisi di `lib/kampus.ts` (konstanta `KAMPUS`), hanya dari kanal resmi kampus.
- Teks pasal ada di `lib/kampus.ts` (`PASAL`) dan tabel `pasal`. Cocokkan dengan naskah resmi sebelum dipakai.
- Aplikasi ini prototipe tugas akhir, bukan layanan resmi Telkom University Purwokerto.
