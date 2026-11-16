import { NextResponse } from 'next/server';
import { dbSiap, satu } from '@/lib/db';
import { cocokSandi, hashSandi, mulaiSesi } from '@/lib/auth';
import type { Role } from '@/lib/types';

// Hash tiruan agar waktu respons sama, baik email terdaftar maupun tidak (mencegah menebak email).
let hashTiruan: Promise<string> | null = null;

export async function POST(req: Request) {
  if (!dbSiap) return NextResponse.json({ ok: false, galat: 'Database belum terhubung.' }, { status: 503 });
  const m = ((await req.json().catch(() => null)) ?? {}) as { email?: string; sandi?: string };
  const email = (m.email ?? '').trim().toLowerCase();
  const sandi = m.sandi ?? '';

  const user = await satu<{ id: number; role: Role; password_hash: string }>('SELECT id, role, password_hash FROM users WHERE email = ?', [email]);
  hashTiruan ??= hashSandi('bukan-sandi-siapa-pun');
  const benar = await cocokSandi(sandi, user?.password_hash ?? (await hashTiruan));
  if (!user || !benar) return NextResponse.json({ ok: false, galat: 'Email atau kata sandi salah.' }, { status: 401 });

  await mulaiSesi(user);
  return NextResponse.json({ ok: true, role: user.role });
}
