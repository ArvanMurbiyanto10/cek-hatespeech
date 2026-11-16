import 'server-only';
import { cookies } from 'next/headers';
import bcrypt from 'bcryptjs';
import { dbSiap, satu } from './db';
import { bacaToken, buatToken, NAMA_COOKIE, UMUR_SESI } from './sesi';
import type { Pengguna } from './types';

export async function hashSandi(sandi: string): Promise<string> {
  return bcrypt.hash(sandi, 12);
}

export async function cocokSandi(sandi: string, hash: string): Promise<boolean> {
  return bcrypt.compare(sandi, hash);
}

/** Menyimpan sesi di cookie httpOnly (tidak bisa dibaca JavaScript di browser). */
export async function mulaiSesi(user: Pick<Pengguna, 'id' | 'role'>): Promise<void> {
  const token = await buatToken({ uid: user.id, role: user.role });
  (await cookies()).set(NAMA_COOKIE, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: UMUR_SESI,
  });
}

export async function akhiriSesi(): Promise<void> {
  (await cookies()).delete(NAMA_COOKIE);
}

/**
 * Pengguna yang sedang masuk, dibaca ulang dari database setiap permintaan
 * (role terbaru selalu dipakai, dan akun yang dihapus otomatis keluar).
 */
export async function penggunaSaatIni(): Promise<Pengguna | null> {
  if (!dbSiap || !process.env.SESSION_SECRET) return null;
  const sesi = await bacaToken((await cookies()).get(NAMA_COOKIE)?.value);
  if (!sesi) return null;
  return satu<Pengguna>('SELECT id, role, nama, nim, prodi, angkatan, kontak, email, dibuat FROM users WHERE id = ?', [sesi.uid]);
}
