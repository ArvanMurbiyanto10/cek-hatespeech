'use client';
import { useState } from 'react';
import { supabaseBrowser } from '@/lib/supabase/browser';
import { Panah } from '@/components/Panah';

export function FormDaftar() {
  const [galat, setGalat] = useState('');
  const [sibuk, setSibuk] = useState(false);

  async function kirim(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const f = new FormData(e.currentTarget);
    const nim = String(f.get('nim')).trim();
    const sandi = String(f.get('sandi'));
    if (!/^\d{8,12}$/.test(nim)) return setGalat('NIM berisi 8 sampai 12 angka.');
    if (sandi.length < 8) return setGalat('Kata sandi minimal 8 karakter.');
    if (sandi !== String(f.get('ulang'))) return setGalat('Ulangi kata sandi dengan sama persis.');

    setSibuk(true);
    setGalat('');
    const { error } = await supabaseBrowser().auth.signUp({
      email: String(f.get('email')).trim(),
      password: sandi,
      options: {
        emailRedirectTo: `${window.location.origin}/auth/callback?lanjut=/lapor`,
        // Disalin ke tabel profiles oleh trigger. Role selalu 'mahasiswa'.
        data: {
          nama: String(f.get('nama')).trim(),
          nim,
          prodi: String(f.get('prodi')).trim(),
          angkatan: String(f.get('angkatan') ?? ''),
          kontak: String(f.get('kontak')).trim(),
        },
      },
    });
    if (error) {
      setSibuk(false);
      setGalat(/registered/i.test(error.message) ? 'Email ini sudah terdaftar. Silakan masuk.' : `Pendaftaran gagal: ${error.message}`);
      return;
    }
    window.location.href = '/masuk?daftar=cek-email';
  }

  return (
    <form onSubmit={kirim} className="akun__form">
      <h2>Data mahasiswa</h2>
      <div className="akun__isian">
        <div className="f penuh"><label htmlFor="nama">Nama lengkap <em>*</em></label><input type="text" id="nama" name="nama" required maxLength={80} autoComplete="name" /></div>
        <div className="f"><label htmlFor="nim">NIM <em>*</em></label><input type="text" id="nim" name="nim" required inputMode="numeric" maxLength={12} placeholder="contoh: 2311102074" /></div>
        <div className="f"><label htmlFor="angkatan">Angkatan</label><input type="number" id="angkatan" name="angkatan" min={2015} max={2035} placeholder="2023" /></div>
        <div className="f penuh"><label htmlFor="prodi">Program studi <em>*</em></label><input type="text" id="prodi" name="prodi" required maxLength={80} placeholder="contoh: S1 Teknik Informatika" /></div>
        <div className="f penuh"><label htmlFor="kontak">Nomor WhatsApp <em>*</em></label><input type="text" id="kontak" name="kontak" required maxLength={30} placeholder="Agar Satgas bisa menghubungimu" /></div>
        <div className="f penuh"><label htmlFor="email">Email <em>*</em></label><input type="email" id="email" name="email" required autoComplete="email" /></div>
        <div className="f"><label htmlFor="sandi">Kata sandi <em>*</em></label><input type="password" id="sandi" name="sandi" required minLength={8} autoComplete="new-password" /></div>
        <div className="f"><label htmlFor="ulang">Ulangi kata sandi <em>*</em></label><input type="password" id="ulang" name="ulang" required minLength={8} autoComplete="new-password" /></div>
      </div>
      {galat && <p className="pesan pesan--galat" role="alert">{galat}</p>}
      <button className="btn btn--primary" type="submit" disabled={sibuk}>{sibuk ? 'Mendaftarkan…' : 'Daftar'}<Panah /></button>
      <p className="akun__ganti">Sudah punya akun? <a href="/masuk">Masuk</a></p>
    </form>
  );
}
