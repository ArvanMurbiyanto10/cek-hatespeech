import { NextResponse } from 'next/server';
import { supabaseServer } from '@/lib/supabase/server';

/** Tautan konfirmasi email dari Supabase kembali ke sini; kode ditukar menjadi sesi. */
export async function GET(req: Request) {
  const url = new URL(req.url);
  const kode = url.searchParams.get('code');
  const lanjut = url.searchParams.get('lanjut') ?? '/lapor';
  if (kode) {
    const sb = await supabaseServer();
    const { error } = await sb.auth.exchangeCodeForSession(kode);
    if (!error) return NextResponse.redirect(new URL(lanjut.startsWith('/') ? lanjut : '/lapor', url.origin));
  }
  return NextResponse.redirect(new URL('/masuk?galat=konfirmasi', url.origin));
}
