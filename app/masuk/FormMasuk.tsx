'use client';
import { useState } from 'react';
import { supabaseBrowser } from '@/lib/supabase/browser';
import { Panah } from '@/components/Panah';

export function FormMasuk({ lanjut }: { lanjut: string }) {
  const [galat, setGalat] = useState('');
  const [sibuk, setSibuk] = useState(false);

  async function kirim(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const f = new FormData(e.currentTarget);
    setSibuk(true);
    setGalat('');
    const sb = supabaseBrowser();
    const { data, error } = await sb.auth.signInWithPassword({ email: String(f.get('email')), password: String(f.get('sandi')) });
    if (error || !data.user) {
      setSibuk(false);
      setGalat(error?.message === 'Email not confirmed' ? 'Email belum dikonfirmasi. Buka tautan di emailmu dulu.' : 'Email atau kata sandi salah.');
      return;
    }
    const { data: profil } = await sb.from('profiles').select('role').eq('id', data.user.id).maybeSingle();
    window.location.href = profil?.role === 'satgas' ? '/satgas' : lanjut || '/laporan';
  }

  return (
    <form onSubmit={kirim} className="akun__form">
      <h2>Masuk</h2>
      <div className="f"><label htmlFor="email">Email</label><input type="email" id="email" name="email" required autoComplete="email" /></div>
      <div className="f"><label htmlFor="sandi">Kata sandi</label><input type="password" id="sandi" name="sandi" required autoComplete="current-password" /></div>
      {galat && <p className="pesan pesan--galat" role="alert">{galat}</p>}
      <button className="btn btn--primary" type="submit" disabled={sibuk}>{sibuk ? 'Memproses…' : 'Masuk'}<Panah /></button>
      <p className="akun__ganti">Belum punya akun? <a href="/daftar">Daftar sebagai mahasiswa</a></p>
    </form>
  );
}
