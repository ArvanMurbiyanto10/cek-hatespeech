import { redirect } from 'next/navigation';
import { penggunaSaatIni } from '@/lib/auth';
import { BelumSiap } from '@/components/BelumSiap';
import { FormDaftar } from './FormDaftar';
import '../akun.css';

export const metadata = { title: 'Daftar' };

export default async function Halaman() {
  if (await penggunaSaatIni()) redirect('/laporan');
  return (
    <main id="top">
      <section className="doc-sec app-page akun" aria-labelledby="daftar-h">
        <div className="wrap"><div className="akun__grid">
          <div>
            <h1 id="daftar-h">Daftar akun mahasiswa</h1>
            <p className="lead">Akun dipakai untuk mengirim laporan ke Satgas PPKPT dan memantau statusnya. Data dirimu otomatis terisi di setiap laporan.</p>
            <ul className="akun__poin">
              <li>Gunakan email dan nomor WhatsApp yang aktif agar Satgas bisa menghubungimu.</li>
              <li>Akun petugas Satgas tidak dibuat di sini; akun mereka dibuat oleh admin aplikasi.</li>
            </ul>
            <BelumSiap />
          </div>
          <div className="kartu"><FormDaftar /></div>
        </div></div>
      </section>
    </main>
  );
}
