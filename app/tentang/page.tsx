import { PENGEMBANG } from '@/lib/kampus';
import './tentang.css';

export const metadata = { title: 'Tentang', description: 'Latar belakang, cara kerja model XLM-RoBERTa, keterbatasan dan pengembang Cek Hatespeech.' };

/** Inisial nama, mis. "Arvan Murbiyanto" -> "AM". */
const inisial = (nama: string) =>
  nama.trim().split(/\s+/).slice(0, 2).map((k) => k.charAt(0).toUpperCase()).join('') || '?';

export default function Halaman() {
  return (
<main id="top">

  <section className="doc-sec doc-intro" aria-labelledby="tt-h">
    <div className="wrap">
      <div className="grid grid--top">
        <div className="span-6">
          <h1 id="tt-h">Tentang aplikasi Cek Hatespeech</h1>
          <p className="t-lead">Cek Hatespeech membantu mahasiswa Telkom University Purwokerto yang mengalami perundungan atau ujaran kebencian melapor ke Satgas PPKPT: bukti diperiksa model AI, pasal UU ITE yang mungkin terkait dipilih otomatis, dan laporan beserta statusnya tersimpan di database. Aplikasi ini dibuat sebagai tugas akhir.</p>
          <p className="btn-row" style={{'marginTop': 'var(--s-6)'} as React.CSSProperties}><a className="btn btn--primary" href="/lapor">Buat laporan<svg className="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path className="m-arrow__stem" d="M1 6h8"/><path className="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div className="frame frame--photo doc-foto span-6 start-7">
          <img className="frame__img" src="/assets/img/tentang.jpg" alt="Empat orang berdiri sambil membaca layar ponsel masing-masing" fetchPriority="high" data-source="library" data-subject="orang memakai media sosial" />
        </div>
      </div>
    </div>
  </section>

  <section className="doc-sec ground-surface" id="latar-belakang" aria-labelledby="lb-h">
    <div className="wrap">
      <div className="grid grid--top">
        <div className="span-5"><h2 id="lb-h">Latar belakang dan tujuan</h2></div>
        <div className="span-6 start-7 doc-prosa">
          <p>Perundungan di kalangan mahasiswa banyak terjadi lewat grup kelas, kolom komentar dan pesan pribadi. Banyak korban tidak yakin apakah yang dialaminya termasuk perundungan, tidak tahu harus melapor ke mana, dan tidak tahu bukti apa yang perlu disiapkan.</p>
          <p>Cek Hatespeech menjawab tiga kebingungan itu: menjelaskan tanda perundungan, menilai teks bukti dengan model XLM-RoBERTa yang dilatih untuk ujaran kebencian dan bahasa kasar berbahasa Indonesia, lalu mengirim laporan langsung ke dasbor Satgas PPKPT Telkom University Purwokerto dan menampilkan status penanganannya.</p>
        </div>
      </div>
      <div className="grid grid--top" style={{'marginTop': 'var(--s-8)'} as React.CSSProperties}>
        <article className="card m-nolift span-3"><span className="doc-no">1</span><p>Membantu mahasiswa mengenali tanda perundungan dan ujaran kebencian.</p></article>
        <article className="card m-nolift span-3"><span className="doc-no">2</span><p>Menerapkan model XLM-RoBERTa untuk menilai teks bukti secara otomatis.</p></article>
        <article className="card m-nolift span-3"><span className="doc-no">3</span><p>Menyimpan laporan dan riwayat penanganannya untuk dua role: mahasiswa dan Satgas PPKPT.</p></article>
        <article className="card m-nolift span-3"><span className="doc-no">4</span><p>Menjaga privasi pelapor: laporan hanya bisa dibaca pelapor dan Satgas.</p></article>
      </div>
    </div>
  </section>

  <section className="doc-sec" id="pengguna" aria-labelledby="pg-h">
    <div className="wrap">
      <div className="grid grid--top">
        <div className="span-6">
          <div className="doc-head"><h2 id="pg-h">Siapa yang memakai Cek Hatespeech</h2></div>
          <div className="grid grid--top">
            <article className="card m-nolift span-12"><h3>Mahasiswa yang menjadi korban</h3><p>Menyusun bukti dan laporan tanpa harus tahu format resmi lebih dulu.</p></article>
            <article className="card m-nolift span-12"><h3>Teman yang menjadi saksi</h3><p>Melaporkan perundungan yang dilihatnya di grup kelas atau di kampus.</p></article>
            <article className="card m-nolift span-12"><h3>Satgas PPKPT dan dosen wali</h3><p>Menerima laporan yang sudah tersusun: identitas, kronologi, bukti dan hasil pemeriksaan AI.</p></article>
          </div>
        </div>
        <div className="frame frame--photo span-6 start-7" style={{'aspectRatio': '4 / 5'} as React.CSSProperties}>
          <img className="frame__img" src="/assets/img/hero.jpg" alt="Tangan memegang ponsel yang membuka media sosial" loading="lazy" data-source="library" data-subject="ponsel dengan media sosial" />
        </div>
      </div>
    </div>
  </section>

  <section className="doc-sec ground-surface" id="ujaran-kebencian" aria-labelledby="uk-h">
    <div className="wrap">
      <div className="grid grid--top">
        <div className="span-6">
          <div className="doc-head">
            <h2 id="uk-h">Mengenal ujaran kebencian</h2>
            <p>Ujaran kebencian adalah provokasi, hasutan atau hinaan kepada orang atau kelompok karena identitasnya. Dampaknya bisa sampai ke diskriminasi dan kekerasan di dunia nyata.</p>
          </div>
          <h3>Aspek yang biasa disasar</h3>
          <p className="muted" style={{'margin': 'var(--s-2) 0 var(--s-4)'} as React.CSSProperties}>Menurut Surat Edaran Kapolri No. SE/6/X/2015.</p>
          <div className="doc-tags">
            <span>Suku</span><span>Agama</span><span>Aliran keagamaan</span><span>Keyakinan</span><span>Ras</span><span>Antargolongan</span>
            <span>Warna kulit</span><span>Etnis</span><span>Gender</span><span>Kaum difabel</span><span>Orientasi seksual</span>
          </div>
        </div>
        <div className="frame frame--photo span-6 start-7" style={{'aspectRatio': '4 / 3'} as React.CSSProperties}>
          <img className="frame__img" src="/assets/img/hate-speech.jpg" alt="Seorang perempuan menunduk sambil memegang kepala dan ponselnya" loading="lazy" data-source="library" data-subject="perundungan daring" />
        </div>
      </div>
      <div className="grid grid--top" style={{'marginTop': 'var(--s-8)'} as React.CSSProperties}>
        <article className="card m-nolift k-hate span-4">
          <h3>Ujaran kebencian</h3>
          <p>Menyerang, merendahkan atau menghasut kebencian terhadap orang atau kelompok karena identitasnya.</p>
          <p className="doc-contoh"><span>Contoh, skor model 99,33%</span>"Semua pendatang dari suku itu pencuri, jangan kasih tempat"</p>
        </article>
        <article className="card m-nolift k-kasar span-4">
          <h3>Bahasa kasar</h3>
          <p>Memakai makian atau kata kotor, tetapi tidak menyerang identitas kelompok tertentu.</p>
          <p className="doc-contoh"><span>Contoh, skor kasar 98,52%</span>"Anjing, macet parah lagi pagi ini"</p>
        </article>
        <article className="card m-nolift k-aman span-4">
          <h3>Kritik dan pendapat</h3>
          <p>Ketidaksetujuan terhadap gagasan atau kebijakan tanpa merendahkan identitas. Model masih bisa salah menandainya.</p>
          <p className="doc-contoh"><span>Contoh, skor model 83,02%</span>"Menurut saya kebijakan ini kurang tepat karena memberatkan rakyat kecil"</p>
        </article>
      </div>
    </div>
  </section>

  <section className="doc-sec" id="model" aria-labelledby="md-h">
    <div className="wrap">
      <div className="doc-head">
        <h2 id="md-h">Model XLM-RoBERTa yang dipakai</h2>
        <p>XLM-RoBERTa dilatih pada teks dari sekitar 100 bahasa, sehingga memahami konteks kalimat, bukan sekadar mencocokkan kata.</p>
      </div>
      <div className="grid grid--top">
        <div className="card m-nolift span-6">
          <table className="doc-spek">
            <tr><th scope="row">Nama model</th><td><a className="link" href="https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1" rel="noopener">nahiar/hatespeech-abusive-xlm-roberta-v1</a></td></tr>
            <tr><th scope="row">Model dasar</th><td>XLM-RoBERTa base (FacebookAI)</td></tr>
            <tr><th scope="row">Pelatihan</th><td>Fine-tuning, learning rate 2e-5, batch size 16</td></tr>
            <tr><th scope="row">Klasifikasi</th><td>Multilabel: HATESPEECH dan ABUSIVE</td></tr>
            <tr><th scope="row">Bahasa</th><td>Indonesia, Melayu, Jawa, Sunda, Madura, Minangkabau, Banjar, Bugis, Aceh dan Inggris</td></tr>
            <tr><th scope="row">Lisensi</th><td>Apache-2.0</td></tr>
          </table>
        </div>
        <div className="span-6 start-7">
          <div className="grid grid--top">
            <div className="card m-nolift span-6"><p className="doc-metrik">92,49%</p><p className="muted">Precision</p></div>
            <div className="card m-nolift span-6"><p className="doc-metrik">93,00%</p><p className="muted">Recall</p></div>
            <div className="card m-nolift span-6"><p className="doc-metrik">92,74%</p><p className="muted">F1 macro</p></div>
            <div className="card m-nolift span-6"><p className="doc-metrik">92,69%</p><p className="muted">F1 weighted</p></div>
          </div>
          <p className="doc-catatan">Metrik dilaporkan pengembang model pada data validasi di kartu model Hugging Face, bukan hasil pengujian aplikasi ini.</p>
        </div>
      </div>
      <h3 style={{'marginTop': 'var(--s-8)'} as React.CSSProperties}>Teknologi yang dipakai</h3>
      <div className="doc-tags" style={{'marginTop': 'var(--s-4)'} as React.CSSProperties}>
        <span>PHP 8</span><span>Vercel</span><span>Python</span><span>FastAPI</span><span>Hugging Face Transformers</span><span>XLM-RoBERTa</span><span>Google Colab</span><span>Cloudflare Tunnel</span>
      </div>
    </div>
  </section>

  <section className="doc-sec ground-surface" id="keterbatasan" aria-labelledby="kb-h">
    <div className="wrap">
      <div className="doc-head">
        <h2 id="kb-h">Keterbatasan Cek Hatespeech</h2>
        <p>Putusan dibuat mesin. Baca sebagai bahan pertimbangan, bukan keputusan akhir.</p>
      </div>
      <div className="grid grid--top">
        <article className="card m-nolift span-4"><h3>Konteks dan sarkasme</h3><p>Sindiran, ironi atau kalimat yang justru mengecam kebencian kadang dinilai keliru.</p></article>
        <article className="card m-nolift span-4"><h3>Bukan alat bukti hukum</h3><p>Putusan tidak dapat menjadi dasar penegakan hukum tanpa peninjauan oleh ahli.</p></article>
        <article className="card m-nolift span-4"><h3>Bias data latih</h3><p>Istilah baru atau sangat lokal mungkin belum dikenali model.</p></article>
        <article className="card m-nolift span-4"><h3>Sasaran dan kategori</h3><p>Model hanya menilai dua label; sasaran dan kategori diperkirakan kamus kata.</p></article>
        <article className="card m-nolift span-4"><h3>Server model terpisah</h3><p>Jika server model sedang mati, pengecekan untuk sementara tidak bisa dilakukan.</p></article>
        <article className="card m-nolift span-4"><h3>Bukan kanal resmi</h3><p>Aplikasi ini prototipe tugas akhir. Sebelum dipakai resmi, alurnya perlu disepakati dengan Satgas PPKPT kampus.</p></article>
      </div>
    </div>
  </section>

  <section className="doc-sec" id="pengembang" aria-labelledby="dev-h">
    <div className="wrap">
      <div className="doc-head"><h2 id="dev-h">Pengembang tugas akhir</h2></div>
      <div className="doc-profil">
        <div className="doc-avatar" role="img" aria-label={PENGEMBANG.nama}>
          
            {inisial(PENGEMBANG.nama)}
          
        </div>
        <div>
          <p className="doc-judul-ta">{PENGEMBANG.judul}</p>
          <table className="doc-spek">
            <tr><th scope="row">Nama</th><td>{PENGEMBANG.nama}</td></tr>
            <tr><th scope="row">NIM</th><td>{PENGEMBANG.nim}</td></tr>
            <tr><th scope="row">Program studi</th><td>{PENGEMBANG.prodi}</td></tr>
            {PENGEMBANG.pembimbing ? (<>
              <tr><th scope="row">Dosen pembimbing</th><td>{PENGEMBANG.pembimbing}</td></tr>
            </>) : null}
            <tr><th scope="row">Tahun</th><td>{PENGEMBANG.tahun}</td></tr>
          </table>
          <div className="doc-kampus">
            <img src="/assets/img/logo-kampus.png" alt="Logo Telkom University" loading="lazy" />
            <div><strong>{PENGEMBANG.kampus}</strong><span className="muted">{PENGEMBANG.prodi}</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>
  );
}
