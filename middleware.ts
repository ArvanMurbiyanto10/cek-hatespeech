import { NextResponse, type NextRequest } from 'next/server';
import { bacaToken, NAMA_COOKIE } from '@/lib/sesi';

/**
 * Penjaga halaman: halaman aplikasi wajib masuk, dan /satgas hanya untuk role Satgas.
 * Halaman tetap mengecek ulang ke database (role terbaru), middleware hanya penyaring awal.
 */
export async function middleware(req: NextRequest) {
  const jalur = req.nextUrl.pathname;
  const sesi = process.env.SESSION_SECRET ? await bacaToken(req.cookies.get(NAMA_COOKIE)?.value) : null;

  if (!sesi) {
    const url = req.nextUrl.clone();
    url.pathname = '/masuk';
    url.search = `?lanjut=${encodeURIComponent(jalur)}`;
    return NextResponse.redirect(url);
  }
  if (jalur.startsWith('/satgas') && sesi.role !== 'satgas') return NextResponse.redirect(new URL('/laporan', req.url));
  return NextResponse.next();
}

export const config = { matcher: ['/lapor', '/laporan/:path*', '/satgas/:path*'] };
