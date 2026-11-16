import { penggunaSaatIni } from '@/lib/supabase/server';
import { NavLinks } from './NavLinks';

/** Bar navigasi tetap. Menu akun berubah sesuai role: mahasiswa atau Satgas. */
export async function Nav() {
  const user = await penggunaSaatIni();
  const role = user?.profil?.role;
  const tambahan =
    role === 'satgas' ? { href: '/satgas', label: 'Dasbor Satgas' } : role === 'mahasiswa' ? { href: '/laporan', label: 'Laporan saya' } : undefined;

  return (
    <header className="nav nav--centre">
      <div className="wrap nav__bar">
        <a className="skip-link" href="#top">Lewati ke isi</a>
        <a className="nav__logo" href="/"><span className="mark" data-mark="dots" aria-hidden="true"></span>Cek Hatespeech</a>
        <NavLinks tambahan={tambahan} />
        <div className="nav__actions nav__akun">
          {user ? (
            <>
              <form action="/auth/keluar" method="post"><button className="nav__keluar" type="submit">Keluar</button></form>
              {role === 'satgas'
                ? <a className="btn btn--primary" href="/satgas">Dasbor</a>
                : <a className="btn btn--primary" href="/lapor">Buat laporan</a>}
            </>
          ) : (
            <>
              <a className="nav__keluar" href="/masuk">Masuk</a>
              <a className="btn btn--primary" href="/lapor">Buat laporan</a>
            </>
          )}
        </div>
        <button className="nav__toggle" type="button" aria-expanded="false" aria-label="Menu">
          <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path d="M2 5h14M2 13h14" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" /></svg>
        </button>
      </div>
    </header>
  );
}
