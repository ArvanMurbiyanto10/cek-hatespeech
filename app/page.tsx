import type { KodePasal } from '@/lib/kampus';
import { Fragment } from 'react';
import { KAMPUS, PASAL } from '@/lib/kampus';
import './beranda.css';


const kontak = (
  [
    ['Email', KAMPUS.email],
    ['WhatsApp', KAMPUS.whatsapp],
    ['Tempat', KAMPUS.tempat],
  ] as [string, string][]
).filter(([, v]) => v !== '');

const KARTU_PASAL: [KodePasal, string][] = [
  ['28-2', 'Kalau kamu diserang karena suku, agama atau ras'],
  ['29', 'Kalau kamu diancam lewat pesan'],
  ['27a', 'Kalau kamu dihina atau dituduh di depan umum'],
];

const lcfirst = (s: string) => s.charAt(0).toLowerCase() + s.slice(1);

export default function Halaman() {
  return (
<main id="top">

  {/* HERO (split): teks di kiri, foto mahasiswi yang membaca pesan di kanan */}
  <section className="hero hero--split" data-enter="" aria-labelledby="hero-h">
    <div className="wrap"><div className="hero-text grid hero__grid">
      <div className="hero__text span-5">
        <p className="hero__kicker"><span className="dot" aria-hidden="true"></span>Untuk mahasiswa Telkom University Purwokerto</p>
        <h1 id="hero-h">Lapor perundungan di Telkom University Purwokerto</h1>
        <p className="hero__lead">Tempel pesan atau komentar yang menyerangmu. AI menilai isinya, pasal UU ITE yang mungkin terkait dipilih otomatis, dan laporanmu langsung masuk ke {KAMPUS.tujuan}.</p>
        <div className="btn-row">
          <a className="btn btn--primary" href="/lapor">Buat laporan<svg className="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path className="m-arrow__stem" d="M1 6h8"/><path className="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a>
          <a className="btn btn--secondary" href="#tanda">Kenali tanda perundungan</a>
        </div>
        <p className="hero__fakta"><span>Gratis dengan akun mahasiswa</span><span>Hanya kamu dan Satgas yang bisa membacanya</span><span>Status laporan bisa dipantau</span></p>
      </div>
      <div className="hero__media span-6 start-7">
        <img src="/assets/img/hate-speech.jpg" alt="Mahasiswi menunduk sambil membaca pesan di ponselnya" fetchPriority="high" />
        <div className="ly-float">
          <div className="ly-float__row"><span className="ly-live">Status laporan</span><span>Diverifikasi</span></div>
          <div className="ly-float__title">Langsung masuk ke dasbor {KAMPUS.tujuan}</div>
        </div>
      </div>
    </div></div>
  </section>

  {/* TANDA: contoh percakapan grup kelas dan lima tanda perundungan */}
  <section className="doc-sec" id="tanda" aria-labelledby="tanda-h">
    <div className="wrap">
      <div className="sec-head">
        <h2 id="tanda-h">Tanda kamu sedang dirundung</h2>
        <p>Perundungan sering dianggap bercanda oleh pelakunya. Kalau perlakuan ini membuatmu takut, malu atau enggan datang ke kampus, itu sudah cukup alasan untuk melapor.</p>
      </div>
      <div className="tanda">
        <div className="chat" role="img" aria-label="Contoh percakapan grup kelas: seorang mahasiswa dikucilkan dari kelompok, logatnya diejek, fotonya disebar dan ia diancam">
          <div className="chat__bar"><span className="chat__ava" aria-hidden="true">IF</span><div><b>Grup Kelas IF-23-B</b><small>38 anggota</small></div></div>
          <div className="chat__body" aria-hidden="true">
            <p className="chat__info">Hari ini</p>
            <div className="msg"><b>Raka</b>Kelompok 3 udah penuh ya. Dimas ga usah diajak, nanti nyusahin<time>20.14</time><span className="msg__tag">Pengucilan</span></div>
            <div className="msg"><b>Sinta</b>wkwk setuju, ngomongnya ngapak banget pas presentasi, malu-maluin<time>20.15</time><span className="msg__tag">Ejekan logat</span></div>
            <div className="msg msg--foto"><b>Raka</b><div className="msg__img">Foto Dimas saat presentasi</div>liat mukanya pas ditanya dosen<time>20.17</time><span className="msg__tag">Foto disebar</span></div>
            <div className="msg"><b>Bayu</b>besok kalau dia masih nongol di lab, kita kerjain lagi<time>20.21</time><span className="msg__tag">Ancaman</span></div>
          </div>
        </div>
        <div>
          <ul className="tanda__list">
            <li><span>1</span><div><h3>Dihina berulang di grup kelas atau angkatan</h3><p>Ejekan soal logat, fisik, nilai atau asal daerah yang terus diulang.</p></div></li>
            <li><span>2</span><div><h3>Diserang karena suku, agama atau ras</h3><p>Kalimat yang merendahkan identitasmu termasuk ujaran kebencian.</p></div></li>
            <li><span>3</span><div><h3>Sengaja dikucilkan</h3><p>Tidak diajak kelompok tugas, dikeluarkan dari grup atau disuruh menjauh.</p></div></li>
            <li><span>4</span><div><h3>Foto atau data pribadimu disebar</h3><p>Diedit, dijadikan bahan tertawaan atau diunggah tanpa izin.</p></div></li>
            <li><span>5</span><div><h3>Diancam lewat pesan atau langsung</h3><p>Ancaman untuk "dikerjain", dipukul atau dipermalukan.</p></div></li>
          </ul>
          <p className="tanda__catatan">Satu kejadian yang berat tetap boleh dilaporkan. Kamu tidak harus menunggu sampai terjadi berulang kali.</p>
        </div>
      </div>
    </div>
  </section>

  {/* ALUR: empat langkah dan pratinjau PDF yang dihasilkan */}
  <section className="doc-sec ground-surface" id="alur" aria-labelledby="alur-h">
    <div className="wrap">
      <div className="alur">
        <div>
          <h2 id="alur-h" style={{'marginBottom': 'var(--s-6)'} as React.CSSProperties}>4 langkah dari bukti ke laporan</h2>
          <ol className="alur__steps">
            <li><h3>Masuk dengan akun mahasiswa</h3><p>Nama, NIM, program studi dan kontak terisi otomatis dari akunmu.</p></li>
            <li><h3>Ceritakan kejadiannya</h3><p>Kapan, di mana atau lewat aplikasi apa, dan siapa yang terlibat kalau kamu tahu.</p></li>
            <li><h3>Tempel bukti, AI memeriksa</h3><p>Salin pesan atau komentarnya. Model XLM-RoBERTa memberi skor ujaran kebencian dan kata kasar, lalu aplikasi menunjukkan pasal UU ITE yang mungkin terkait.</p></li>
            <li><h3>Kirim, lalu pantau statusnya</h3><p>Satgas memverifikasi dan menindaklanjuti laporanmu. Setiap perubahan status dan catatan Satgas muncul di Laporan saya, dan PDF-nya bisa diunduh.</p></li>
          </ol>
          <p className="btn-row" style={{'marginTop': 'var(--s-6)'} as React.CSSProperties}><a className="btn btn--primary" href="/lapor">Buat laporan<svg className="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path className="m-arrow__stem" d="M1 6h8"/><path className="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div className="kertas-wrap">
          <div className="kertas" role="img" aria-label="Contoh halaman PDF laporan dugaan perundungan berisi data pelapor, bukti dan hasil pemeriksaan AI">
            <div className="kertas__pita"></div>
            <h4>LAPORAN DUGAAN PERUNDUNGAN</h4>
            <p className="kertas__sub">Kepada: {KAMPUS.tujuan}<br />No. LP-20261111-2014</p>
            <p className="kertas__judul">1. Data pelapor</p>
            <dl><dt>Nama</dt><dd>Dimas Pratama</dd><dt>NIM</dt><dd>23111020xx</dd><dt>Program studi</dt><dd>S1 Teknik Informatika</dd><dt>Melapor sebagai</dt><dd>Korban</dd></dl>
            <p className="kertas__judul">2. Kejadian</p>
            <dl><dt>Tanggal</dt><dd>10 November 2026</dd><dt>Tempat</dt><dd>Grup WhatsApp kelas</dd></dl>
            <p className="kertas__judul">3. Bukti dan pemeriksaan AI</p>
            <p className="kertas__kutip">"Kaum mereka selalu bikin masalah di kampung ini"</p>
            <div className="kertas__status"><span>Perlu ditinjau manusia</span><span>65,21%</span></div>
            <div className="kertas__bar"><i></i></div>
            <p className="kertas__judul">4. Dasar hukum yang mungkin terkait</p>
            <p>UU ITE Pasal 28 ayat (2) jo. Pasal 45A ayat (2)<br />Permendikbudristek No. 55 Tahun 2024</p>
            <div className="kertas__ttd"><div>Purwokerto, 11 November 2026<span></span>( Dimas Pratama )</div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {/* PASAL: UU ITE yang bisa menjerat pelaku, dipilih otomatis di laporan */}
  <section className="doc-sec" id="pasal" aria-labelledby="pasal-h">
    <div className="wrap">
      <div className="sec-head">
        <h2 id="pasal-h">Pasal UU ITE yang bisa menjerat pelaku</h2>
        <p>Saat kamu membuat laporan, aplikasi memilih pasal yang mungkin terkait dari hasil AI dan bentuk perundungan yang kamu centang, lalu mencetaknya di PDF.</p>
      </div>
      <div className="pasal-grid">
{KARTU_PASAL.map(([k, kapan]) => { const p = PASAL[k]; return (
        <article className="pasal-kartu" key={k}>
          <span className="kapan">{kapan}</span>
          <h3>{p.nama}</h3>
          <p className="sub">{p.judul}</p>
          <p className="isi">{p.isi}</p>
          <p className="ancaman"><span>{p.ancaman}</span></p>
          {p.catatan && <p className="catatan">{p.catatan}</p>}
        </article>
); })}
      </div>
      <div className="pasal-kampus">
        <p><b>{PASAL.kampus.nama}</b> mewajibkan kampus menangani perundungan meski tidak ada unsur pidana. Sanksi bagi pelaku: {lcfirst(PASAL.kampus.ancaman)}.</p>
        <a className="link" href="/dasar-hukum">Baca semua pasal dan unsurnya</a>
      </div>
    </div>
  </section>

  {/* SERAHKAN: tujuan laporan di kampus, di pita merah */}
  <section className="doc-sec ground-inverse" id="serahkan" aria-labelledby="serahkan-h">
    <div className="wrap">
      <div className="sec-head">
        <h2 id="serahkan-h">Siapa yang menerima laporanmu</h2>
        <p>Laporan masuk ke dasbor Satgas PPKPT. Satgas memeriksa, memanggil pihak terkait bila perlu, dan mencatat setiap langkah yang bisa kamu lihat.</p>
      </div>
      <div className="tujuan">
        <article className="utama">
          <span className="label">Tujuan utama</span>
          <h3>{KAMPUS.tujuan}</h3>
          <p>{KAMPUS.tujuanLengkap} menerima dan menangani laporan kekerasan di kampus, termasuk perundungan.</p>
          {kontak.length ? (<>
            <dl>{kontak.map(([k, v]) => <Fragment key={k}><dt>{k}</dt><dd>{v}</dd></Fragment>)}</dl>
          </>) : (<>
            <p className="tujuan__kosong">Tanyakan kontak resmi Satgas ke bagian kemahasiswaan atau lewat akun resmi kampus.</p>
          </>)}
        </article>
        <article>
          <span className="label">Atau</span>
          <h3>Dosen wali atau Kaprodi</h3>
          <p>Kalau kamu belum siap ke Satgas, dosen wali bisa mendampingimu melapor.</p>
        </article>
        <article>
          <span className="label">Pendampingan</span>
          <h3>Layanan konseling</h3>
          <p>Untuk bercerita dan menenangkan diri, sebelum atau sesudah melapor.</p>
        </article>
      </div>
      <p className="aturan">Kalau pelaku juga perlu diproses pidana menurut UU ITE, Satgas dapat mendampingimu melapor ke kepolisian. <a href="/dasar-hukum">Baca dasar hukumnya</a></p>
    </div>
  </section>

  {/* PRIVASI: apa yang dikirim dan tidak */}
  <section className="doc-sec" id="privasi" aria-labelledby="privasi-h">
    <div className="wrap">
      <div className="privasi">
        <div className="privasi__foto"><img src="/assets/img/hero.jpg" alt="Tangan memegang ponsel yang menampilkan media sosial" loading="lazy" /></div>
        <div>
          <h2 id="privasi-h">Laporanmu hanya untukmu dan Satgas</h2>
          <ul className="data-rows">
            <li><b className="ya">Disimpan</b><span>Data diri, kronologi, teks bukti dan tangkapan layar, di database yang hanya bisa diakses lewat akun.</span></li>
            <li><b className="tidak">Siapa yang bisa membaca</b><span>Hanya kamu dan petugas Satgas PPKPT. Aturan ini dijaga langsung oleh database, bukan hanya oleh tampilan web.</span></li>
            <li><b>Model AI</b><span>Server model hanya menerima teks bukti untuk dinilai, tanpa nama atau NIM.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {/* FAQ: pertanyaan sebelum melapor, dengan nomor darurat */}
  <section className="doc-sec ground-surface" id="faq" aria-labelledby="faq-h">
    <div className="wrap">
      <div className="faq-wrap">
        <div>
          <h2 id="faq-h">Pertanyaan sebelum melapor</h2>
          <div className="darurat">
            <b>Dalam bahaya sekarang?</b>
            <p>Hubungi 112 atau minta tolong orang terdekat. Jangan tunggu sampai laporan selesai.</p>
            <p>Kalau pikiranmu terasa sangat berat, hubungi layanan kesehatan jiwa 119 ekstensi 8 atau orang yang kamu percaya.</p>
          </div>
          <p className="btn-row"><a className="btn btn--primary" href="/lapor">Buat laporan<svg className="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path className="m-arrow__stem" d="M1 6h8"/><path className="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div className="faq">
          <div className="faq__item"><h3>Apakah laporanku langsung terkirim ke kampus?</h3><p>Ya. Setelah kamu menekan Kirim laporan, laporan masuk ke dasbor {KAMPUS.tujuan} dan statusnya bisa kamu pantau di Laporan saya.</p></div>
          <div className="faq__item"><h3>Bagaimana kalau AI bilang tidak terindikasi?</h3><p>Kamu tetap boleh melapor. AI hanya menilai teks, sedangkan perundungan juga bisa berupa pengucilan, ancaman langsung atau kekerasan fisik.</p></div>
          <div className="faq__item"><h3>Bolehkah melapor untuk teman?</h3><p>Boleh. Pilih "Saksi" di formulir, lalu ceritakan apa yang kamu lihat. Data dirimu sebagai pelapor tetap tercatat.</p></div>
          <div className="faq__item"><h3>Bukti apa yang sebaiknya kusimpan?</h3><p>Tangkapan layar utuh yang memperlihatkan nama akun, isi pesan dan tanggal. Jangan hapus percakapan aslinya.</p></div>
          <div className="faq__item"><h3>Apakah identitasku akan dirahasiakan?</h3><p>Identitasmu hanya terlihat oleh petugas Satgas. Tulis di kronologi kalau kamu tidak ingin identitasmu diungkap kepada terduga pelaku.</p></div>
        </div>
      </div>
    </div>
  </section>

</main>
  );
}
