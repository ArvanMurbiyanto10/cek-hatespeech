import { NextResponse } from 'next/server';
import { supabaseServer } from '@/lib/supabase/server';
import { supabaseSiap } from '@/lib/supabase/konfigurasi';

export async function POST(req: Request) {
  if (supabaseSiap) {
    const sb = await supabaseServer();
    await sb.auth.signOut();
  }
  return NextResponse.redirect(new URL('/', req.url), { status: 303 });
}
