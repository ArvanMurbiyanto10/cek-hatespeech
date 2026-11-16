import { redirect } from 'next/navigation';
import { penggunaSaatIni } from '@/lib/auth';
import { BelumSiap } from '@/components/BelumSiap';
import { FormMasuk } from './FormMasuk';
import '../akun.css';

export const metadata = { title: 'Masuk' };

export default async function Halaman({ searchParams }: { searchParams: Promise<{ lanjut?: string }> }) {
  const sp = await searchParams;
  const user = await penggunaSaatIni();
  if (user) redirect(user.role === 'satgas' ? '/satgas' : sp.lanjut?.startsWith('/') ? sp.lanjut : '/laporan');

  return (
    <main id="top">
      <section className="doc-sec app-page akun" aria-labelledby="masuk-h">
        <div className="wrap"><div className="akun__grid">
          <div>
            <h1 id="masuk-h">Masuk ke Cek Hatespeech</h1>
            <p className="lead">Mahasiswa masuk untuk membuat dan memantau laporan. Petugas Satgas PPKPT masuk untuk menangani laporan yang masuk.</p>
            <ul className="akun__poin">
              <li>Laporanmu hanya bisa dilihat olehmu dan Satgas PPKPT.</li>
              <li>Status laporan bisa dipantau kapan saja.</li>
              <li>PDF laporan tetap bisa diunduh untuk arsip.</li>
            </ul>
            <BelumSiap />
          </div>
          <div className="kartu">
            <FormMasuk lanjut={sp.lanjut?.startsWith('/') ? sp.lanjut : ''} />
          </div>
        </div></div>
      </section>
    </main>
  );
}
