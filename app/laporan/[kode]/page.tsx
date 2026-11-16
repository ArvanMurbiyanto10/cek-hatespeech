import { notFound, redirect } from 'next/navigation';
import { penggunaSaatIni } from '@/lib/supabase/server';
import { ambilLaporanLengkap } from '@/lib/data';
import { deteksiKamus } from '@/lib/kamus';
import { PASAL, STATUS, type KodePasal } from '@/lib/kampus';
import { TombolPdf } from '@/components/TombolPdf';
import { FormStatus } from './FormStatus';
import '../laporan.css';

export const metadata = { title: 'Detail laporan' };

const JUDUL_AI: Record<string, string> = {
  hate: 'Indikasi kuat ujaran kebencian', tinjau: 'Perlu ditinjau manusia', abusive: 'Bahasa kasar', aman: 'Tidak terindikasi',
};
const tgl = (s: string, jam = false) =>
  new Date(s).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', ...(jam ? { hour: '2-digit', minute: '2-digit' } : {}) });
const persen = (x: number | null) => ((x ?? 0) * 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';

export default async function Halaman({ params, searchParams }: { params: Promise<{ kode: string }>; searchParams: Promise<{ baru?: string }> }) {
  const { kode } = await params;
  const { baru } = await searchParams;
  const user = await penggunaSaatIni();
  if (!user) redirect(`/masuk?lanjut=/laporan/${kode}`);
  const lap = await ambilLaporanLengkap(kode);
  if (!lap) notFound();
  const satgas = user.profil?.role === 'satgas';
  const p = lap.pelapor;

  return (
    <main id="top">
      <section className="doc-sec app-page" aria-labelledby="detail-h">
        <div className="wrap">
          {baru && <p className="pesan pesan--ok" style={{ marginTop: 0, marginBottom: 'var(--s-6)' }}>Laporan terkirim ke Satgas PPKPT. Simpan kode <b>{lap.kode}</b> dan pantau statusnya di halaman ini.</p>}
          <div className="detail__head">
            <div>
              <p className="detail__kode">{lap.kode}</p>
              <h1 id="detail-h">{satgas ? `Laporan dari ${p?.nama ?? 'pelapor'}` : 'Laporan perundunganmu'}</h1>
              <div className="detail__meta">
                <span className={`lencana lencana--${lap.status}`}>{STATUS[lap.status].label}</span>
                {satgas && <span className={`lencana lencana--${lap.prioritas}`}>Prioritas {lap.prioritas}</span>}
                <span className="lencana">Dikirim {tgl(lap.dibuat)}</span>
              </div>
            </div>
            <div className="btn-row">
              <a className="btn btn--secondary" href={satgas ? '/satgas' : '/laporan'}>{satgas ? 'Kembali ke dasbor' : 'Semua laporanku'}</a>
              <TombolPdf laporan={lap} />
            </div>
          </div>

          <div className="detail__grid">
            <div className="detail__kolom">
              <div className="kartu">
                <h2>Kejadian</h2>
                <dl className="rincian">
                  <dt>Tanggal kejadian</dt><dd>{tgl(lap.tanggal_kejadian + 'T00:00:00')}</dd>
                  <dt>Tempat atau aplikasi</dt><dd>{lap.tempat}</dd>
                  <dt>Frekuensi</dt><dd>{lap.frekuensi ?? '-'}</dd>
                  <dt>Bentuk</dt><dd>{lap.bentuk.join(', ')}</dd>
                  <dt>Terduga pelaku</dt><dd>{lap.terduga_pelaku || 'Tidak disebutkan'}</dd>
                  <dt>Melapor sebagai</dt><dd>{lap.peran === 'korban' ? 'Korban' : 'Saksi atau teman korban'}</dd>
                </dl>
                <p className="kronologi">{lap.kronologi}</p>
              </div>

              <div className="kartu">
                <h2>Bukti</h2>
                {lap.bukti.map((b) => (
                  <div key={b.id}>
                    {/* sorotan kamus dihitung ulang dari teks; HTML-nya sudah di-escape */}
                    <p className="bukti-teks" dangerouslySetInnerHTML={{ __html: deteksiKamus(b.isi_teks).html }} />
                    {b.ai_label ? (
                      <div className={`ai-ringkas ai-ringkas--${b.ai_label}`}>
                        <b>{JUDUL_AI[b.ai_label]}</b>
                        <span>Ujaran kebencian {persen(b.skor_hatespeech)} · bahasa kasar {persen(b.skor_abusive)}{b.temuan.length ? ` · kata disorot: ${b.temuan.map((t) => t.kata).join(', ')}` : ''}</span>
                      </div>
                    ) : (
                      <p className="pesan pesan--info">Pemeriksaan AI tidak tersedia saat laporan dikirim.</p>
                    )}
                  </div>
                ))}
                {!lap.bukti.length && <p style={{ margin: 0, color: '#5c5656' }}>Tidak ada teks bukti; lihat tangkapan layar.</p>}
                {lap.lampiran.length > 0 && (
                  <div className="galeri">
                    {lap.lampiran.map((l, i) => l.url && (
                      <a key={l.id} href={l.url} target="_blank" rel="noopener" aria-label={`Buka tangkapan layar ${i + 1}`}>
                        {/* eslint-disable-next-line @next/next/no-img-element */}
                        <img src={l.url} alt={`Tangkapan layar bukti ${i + 1}`} />
                      </a>
                    ))}
                  </div>
                )}
              </div>

              <div className="kartu">
                <h2>Pasal yang mungkin terkait</h2>
                {lap.laporan_pasal.map((lp) => {
                  const ps = PASAL[lp.pasal_kode as KodePasal];
                  if (!ps) return null;
                  return (
                    <div key={lp.pasal_kode} className={`pasal-item${lp.pasal_kode === 'kampus' ? ' pasal-item--kampus' : ''}`}>
                      <b>{ps.nama}</b>
                      <p>{ps.judul}. {lp.pasal_kode === 'kampus' ? 'Sanksi' : 'Ancaman pidana'}: {ps.ancaman.charAt(0).toLowerCase() + ps.ancaman.slice(1)}.</p>
                      <small>Dasar pemilihan: {lp.alasan}</small>
                    </div>
                  );
                })}
              </div>
            </div>

            <div className="detail__kolom">
              {satgas && <FormStatus id={lap.id} kode={lap.kode} status={lap.status} prioritas={lap.prioritas} />}
              {satgas && p && (
                <div className="kartu">
                  <h2>Data pelapor</h2>
                  <dl className="rincian">
                    <dt>Nama</dt><dd>{p.nama}</dd>
                    <dt>NIM</dt><dd>{p.nim ?? '-'}</dd>
                    <dt>Program studi</dt><dd>{p.prodi ?? '-'}{p.angkatan ? `, angkatan ${p.angkatan}` : ''}</dd>
                    <dt>WhatsApp</dt><dd>{p.kontak ?? '-'}</dd>
                    <dt>Email</dt><dd>{p.email ?? '-'}</dd>
                  </dl>
                </div>
              )}
              <div className="kartu">
                <h2>Riwayat penanganan</h2>
                <ol className="linimasa">
                  {lap.riwayat_status.map((r) => (
                    <li key={r.id}>
                      <b>{STATUS[r.status as keyof typeof STATUS]?.label ?? r.status}</b>
                      <time dateTime={r.dibuat}>{tgl(r.dibuat, true)}</time>
                      {r.catatan && <p>{r.catatan}</p>}
                    </li>
                  ))}
                </ol>
                {!satgas && <p className="pesan pesan--info" style={{ marginBottom: 0 }}>{STATUS[lap.status].info}</p>}
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
