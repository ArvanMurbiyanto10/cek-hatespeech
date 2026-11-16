import { redirect } from 'next/navigation';
import { penggunaSaatIni, supabaseServer } from '@/lib/supabase/server';
import { STATUS } from '@/lib/kampus';
import type { Laporan } from '@/lib/types';
import { Panah } from '@/components/Panah';
import './laporan.css';

export const metadata = { title: 'Laporan saya' };

const tgl = (s: string) => new Date(s).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

export default async function Halaman() {
  const user = await penggunaSaatIni();
  if (!user) redirect('/masuk?lanjut=/laporan');
  if (user.profil?.role === 'satgas') redirect('/satgas');

  const sb = await supabaseServer();
  const { data } = await sb
    .from('laporan')
    .select('id, kode, tempat, bentuk, status, dibuat, diubah')
    .order('dibuat', { ascending: false });
  const daftar = (data ?? []) as Pick<Laporan, 'id' | 'kode' | 'tempat' | 'bentuk' | 'status' | 'dibuat' | 'diubah'>[];

  return (
    <main id="top">
      <section className="doc-sec app-page" aria-labelledby="saya-h">
        <div className="wrap">
          <div className="detail__head">
            <div>
              <h1 id="saya-h">Laporan saya</h1>
              <p className="lead">Halo, {user.profil?.nama || 'mahasiswa'}. Pantau status setiap laporan yang sudah kamu kirim ke Satgas PPKPT.</p>
            </div>
            <a className="btn btn--primary" href="/lapor">Buat laporan baru<Panah /></a>
          </div>

          {daftar.length ? (
            <div className="tabel-wrap">
              <table className="tabel">
                <thead><tr><th>Kode</th><th>Dikirim</th><th>Tempat</th><th>Bentuk</th><th>Status</th><th>Diperbarui</th></tr></thead>
                <tbody>
                  {daftar.map((l) => (
                    <tr key={l.id}>
                      <td><a href={`/laporan/${l.kode}`}>{l.kode}</a></td>
                      <td className="muted-sel">{tgl(l.dibuat)}</td>
                      <td>{l.tempat}</td>
                      <td className="muted-sel">{l.bentuk.join(', ')}</td>
                      <td><span className={`lencana lencana--${l.status}`}>{STATUS[l.status].label}</span></td>
                      <td className="muted-sel">{tgl(l.diubah)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="kosong">
              <p>Kamu belum mengirim laporan.</p>
              <a className="btn btn--primary" href="/lapor">Buat laporan<Panah /></a>
            </div>
          )}
        </div>
      </section>
    </main>
  );
}
