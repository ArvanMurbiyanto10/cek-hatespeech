'use client';
import { bacaBalasan } from '@/lib/balasan';
import { useState } from 'react';
import { Panah } from '@/components/Panah';

export function FormMasuk({ lanjut }: { lanjut: string }) {
  const [galat, setGalat] = useState('');
  const [sibuk, setSibuk] = useState(false);

  async function kirim(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const f = new FormData(e.currentTarget);
    setSibuk(true);
    setGalat('');
    try {
      const r = await fetch('/api/auth/masuk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: f.get('email'), sandi: f.get('sandi') }),
      });
      const d = await bacaBalasan<{ ok: boolean; role?: string; galat?: string }>(r);
      if (!d.ok) throw new Error(d.galat ?? 'Gagal masuk.');
      window.location.href = d.role === 'satgas' ? '/satgas' : lanjut || '/laporan';
    } catch (err) {
      setSibuk(false);
      setGalat(err instanceof Error ? err.message : 'Gagal masuk.');
    }
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
