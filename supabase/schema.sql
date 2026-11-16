-- =====================================================================
-- Cek Hatespeech: skema database (PostgreSQL di Supabase)
-- Jalankan sekali di Supabase: SQL Editor -> New query -> tempel -> Run.
--
-- Dua role:
--   mahasiswa : membuat laporan dan hanya melihat laporannya sendiri
--   satgas    : melihat semua laporan, mengubah status, memberi catatan
-- Aturan akses dijaga database lewat Row Level Security (RLS).
-- =====================================================================

-- ---------- 1. Profil pengguna (1:1 dengan auth.users) ----------
create table if not exists public.profiles (
  id        uuid primary key references auth.users (id) on delete cascade,
  role      text not null default 'mahasiswa' check (role in ('mahasiswa', 'satgas')),
  nama      text not null,
  nim       text,
  prodi     text,
  angkatan  int,
  kontak    text,
  email     text,
  dibuat    timestamptz not null default now()
);

-- Profil dibuat otomatis saat mendaftar. Role SELALU 'mahasiswa';
-- akun Satgas dinaikkan manual oleh admin (lihat bagian paling bawah).
create or replace function public.buat_profil()
returns trigger language plpgsql security definer set search_path = public as $$
begin
  insert into public.profiles (id, nama, nim, prodi, angkatan, kontak, email)
  values (
    new.id,
    coalesce(new.raw_user_meta_data ->> 'nama', ''),
    new.raw_user_meta_data ->> 'nim',
    new.raw_user_meta_data ->> 'prodi',
    nullif(new.raw_user_meta_data ->> 'angkatan', '')::int,
    new.raw_user_meta_data ->> 'kontak',
    new.email
  );
  return new;
end $$;

drop trigger if exists saat_daftar on auth.users;
create trigger saat_daftar after insert on auth.users
  for each row execute function public.buat_profil();

create or replace function public.is_satgas()
returns boolean language sql stable security definer set search_path = public as $$
  select exists (select 1 from public.profiles where id = auth.uid() and role = 'satgas');
$$;

-- ---------- 2. Master pasal ----------
create table if not exists public.pasal (
  kode     text primary key,
  nama     text not null,
  judul    text not null,
  isi      text not null,
  ancaman  text not null,
  catatan  text not null default ''
);

insert into public.pasal (kode, nama, judul, isi, ancaman, catatan) values
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
on conflict (kode) do update set nama = excluded.nama, judul = excluded.judul, isi = excluded.isi,
  ancaman = excluded.ancaman, catatan = excluded.catatan;

-- ---------- 3. Laporan ----------
create table if not exists public.laporan (
  id                uuid primary key default gen_random_uuid(),
  kode              text not null unique,
  pelapor_id        uuid not null references public.profiles (id) on delete cascade,
  peran             text not null check (peran in ('korban', 'saksi')),
  tanggal_kejadian  date not null,
  tempat            text not null,
  frekuensi         text,
  bentuk            text[] not null default '{}',
  terduga_pelaku    text,
  kronologi         text not null check (char_length(kronologi) <= 1500),
  status            text not null default 'terkirim'
                    check (status in ('terkirim', 'diverifikasi', 'ditindaklanjuti', 'selesai', 'ditolak')),
  prioritas         text not null default 'normal' check (prioritas in ('tinggi', 'sedang', 'normal')),
  dibuat            timestamptz not null default now(),
  diubah            timestamptz not null default now()
);
create index if not exists laporan_pelapor_idx on public.laporan (pelapor_id);
create index if not exists laporan_status_idx on public.laporan (status, dibuat desc);

-- ---------- 4. Bukti teks + hasil AI (1 laporan : banyak bukti) ----------
create table if not exists public.bukti (
  id               uuid primary key default gen_random_uuid(),
  laporan_id       uuid not null references public.laporan (id) on delete cascade,
  isi_teks         text not null check (char_length(isi_teks) <= 2000),
  ai_label         text check (ai_label in ('hate', 'tinjau', 'abusive', 'aman')),
  skor_hatespeech  numeric(6, 4),
  skor_abusive     numeric(6, 4),
  temuan           jsonb not null default '[]',
  model            text,
  dibuat           timestamptz not null default now()
);

-- ---------- 5. Lampiran tangkapan layar (berkas di Storage bucket 'lampiran') ----------
create table if not exists public.lampiran (
  id          uuid primary key default gen_random_uuid(),
  laporan_id  uuid not null references public.laporan (id) on delete cascade,
  path        text not null,
  dibuat      timestamptz not null default now()
);

-- ---------- 6. Pasal yang mungkin terkait (banyak-ke-banyak) ----------
create table if not exists public.laporan_pasal (
  laporan_id  uuid not null references public.laporan (id) on delete cascade,
  pasal_kode  text not null references public.pasal (kode),
  alasan      text not null default '',
  primary key (laporan_id, pasal_kode)
);

-- ---------- 7. Riwayat status (jejak audit penanganan) ----------
create table if not exists public.riwayat_status (
  id          bigint generated always as identity primary key,
  laporan_id  uuid not null references public.laporan (id) on delete cascade,
  status      text not null,
  catatan     text not null default '',
  oleh        uuid references public.profiles (id),
  dibuat      timestamptz not null default now()
);

-- =====================================================================
-- Row Level Security
-- =====================================================================
alter table public.profiles       enable row level security;
alter table public.pasal          enable row level security;
alter table public.laporan        enable row level security;
alter table public.bukti          enable row level security;
alter table public.lampiran       enable row level security;
alter table public.laporan_pasal  enable row level security;
alter table public.riwayat_status enable row level security;

-- profiles: lihat milik sendiri (Satgas lihat semua); ubah data diri sendiri, TIDAK termasuk role.
drop policy if exists profil_lihat on public.profiles;
create policy profil_lihat on public.profiles for select using (id = auth.uid() or public.is_satgas());
drop policy if exists profil_ubah on public.profiles;
create policy profil_ubah on public.profiles for update using (id = auth.uid()) with check (id = auth.uid());
revoke update on public.profiles from authenticated, anon;
grant update (nama, nim, prodi, angkatan, kontak) on public.profiles to authenticated;

-- pasal: boleh dibaca siapa saja.
drop policy if exists pasal_lihat on public.pasal;
create policy pasal_lihat on public.pasal for select using (true);

-- laporan
drop policy if exists laporan_lihat on public.laporan;
create policy laporan_lihat on public.laporan for select using (pelapor_id = auth.uid() or public.is_satgas());
drop policy if exists laporan_buat on public.laporan;
create policy laporan_buat on public.laporan for insert
  with check (pelapor_id = auth.uid() and status = 'terkirim');
-- pelapor boleh membatalkan laporannya selama belum diperiksa Satgas (juga dipakai untuk rollback saat gagal simpan).
drop policy if exists laporan_batal on public.laporan;
create policy laporan_batal on public.laporan for delete
  using (pelapor_id = auth.uid() and status = 'terkirim');
drop policy if exists laporan_ubah_satgas on public.laporan;
create policy laporan_ubah_satgas on public.laporan for update using (public.is_satgas()) with check (public.is_satgas());
revoke update on public.laporan from authenticated, anon;
grant update (status, prioritas, diubah) on public.laporan to authenticated;

-- tabel anak: terlihat bila laporannya terlihat; ditambah hanya oleh pelapor laporan itu.
drop policy if exists bukti_lihat on public.bukti;
create policy bukti_lihat on public.bukti for select
  using (exists (select 1 from public.laporan l where l.id = laporan_id));
drop policy if exists bukti_buat on public.bukti;
create policy bukti_buat on public.bukti for insert
  with check (exists (select 1 from public.laporan l where l.id = laporan_id and l.pelapor_id = auth.uid()));

drop policy if exists lampiran_lihat on public.lampiran;
create policy lampiran_lihat on public.lampiran for select
  using (exists (select 1 from public.laporan l where l.id = laporan_id));
drop policy if exists lampiran_buat on public.lampiran;
create policy lampiran_buat on public.lampiran for insert
  with check (exists (select 1 from public.laporan l where l.id = laporan_id and l.pelapor_id = auth.uid())
              and split_part(path, '/', 1) = auth.uid()::text);

drop policy if exists lp_lihat on public.laporan_pasal;
create policy lp_lihat on public.laporan_pasal for select
  using (exists (select 1 from public.laporan l where l.id = laporan_id));
drop policy if exists lp_buat on public.laporan_pasal;
create policy lp_buat on public.laporan_pasal for insert
  with check (exists (select 1 from public.laporan l where l.id = laporan_id and l.pelapor_id = auth.uid()));

drop policy if exists riwayat_lihat on public.riwayat_status;
create policy riwayat_lihat on public.riwayat_status for select
  using (exists (select 1 from public.laporan l where l.id = laporan_id));
drop policy if exists riwayat_buat on public.riwayat_status;
create policy riwayat_buat on public.riwayat_status for insert
  with check (oleh = auth.uid() and (
    public.is_satgas()
    or (status = 'terkirim' and exists (select 1 from public.laporan l where l.id = laporan_id and l.pelapor_id = auth.uid()))
  ));

-- =====================================================================
-- Storage: bucket privat 'lampiran'. Berkas disimpan di <uid pelapor>/<id laporan>/<n>.jpg
-- =====================================================================
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('lampiran', 'lampiran', false, 5242880, array['image/jpeg', 'image/png', 'image/webp'])
on conflict (id) do nothing;

drop policy if exists lampiran_unggah on storage.objects;
create policy lampiran_unggah on storage.objects for insert to authenticated
  with check (bucket_id = 'lampiran' and (storage.foldername(name))[1] = auth.uid()::text);
drop policy if exists lampiran_baca on storage.objects;
create policy lampiran_baca on storage.objects for select to authenticated
  using (bucket_id = 'lampiran' and ((storage.foldername(name))[1] = auth.uid()::text or public.is_satgas()));

-- =====================================================================
-- Menjadikan akun Satgas (jalankan setelah orangnya mendaftar biasa):
--   update public.profiles set role = 'satgas' where email = 'email-satgas@contoh.ac.id';
-- =====================================================================
