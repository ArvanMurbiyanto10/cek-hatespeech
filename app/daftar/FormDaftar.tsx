'use client';
import { bacaBalasan } from '@/lib/balasan';
import { useState } from 'react';
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
    try {
      const r = await fetch('/api/auth/daftar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nama: f.get('nama'), nim, prodi: f.get('prodi'), angkatan: f.get('angkatan'),
          kontak: f.get('kontak'), email: f.get('email'), sandi,
        }),
      });
      const d = await bacaBalasan<{ ok: boolean; tujuan?: string; galat?: string }>(r);
      if (!d.ok) throw new Error(d.galat ?? 'Pendaftaran gagal.');
      window.location.href = d.tujuan ?? '/lapor';
    } catch (err) {
      setSibuk(false);
      setGalat(err instanceof Error ? err.message : 'Pendaftaran gagal.');
    }
  }

  return (
    <form onSubmit={kirim} className="akun__form">
      <h2>Data mahasiswa</h2>
      <div className="akun__isian">
        <div className="f penuh"><label htmlFor="nama">Nama lengkap <em>*</em></label><input type="text" id="nama" name="nama" required maxLength={80} autoComplete="name" /></div>
        <div className="f"><label htmlFor="nim">NIM <em>*</em></label><input type="text" id="nim" name="nim" required inputMode="numeric" maxLength={12} placeholder="contoh: 2311102074" /></div>
        <div className="f"><label htmlFor="angkatan">Angkatan</label><input type="number" id="angkatan" name="angkatan" min={2015} max={2035} placeholder="2023" /></div>
        <div className="f penuh"><label htmlFor="prodi">Program studi <em>*</em></label><input type="text" id="prodi" name="prodi" required maxLength={80} placeholder="contoh: S1 Teknik Informatika" /></div>
        <div className="f penuh"><label htmlFor="kontak">Nomor WhatsApp <em>*</em></label><input type="text" id="kontak" name="kontak" required maxLength={20} inputMode="tel" placeholder="Agar Satgas bisa menghubungimu" /></div>
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
