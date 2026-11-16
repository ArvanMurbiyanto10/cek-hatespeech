import { NextResponse, type NextRequest } from 'next/server';
import { createServerClient } from '@supabase/ssr';
import { SUPABASE_ANON_KEY, SUPABASE_URL, supabaseSiap } from '@/lib/supabase/konfigurasi';

/** Halaman yang wajib masuk. Pemeriksaan role (mahasiswa/satgas) dilakukan di halaman masing-masing. */
const WAJIB_MASUK = ['/lapor', '/laporan', '/satgas'];

export async function middleware(req: NextRequest) {
  let res = NextResponse.next({ request: req });
  if (!supabaseSiap) return res;

  const sb = createServerClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    cookies: {
      getAll: () => req.cookies.getAll(),
      setAll: (daftar) => {
        daftar.forEach(({ name, value }) => req.cookies.set(name, value));
        res = NextResponse.next({ request: req });
        daftar.forEach(({ name, value, options }) => res.cookies.set(name, value, options));
      },
    },
  });

  // Memperbarui sesi yang hampir kedaluwarsa. Jangan menaruh logika lain di antara pembuatan klien dan getUser().
  const { data } = await sb.auth.getUser();
  const jalur = req.nextUrl.pathname;
  if (!data.user && WAJIB_MASUK.some((p) => jalur === p || jalur.startsWith(p + '/'))) {
    const url = req.nextUrl.clone();
    url.pathname = '/masuk';
    url.search = `?lanjut=${encodeURIComponent(jalur)}`;
    return NextResponse.redirect(url);
  }
  return res;
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|assets|favicon.svg|.*\.(?:png|jpg|jpeg|webp|svg|css|js)$).*)'],
};
