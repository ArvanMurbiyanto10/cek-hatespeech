import { redirect } from 'next/navigation';
import { penggunaSaatIni, supabaseServer } from '@/lib/supabase/server';
import { DAFTAR_STATUS, STATUS, type StatusLaporan } from '@/lib/kampus';
import type { Laporan } from '@/lib/types';
import '../laporan/laporan.css';

export const metadata = { title: 'Dasbor Satgas' };

type Baris = Pick<Laporan, 'id' | 'kode' | 'tempat' | 'bentuk' | 'status' | 'prioritas' | 'dibuat' | 'diubah'> & {
  pelapor: { nama: string; nim: string | null } | null;
};

const URUT_PRIORITAS = { tinggi: 0, sedang: 1, normal: 2 } as const;
const tgl = (s: string) => new Date(s).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

function hitung<T extends string>(nilai: T[]): [T, number][] {
  const m = new Map<T, number>();
  nilai.forEach((v) => m.set(v, (m.get(v) ?? 0) + 1));
  return [...m.entries()].sort((a, b) => b[1] - a[1]);
}

export default async function Halaman({ searchParams }: { searchParams: Promise<{ status?: string }> }) {
  const user = await penggunaSaatIni();
  if (!user) redirect('/masuk?lanjut=/satgas');
  if (user.profil?.role !== 'satgas') redirect('/laporan');

  const { status } = await searchParams;
  const filter = DAFTAR_STATUS.includes(status as StatusLaporan) ? (status as StatusLaporan) : null;

  const sb = await supabaseServer();
  const { data } = await sb
    .from('laporan')
    .select('id, kode, tempat, bentuk, status, prioritas, dibuat, diubah, pelapor:profiles(nama, nim)')
    .order('dibuat', { ascending: false });
  const semua = (data ?? []) as unknown as Baris[];

  // Laporan aktif diurutkan berdasarkan prioritas, lalu yang terbaru.
  const tampil = semua
    .filter((l) => (filter ? l.status === filter : !['selesai', 'ditolak'].includes(l.status)))
    .sort((a, b) => URUT_PRIORITAS[a.prioritas] - URUT_PRIORITAS[b.prioritas] || b.dibuat.localeCompare(a.dibuat));
  const perStatus = Object.fromEntries(DAFTAR_STATUS.map((s) => [s, semua.filter((l) => l.status === s).length])) as Record<StatusLaporan, number>;
  const perBentuk = hitung(semua.flatMap((l) => l.bentuk));
  const perTempat = hitung(semua.map((l) => l.tempat));
  const maks = Math.max(1, ...perBentuk.map((x) => x[1]), ...perTempat.map((x) => x[1]));

  return (
    <main id="top">
      <section className="doc-sec app-page" aria-labelledby="satgas-h">
        <div className="wrap">
          <h1 id="satgas-h">Dasbor Satgas PPKPT</h1>
          <p className="lead">{semua.length} laporan tercatat. Laporan aktif diurutkan dari prioritas tertinggi: ancaman dan indikasi kuat ujaran kebencian tampil paling atas.</p>

          <nav className="statistik" aria-label="Saring menurut status">
            {DAFTAR_STATUS.map((s) => (
              <a key={s} href={filter === s ? '/satgas' : `/satgas?status=${s}`} aria-current={filter === s ? 'true' : undefined}>
                <b>{perStatus[s]}</b><span>{STATUS[s].label}</span>
              </a>
            ))}
          </nav>

          <h2 style={{ marginTop: 'var(--s-8)', fontSize: 24 }}>{filter ? `Laporan ${STATUS[filter].label.toLowerCase()}` : 'Laporan aktif'}</h2>
          {tampil.length ? (
            <div className="tabel-wrap" style={{ marginTop: 'var(--s-5)' }}>
              <table className="tabel">
                <thead><tr><th>Prioritas</th><th>Kode</th><th>Pelapor</th><th>Bentuk</th><th>Tempat</th><th>Status</th><th>Dikirim</th></tr></thead>
                <tbody>
                  {tampil.map((l) => (
                    <tr key={l.id}>
                      <td><span className={`lencana lencana--${l.prioritas}`}>{l.prioritas}</span></td>
                      <td><a href={`/laporan/${l.kode}`}>{l.kode}</a></td>
                      <td>{l.pelapor?.nama ?? '-'}<br /><span className="muted-sel">{l.pelapor?.nim ?? ''}</span></td>
                      <td className="muted-sel">{l.bentuk.join(', ')}</td>
                      <td className="muted-sel">{l.tempat}</td>
                      <td><span className={`lencana lencana--${l.status}`}>{STATUS[l.status].label}</span></td>
                      <td className="muted-sel">{tgl(l.dibuat)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="kosong"><p>Tidak ada laporan di daftar ini.</p></div>
          )}

          <div className="rekap">
            <div className="kartu">
              <h2>Bentuk perundungan</h2>
              {perBentuk.length ? perBentuk.map(([n, j]) => (
                <div className="batang" key={n}><span>{n}</span><b>{j}</b><i style={{ ['--v' as string]: `${(j / maks) * 100}%` }} /></div>
              )) : <p style={{ margin: 0, color: '#5c5656' }}>Belum ada data.</p>}
            </div>
            <div className="kartu">
              <h2>Tempat atau aplikasi</h2>
              {perTempat.length ? perTempat.map(([n, j]) => (
                <div className="batang" key={n}><span>{n}</span><b>{j}</b><i style={{ ['--v' as string]: `${(j / maks) * 100}%` }} /></div>
              )) : <p style={{ margin: 0, color: '#5c5656' }}>Belum ada data.</p>}
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
