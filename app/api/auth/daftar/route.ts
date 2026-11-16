import { NextResponse } from 'next/server';
import { dbSiap, jalankan, satu } from '@/lib/db';
import { hashSandi, mulaiSesi } from '@/lib/auth';

interface Masukan { nama?: string; nim?: string; prodi?: string; angkatan?: string; kontak?: string; email?: string; sandi?: string }

const gagal = (galat: string, status = 400) => NextResponse.json({ ok: false, galat }, { status });

/** Pendaftaran akun MAHASISWA. Akun Satgas hanya dibuat lewat `npm run buat-satgas`. */
export async function POST(req: Request) {
  if (!dbSiap) return gagal('Database belum terhubung.', 503);
  const m = ((await req.json().catch(() => null)) ?? {}) as Masukan;
  const nama = (m.nama ?? '').trim();
  const nim = (m.nim ?? '').trim();
  const prodi = (m.prodi ?? '').trim();
  const kontak = (m.kontak ?? '').trim();
  const email = (m.email ?? '').trim().toLowerCase();
  const sandi = m.sandi ?? '';
  const angkatan = m.angkatan ? Number(m.angkatan) : null;

  if (nama.length < 3 || nama.length > 80) return gagal('Isi nama lengkap (3 sampai 80 karakter).');
  if (!/^\d{8,12}$/.test(nim)) return gagal('NIM berisi 8 sampai 12 angka.');
  if (!prodi || prodi.length > 80) return gagal('Isi program studi.');
  if (!/^[0-9+\-\s]{8,20}$/.test(kontak)) return gagal('Isi nomor WhatsApp yang benar.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 120) return gagal('Alamat email tidak valid.');
  if (sandi.length < 8 || sandi.length > 72) return gagal('Kata sandi 8 sampai 72 karakter.');
  if (angkatan !== null && (!Number.isInteger(angkatan) || angkatan < 2000 || angkatan > 2100)) return gagal('Angkatan tidak valid.');

  if (await satu('SELECT id FROM users WHERE email = ? OR nim = ?', [email, nim])) {
    return gagal('Email atau NIM ini sudah terdaftar. Silakan masuk.');
  }
  const hasil = await jalankan(
    'INSERT INTO users (role, nama, nim, prodi, angkatan, kontak, email, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    ['mahasiswa', nama, nim, prodi, angkatan, kontak, email, await hashSandi(sandi)],
  );
  await mulaiSesi({ id: hasil.insertId, role: 'mahasiswa' });
  return NextResponse.json({ ok: true, tujuan: '/lapor' });
}
