'use client';
import { usePathname } from 'next/navigation';

const MENU = [
  { href: '/', label: 'Beranda' },
  { href: '/tentang', label: 'Tentang' },
  { href: '/dasar-hukum', label: 'Dasar hukum' },
  { href: '/lapor', label: 'Lapor' },
];

/** Tautan utama; tautan halaman aktif diberi aria-current agar digaris bawahi kit. */
export function NavLinks({ tambahan }: { tambahan?: { href: string; label: string } }) {
  const jalur = usePathname();
  const daftar = tambahan ? [...MENU, tambahan] : MENU;
  return (
    <nav className="nav__links" aria-label="Utama">
      {daftar.map((m) => {
        const aktif = m.href === '/' ? jalur === '/' : jalur === m.href || jalur.startsWith(m.href + '/');
        return (
          <div className="nav__item" key={m.href}>
            <a className="nav__link" href={m.href} aria-current={aktif ? 'page' : undefined}>{m.label}</a>
          </div>
        );
      })}
    </nav>
  );
}
