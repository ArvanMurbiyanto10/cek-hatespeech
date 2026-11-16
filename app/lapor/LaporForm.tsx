'use client';
import { useEffect, useMemo, useRef, useState } from 'react';
import { BENTUK, FREKUENSI, KAMPUS, MAKS_KARAKTER, TEMPAT } from '@/lib/kampus';
import { pilihPasal } from '@/lib/pasal';
import { supabaseBrowser } from '@/lib/supabase/browser';
import { Panah } from '@/components/Panah';

interface Pelapor { nama: string; nim: string; prodi: string; angkatan: number | null; kontak: string; email: string }

interface HasilAI {
  label: 'hate' | 'tinjau' | 'abusive' | 'aman';
  judul: string;
  penjelasan: string;
  hs: number;
  ab: number;
  temuan: { kata: string; tipe: string; kode: string }[];
  html: string;
}

interface Gambar { blob: Blob; url: string; nama: string }

const IKON: Record<HasilAI['label'], string> = { hate: '!', tinjau: '?', abusive: '!', aman: '✓' };
const persen = (x: number) => (x * 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';

/** Perkecil gambar di browser (sisi terpanjang 1600 px, JPEG) sebelum diunggah ke Storage. */
async function perkecil(file: File): Promise<Blob> {
  const bmp = await createImageBitmap(file);
  const s = Math.min(1, 1600 / Math.max(bmp.width, bmp.height));
  const c = document.createElement('canvas');
  c.width = Math.round(bmp.width * s);
  c.height = Math.round(bmp.height * s);
  const x = c.getContext('2d')!;
  x.fillStyle = '#fff';
  x.fillRect(0, 0, c.width, c.height);
  x.drawImage(bmp, 0, 0, c.width, c.height);
  return new Promise((ok, gagal) => c.toBlob((b) => (b ? ok(b) : gagal(new Error('Gagal memproses gambar'))), 'image/jpeg', 0.85));
}

export function LaporForm({ pelapor, uid }: { pelapor: Pelapor; uid: string }) {
  const idLaporan = useRef<string>(crypto.randomUUID());
  const [peran, setPeran] = useState('');
  const [tanggal, setTanggal] = useState('');
  const [frekuensi, setFrekuensi] = useState<string>(FREKUENSI[0]);
  const [tempat, setTempat] = useState('');
  const [tempatLain, setTempatLain] = useState('');
  const [bentuk, setBentuk] = useState<string[]>([]);
  const [pelaku, setPelaku] = useState('');
  const [kronologi, setKronologi] = useState('');
  const [bukti, setBukti] = useState('');
  const [gambar, setGambar] = useState<Gambar[]>([]);
  const [setuju, setSetuju] = useState(false);

  const [ai, setAi] = useState<HasilAI | null>(null);
  const [teksDiperiksa, setTeksDiperiksa] = useState('');
  const [galatAi, setGalatAi] = useState('');
  const [memeriksa, setMemeriksa] = useState(false);
  const [salah, setSalah] = useState<string[]>([]);
  const [mengirim, setMengirim] = useState('');
  const [galatKirim, setGalatKirim] = useState('');
  const hariIni = useMemo(() => new Date().toISOString().slice(0, 10), []);

  const aiBerlaku = ai && bukti.trim() === teksDiperiksa ? ai : null;
  const pasal = pilihPasal(aiBerlaku, bentuk);
  const kotor = Boolean(kronologi || bukti || gambar.length);

  useEffect(() => {
    const cegah = (e: BeforeUnloadEvent) => { if (kotor && !mengirim) e.preventDefault(); };
    window.addEventListener('beforeunload', cegah);
    return () => window.removeEventListener('beforeunload', cegah);
  }, [kotor, mengirim]);

  async function periksa() {
    const teks = bukti.trim();
    if (!teks) return setGalatAi('Tempel dulu teks bukti yang ingin diperiksa.');
    setMemeriksa(true);
    setGalatAi('');
    try {
      const r = await fetch('/api/periksa', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ teks }) });
      const d = (await r.json()) as { ok: boolean; hasil?: HasilAI; galat?: string };
      if (!d.ok || !d.hasil) setGalatAi(d.galat ?? 'Pemeriksaan gagal.');
      else { setAi(d.hasil); setTeksDiperiksa(teks); }
    } catch {
      setGalatAi('Tidak bisa menghubungi server. Periksa koneksi internet, lalu coba lagi.');
    } finally {
      setMemeriksa(false);
    }
  }

  async function tambahGambar(e: React.ChangeEvent<HTMLInputElement>) {
    const files = Array.from(e.target.files ?? []);
    e.target.value = '';
    const baru: Gambar[] = [];
    for (const f of files) {
      if (gambar.length + baru.length >= 3) { alert('Maksimal 3 tangkapan layar.'); break; }
      if (!/^image\/(png|jpe?g|webp)$/.test(f.type)) { alert(`${f.name} bukan gambar PNG, JPG atau WebP.`); continue; }
      const blob = await perkecil(f);
      baru.push({ blob, url: URL.createObjectURL(blob), nama: f.name });
    }
    setGambar((g) => [...g, ...baru]);
  }

  function hapusGambar(i: number) {
    setGambar((g) => { URL.revokeObjectURL(g[i].url); return g.filter((_, j) => j !== i); });
  }

  function validasi(): string[] {
    const s: string[] = [];
    if (!peran) s.push('Pilih kamu melapor sebagai korban atau saksi.');
    if (!tanggal) s.push('Isi tanggal kejadian.');
    if (!tempat) s.push('Pilih tempat atau aplikasi kejadian.');
    if (tempat === 'Lainnya' && !tempatLain.trim()) s.push('Tulis tempat kejadiannya.');
    if (!bentuk.length) s.push('Pilih minimal satu bentuk perundungan.');
    if (!kronologi.trim()) s.push('Ceritakan kronologi singkatnya.');
    if (!bukti.trim() && !gambar.length) s.push('Tempel teks bukti atau lampirkan tangkapan layar.');
    if (!setuju) s.push('Centang pernyataan kebenaran laporan.');
    return s;
  }

  async function kirim(e: React.FormEvent) {
    e.preventDefault();
    const s = validasi();
    setSalah(s);
    if (s.length) return;
    setGalatKirim('');
    try {
      // 1. unggah tangkapan layar ke Storage (folder milik pelapor)
      const sb = supabaseBrowser();
      const paths: string[] = [];
      for (const [i, g] of gambar.entries()) {
        setMengirim(`Mengunggah tangkapan layar ${i + 1} dari ${gambar.length}…`);
        const path = `${uid}/${idLaporan.current}/${i + 1}.jpg`;
        const { error } = await sb.storage.from('lampiran').upload(path, g.blob, { contentType: 'image/jpeg', upsert: true });
        if (error) throw new Error(`Tangkapan layar gagal diunggah: ${error.message}`);
        paths.push(path);
      }
      // 2. kirim laporan; AI dihitung ulang di server
      setMengirim('Menyimpan laporan dan memeriksa bukti dengan AI…');
      const r = await fetch('/api/laporan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idLaporan.current, peran, tanggal, tempat, tempatLain, frekuensi, bentuk, pelaku, kronologi, bukti, lampiran: paths, setuju }),
      });
      const d = (await r.json()) as { ok: boolean; kode?: string; galat?: string };
      if (!d.ok || !d.kode) throw new Error(d.galat ?? 'Laporan gagal dikirim.');
      window.location.href = `/laporan/${d.kode}?baru=1`;
    } catch (err) {
      setMengirim('');
      setGalatKirim(err instanceof Error ? err.message : 'Laporan gagal dikirim.');
    }
  }

  const cek = {
    diri: Boolean(pelapor.nama && pelapor.nim && peran),
    kejadian: Boolean(tanggal && tempat && (tempat !== 'Lainnya' || tempatLain.trim()) && bentuk.length && kronologi.trim()),
    bukti: Boolean(bukti.trim() || gambar.length),
    ai: Boolean(aiBerlaku),
    setuju,
  };

  return (
    <div className="lapor__grid">
      <form onSubmit={kirim} noValidate autoComplete="off">
        <fieldset className="bag">
          <legend><span>1</span>Data pelapor</legend>
          <p className="bag__info">Diambil dari akunmu dan hanya terlihat oleh Satgas.</p>
          <dl className="data-diri">
            <div><dt>Nama</dt><dd>{pelapor.nama || '-'}</dd></div>
            <div><dt>NIM</dt><dd>{pelapor.nim || '-'}</dd></div>
            <div><dt>Program studi</dt><dd>{pelapor.prodi || '-'}{pelapor.angkatan ? `, angkatan ${pelapor.angkatan}` : ''}</dd></div>
            <div><dt>Kontak</dt><dd>{pelapor.kontak || pelapor.email}</dd></div>
          </dl>
          <div className="f" style={{ marginTop: 18 }}>
            <span className="f__label">Kamu melapor sebagai <em>*</em></span>
            <div className="pilih">
              <label><input type="radio" name="peran" value="korban" checked={peran === 'korban'} onChange={() => setPeran('korban')} /> Korban</label>
              <label><input type="radio" name="peran" value="saksi" checked={peran === 'saksi'} onChange={() => setPeran('saksi')} /> Saksi atau teman korban</label>
            </div>
          </div>
        </fieldset>

        <fieldset className="bag">
          <legend><span>2</span>Kejadian</legend>
          <p className="bag__info">Tulis seingatmu. Detail kecil seperti jam atau nama grup membantu Satgas menindaklanjuti.</p>
          <div className="isian">
            <div className="f"><label htmlFor="tanggal">Tanggal kejadian <em>*</em></label><input type="date" id="tanggal" max={hariIni} value={tanggal} onChange={(e) => setTanggal(e.target.value)} /><small>Kalau berulang, isi tanggal kejadian terakhir.</small></div>
            <div className="f"><label htmlFor="frekuensi">Sudah berapa kali?</label><select id="frekuensi" value={frekuensi} onChange={(e) => setFrekuensi(e.target.value)}>{FREKUENSI.map((x) => <option key={x}>{x}</option>)}</select></div>
            <div className="f"><label htmlFor="tempat">Tempat atau aplikasi <em>*</em></label><select id="tempat" value={tempat} onChange={(e) => setTempat(e.target.value)}><option value="">Pilih</option>{TEMPAT.map((x) => <option key={x}>{x}</option>)}</select></div>
            {tempat === 'Lainnya' && <div className="f"><label htmlFor="tempat-lain">Tulis tempatnya <em>*</em></label><input type="text" id="tempat-lain" maxLength={80} value={tempatLain} onChange={(e) => setTempatLain(e.target.value)} /></div>}
            <div className="f penuh"><span className="f__label">Bentuk perundungan <em>*</em></span>
              <div className="pilih">
                {BENTUK.map((b) => (
                  <label key={b}><input type="checkbox" checked={bentuk.includes(b)} onChange={(e) => setBentuk((x) => (e.target.checked ? [...x, b] : x.filter((y) => y !== b)))} /> {b}</label>
                ))}
              </div>
            </div>
            <div className="f penuh"><label htmlFor="pelaku">Terduga pelaku</label><input type="text" id="pelaku" maxLength={120} placeholder="Nama atau nama akun, boleh dikosongkan" value={pelaku} onChange={(e) => setPelaku(e.target.value)} /></div>
            <div className="f penuh"><label htmlFor="kronologi">Kronologi singkat <em>*</em></label><textarea id="kronologi" maxLength={1500} placeholder="Apa yang terjadi, siapa saja yang ada, dan apa dampaknya untukmu." value={kronologi} onChange={(e) => setKronologi(e.target.value)} /><span className="hitung">{kronologi.length.toLocaleString('id-ID')} / 1.500</span></div>
          </div>
        </fieldset>

        <fieldset className="bag">
          <legend><span>3</span>Bukti</legend>
          <p className="bag__info">Salin teks pesan atau komentar yang menyerangmu. Model AI menilai skornya, lalu pasal yang mungkin terkait dipilih otomatis.</p>
          <div className="f">
            <label htmlFor="bukti">Teks pesan atau komentar</label>
            <textarea id="bukti" maxLength={MAKS_KARAKTER} placeholder="Tempel di sini, misalnya: besok kalau dia masih nongol di lab, kita kerjain lagi" value={bukti} onChange={(e) => setBukti(e.target.value)} />
            <span className="hitung">{bukti.length.toLocaleString('id-ID')} / {MAKS_KARAKTER.toLocaleString('id-ID')}</span>
          </div>
          <div className="periksa-row">
            <button className="btn btn--secondary" type="button" id="tombol-ai" onClick={periksa} disabled={memeriksa}>{memeriksa ? 'Memeriksa…' : ai ? 'Periksa ulang' : 'Periksa dengan AI'} <Panah /></button>
          </div>
          {galatAi && <p className="pesan pesan--galat" role="alert">{galatAi}</p>}
          {ai && !aiBerlaku && <p className="pesan pesan--basi">Teks bukti berubah setelah diperiksa. Tekan Periksa ulang agar hasilnya sesuai.</p>}
          {ai && (
            <div className={`ai ai--${ai.label}`} aria-live="polite">
              <div className="ai__vonis"><span className="ai__ikon" aria-hidden="true">{IKON[ai.label]}</span><div><p className="ai__judul">{ai.judul}</p>{ai.penjelasan && <p className="ai__sub">{ai.penjelasan}</p>}</div></div>
              <div className="ai__isi">
                <div className="skor">
                  <div><span>Skor ujaran kebencian</span><b>{persen(ai.hs)}</b><i style={{ ['--v' as string]: `${(ai.hs * 100).toFixed(2)}%` }} /></div>
                  <div><span>Skor bahasa kasar</span><b>{persen(ai.ab)}</b><i style={{ ['--v' as string]: `${(ai.ab * 100).toFixed(2)}%` }} /></div>
                </div>
                {/* html berasal dari server dan sudah di-escape */}
                <p className="ai__teks" dangerouslySetInnerHTML={{ __html: ai.html }} />
                <p className="ai__catatan">Penilaian model AI, bukan putusan. Hasil apa pun tetap boleh kamu laporkan.</p>
              </div>
            </div>
          )}

          <div className="pasal-box" aria-live="polite">
            <p className="pasal-box__judul">Pasal yang mungkin terkait</p>
            <p className="pasal-box__info">Dipilih otomatis dari hasil AI dan bentuk perundungan yang kamu centang, lalu ikut tersimpan di laporan. Penentuan pasal tetap wewenang Satgas dan aparat penegak hukum.</p>
            {pasal.map((d) => (
              <div key={d.kode} className={`lp-pasal${d.kode === 'kampus' ? ' lp-pasal--kampus' : ''}`}>
                <h4>{d.pasal.nama}</h4>
                <p>{d.pasal.judul}. {d.pasal.isi}</p>
                <p className="lp-pasal__ancaman">{d.kode === 'kampus' ? 'Sanksi: ' : 'Ancaman pidana: '}{d.pasal.ancaman}.</p>
                {d.pasal.catatan && <p style={{ color: '#5c5656' }}>{d.pasal.catatan}</p>}
                <p className="lp-pasal__alasan">{d.alasan.map((a) => <span key={a}>{a}</span>)}</p>
                <a href={d.pasal.tautan} target="_blank" rel="noopener">Baca unsur pasalnya</a>
              </div>
            ))}
          </div>

          <div className="f" style={{ marginTop: 'var(--s-6)' }}>
            <span className="f__label">Tangkapan layar</span>
            <label className="unggah"><input type="file" accept="image/png,image/jpeg,image/webp" multiple onChange={tambahGambar} /><b>Pilih gambar</b><span>{gambar.length ? `${gambar.length} dari 3 terlampir` : 'PNG, JPG atau WebP, maksimal 3'}</span><small>Disimpan privat; hanya kamu dan Satgas yang bisa membukanya.</small></label>
            {gambar.length > 0 && (
              <div className="thumbs">
                {gambar.map((g, i) => (
                  <div className="thumb" key={g.url}>
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img src={g.url} alt={`Tangkapan layar ${i + 1}`} />
                    <button type="button" onClick={() => hapusGambar(i)}>Hapus</button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </fieldset>

        <fieldset className="bag">
          <legend><span>4</span>Pernyataan</legend>
          <p className="bag__info">Laporan yang jujur membantu Satgas bertindak cepat.</p>
          <label className="setuju"><input type="checkbox" checked={setuju} onChange={(e) => setSetuju(e.target.checked)} /> Saya menyatakan informasi dalam laporan ini benar, menyetujui laporan ini disimpan untuk ditangani {KAMPUS.tujuan}, dan bersedia dihubungi untuk keterangan lebih lanjut.</label>
        </fieldset>

        <button type="submit" id="kirim-bawah" hidden>Kirim</button>
      </form>

      <aside className="ringkas" aria-label="Kelengkapan laporan">
        <div className="ringkas__head"><b>Kelengkapan laporan</b><small>Untuk {KAMPUS.tujuan}</small></div>
        <ol>
          {([['diri', '1. Data pelapor'], ['kejadian', '2. Kejadian'], ['bukti', '3. Bukti'], ['ai', 'Pemeriksaan AI'], ['setuju', '4. Pernyataan']] as const).map(([k, l]) => (
            <li key={k} className={cek[k] ? 'ok' : ''}><span>{l}</span><span>{cek[k] ? 'Lengkap' : k === 'ai' ? 'Opsional' : 'Belum'}</span></li>
          ))}
        </ol>
        {salah.length > 0 && <ul className="salah-list" role="alert">{salah.map((s) => <li key={s}>{s}</li>)}</ul>}
        <div className="ringkas__aksi">
          <button className="btn btn--primary" type="button" disabled={Boolean(mengirim)} onClick={() => document.getElementById('kirim-bawah')?.click()}>
            {mengirim ? 'Mengirim…' : 'Kirim laporan'}<Panah />
          </button>
          {mengirim && <p>{mengirim}</p>}
          {galatKirim && <p className="pesan pesan--galat" role="alert" style={{ margin: 0 }}>{galatKirim}</p>}
          <p>Setelah dikirim, kamu mendapat kode laporan dan bisa mengunduh PDF-nya.</p>
        </div>
      </aside>
    </div>
  );
}
