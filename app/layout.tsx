import type { Metadata, Viewport } from 'next';
import Script from 'next/script';
import { Nav } from '@/components/Nav';
import { Footer } from '@/components/Footer';

// CSS kit tasteskill (urutan penting), lalu palet merah putih, ui/base, dan penyesuaian.
import '@/public/assets/system.css';
import '@/public/assets/ui/cards.css';
import '@/public/assets/motion/motion.css';
import '@/public/assets/backgrounds/backgrounds.css';
import '@/public/assets/layout/layout.css';
import '@/public/assets/icons/icon.css';
import './globals.css';
import '@/public/assets/ui/base.css';
import './tambahan.css';

export const metadata: Metadata = {
  metadataBase: new URL('https://cek-hatespeech.vercel.app'),
  title: {
    default: 'Cek Hatespeech: lapor perundungan untuk mahasiswa Telkom University Purwokerto',
    template: '%s | Cek Hatespeech',
  },
  description:
    'Laporkan perundungan dan ujaran kebencian ke Satgas PPKPT Telkom University Purwokerto. Bukti diperiksa model AI, pasal UU ITE dipilih otomatis.',
  icons: { icon: '/favicon.svg' },
  openGraph: { type: 'website', siteName: 'Cek Hatespeech' },
};

export const viewport: Viewport = { themeColor: '#a8101a', width: 'device-width', initialScale: 1, viewportFit: 'cover' };

// Navigasi bergantung pada sesi pengguna, jadi setiap halaman dirender per permintaan.
export const dynamic = 'force-dynamic';

const SKRIP_MASUK = "document.documentElement.classList.add('m-enter');setTimeout(function(){document.documentElement.classList.add('m-enter-done')},700)";

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="id" data-type="software" data-radius="soft" data-buttons="arrow" data-motion="" suppressHydrationWarning>
      <head>
        <meta name="site-kind" content="software" />
        <meta name="page-type" content="software" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link
          href="https://fonts.googleapis.com/css2?family=Host+Grotesk:wght@400..700&family=JetBrains+Mono:wght@400..500&display=swap"
          rel="stylesheet"
        />
        <script dangerouslySetInnerHTML={{ __html: SKRIP_MASUK }} />
      </head>
      <body suppressHydrationWarning>
        <Nav />
        {children}
        <Footer />
        <Script src="/assets/backgrounds/backgrounds.js" strategy="afterInteractive" />
        <Script src="/assets/system.js" type="module" strategy="afterInteractive" />
        <Script src="/assets/motion/motion.js" type="module" strategy="afterInteractive" />
        <Script src="/assets/layout/layout.js" type="module" strategy="afterInteractive" />
      </body>
    </html>
  );
}
