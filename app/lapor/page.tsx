import { redirect } from 'next/navigation';
import { penggunaSaatIni } from '@/lib/supabase/server';
import { KAMPUS } from '@/lib/kampus';
import { BelumSiap } from '@/components/BelumSiap';
import { LaporForm } from './LaporForm';
import './lapor.css';

export const metadata = {
  title: 'Buat laporan perundungan',
  description: 'Kirim laporan dugaan perundungan ke Satgas PPKPT Telkom University Purwokerto. Bukti diperiksa model AI dan pasal UU ITE dipilih otomatis.',
};

export default async function Halaman() {
  const user = await penggunaSaatIni();
  if (!user) {
    return (
      <main id="top">
        <section className="doc-sec app-page"><div className="wrap"><h1>Buat laporan perundungan</h1><BelumSiap /></div></section>
      </main>
    );
  }
  if (user.profil?.role === 'satgas') redirect('/satgas');

  const p = user.profil;
  return (
    <main id="top">
      <section className="doc-sec lapor" aria-labelledby="lapor-h">
        <div className="wrap">
          <div className="lapor__head">
            <h1 id="lapor-h">Buat laporan perundungan</h1>
            <p>
              Isi empat bagian di bawah, lalu kirim. Laporan langsung masuk ke dasbor {KAMPUS.tujuan} dan hanya bisa dilihat olehmu dan
              petugas Satgas. Statusnya bisa kamu pantau di halaman Laporan saya.
            </p>
          </div>
          <LaporForm
            pelapor={{
              nama: p?.nama ?? '',
              nim: p?.nim ?? '',
              prodi: p?.prodi ?? '',
              angkatan: p?.angkatan ?? null,
              kontak: p?.kontak ?? '',
              email: user.email,
            }}
            uid={user.id}
          />
        </div>
      </section>
    </main>
  );
}
