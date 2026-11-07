# Cek Hatespeech

Aplikasi web satu halaman untuk mendeteksi **ujaran kebencian** (*hate speech*) dan **bahasa kasar** (*abusive language*) pada teks berbahasa Indonesia. Penilaiannya dilakukan oleh model Transformer [`nahiar/hatespeech-abusive-xlm-roberta-v1`](https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1).

Proyek ini dibuat sebagai tugas akhir.

## Fitur

- Cek teks hingga 2.000 karakter. Hasilnya berupa salah satu dari empat status: **Terindikasi Hate Speech**, **Perlu Ditinjau**, **Ujaran Kasar**, atau **Bukan Hate Speech**.
- Skor probabilitas dari model untuk hate speech dan bahasa kasar.
- Sorotan kata, perkiraan sasaran (individu/kelompok), dan kategori (agama, ras, fisik, gender) dari kamus kata bantu.
- Halaman edukasi: pengertian ujaran kebencian, dasar hukum dan penjelasan pasal (UU ITE, KUHP baru, UU 40/2008), cara kerja sistem, informasi model, keterbatasan, dan FAQ.

## Arsitektur

```
Browser ──▶ index.php (PHP) ──JSON──▶ API FastAPI (Google Colab + Cloudflare Tunnel) ──▶ XLM-RoBERTa
```

- **api/index.php**: tampilan, validasi input, pemanggilan API model, dan kamus kata bantu.
- **API model**: FastAPI yang berjalan di Google Colab. Kode API tidak termasuk di repository ini.

## Menjalankan secara lokal

Kebutuhan: PHP 8.0 atau lebih baru dengan ekstensi `curl` dan `mbstring`.

1. Jalankan notebook API model di Google Colab, lalu salin URL Cloudflare Tunnel yang muncul.
2. Atur alamat API lewat environment variable `API_URL` (URL tadi ditambah `/predict`), atau ubah nilai cadangannya di `api/index.php`.
3. Jalankan server dari folder akar proyek:
   ```bash
   API_URL=https://xxxx.trycloudflare.com/predict php -S localhost:8000 -t . api/index.php
   ```
4. Buka http://localhost:8000.

Data identitas di bagian "Pengembang" diatur lewat konstanta `PENGEMBANG` di bagian atas `api/index.php`.

## Deploy ke Vercel

Proyek memakai runtime komunitas [`vercel-php`](https://github.com/vercel-community/php) yang diatur di `vercel.json`.

1. Import repository ini di Vercel (Add New > Project). Framework Preset: **Other**.
2. Tambahkan Environment Variable `API_URL` berisi URL API model ditambah `/predict`.
3. Deploy. Setiap kali `API_URL` diganti, lakukan **Redeploy** agar nilainya terpakai.

## Struktur

```
.
├── api/index.php      # Seluruh aplikasi (logika, tampilan, skrip)
├── assets/img/        # Foto halaman (lihat BACA-SAYA.txt)
└── vercel.json        # Konfigurasi runtime PHP di Vercel
```

## Kredit

- Model: [nahiar/hatespeech-abusive-xlm-roberta-v1](https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1) (Apache-2.0)
- Foto (CC0 / domain publik): rawpixel, Rawpixel.com via Wikimedia Commons, Siddharth Bhogra via StockSnap, Joe Gratz via Wikimedia Commons. Rincian ada di [`assets/img/BACA-SAYA.txt`](assets/img/BACA-SAYA.txt).

## Catatan

Hasil deteksi bersifat otomatis dan bisa keliru. Hasilnya bukan alat bukti hukum, dan penjelasan pasal di halaman ini adalah ringkasan untuk tujuan edukasi.
