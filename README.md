# Cek Hatespeech

Aplikasi web pelaporan perundungan dan ujaran kebencian untuk mahasiswa Telkom University Purwokerto.
Bukti teks dinilai model **XLM-RoBERTa** (`nahiar/hatespeech-abusive-xlm-roberta-v1`), pasal UU ITE yang mungkin
terkait dipilih otomatis, dan laporan disimpan di database untuk ditangani **Satgas PPKPT**.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Aplikasi web | Next.js 15 (App Router), React 19, TypeScript |
| Database, login, penyimpanan file | Supabase (PostgreSQL, Auth, Storage, Row Level Security) |
| Model AI | FastAPI "RuangAman API" di Google Colab, diakses lewat Cloudflare Tunnel |
| PDF laporan | jsPDF (dibuat di browser) |
| Hosting | Vercel |

## Dua role

- **Mahasiswa**: daftar dan masuk, membuat laporan (data diri terisi dari akun), memantau status, mengunduh PDF.
- **Satgas PPKPT**: melihat semua laporan di dasbor (urut prioritas), mengubah status, menulis catatan, melihat rekap.

Aturan "mahasiswa hanya melihat laporannya sendiri, Satgas melihat semua" dijaga oleh database lewat RLS
(lihat `supabase/schema.sql`).

## Struktur

```
app/                 halaman (beranda, tentang, dasar-hukum, masuk, daftar, lapor, laporan, satgas)
app/api/periksa      pratinjau pemeriksaan AI
app/api/laporan      menyimpan laporan baru (AI dihitung ulang di server)
lib/kamus.ts         kamus kata untuk menyorot kata dan memperkirakan sasaran
lib/model.ts         pemanggilan model AI
lib/pasal.ts         pemilihan pasal yang mungkin terkait dan prioritas laporan
lib/pdf.ts           pembuat PDF laporan
supabase/schema.sql  tabel, RLS, trigger profil dan bucket lampiran
public/assets        CSS, JS dan foto
```

## Menjalankan

1. **Buat proyek Supabase** di supabase.com (paket gratis cukup).
2. Buka **SQL Editor**, tempel isi `supabase/schema.sql`, lalu **Run**.
3. Di **Authentication -> URL Configuration**, isi Site URL (misalnya `https://cek-hatespeech.vercel.app`) dan
   tambahkan `http://localhost:3000/**` serta `https://cek-hatespeech.vercel.app/**` ke Redirect URLs.
4. Salin `.env.example` menjadi `.env.local`, lalu isi dari **Project Settings -> API**:
   ```
   NEXT_PUBLIC_SUPABASE_URL=...
   NEXT_PUBLIC_SUPABASE_ANON_KEY=...
   API_URL=https://xxxx.trycloudflare.com/predict
   ```
5. Jalankan:
   ```
   npm install
   npm run dev
   ```
   Buka http://localhost:3000.
6. **Akun Satgas**: daftar seperti biasa, lalu jalankan di SQL Editor:
   ```sql
   update public.profiles set role = 'satgas' where email = 'email-satgas@contoh.ac.id';
   ```

## Deploy ke Vercel

1. Di Vercel: **Settings -> General -> Framework Preset** pilih **Next.js**.
2. **Settings -> Environment Variables**: isi tiga variabel di atas (Production, Preview, Development).
3. Push ke `main`; Vercel membangun ulang otomatis.

## Catatan

- Kontak resmi Satgas diisi di `lib/kampus.ts` (konstanta `KAMPUS`), hanya dari kanal resmi kampus.
- Teks pasal ada di `lib/kampus.ts` (`PASAL`) dan tabel `pasal`. Cocokkan dengan naskah resmi sebelum dipakai.
- Aplikasi ini prototipe tugas akhir, bukan layanan resmi Telkom University Purwokerto.
