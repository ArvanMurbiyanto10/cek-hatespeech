<?php
/*
 * Halaman Tentang: latar belakang, ujaran kebencian, model, keterbatasan, pengembang.
 * Sub-halaman teks panjang dengan sistem yang sama.
 */

$css = <<<'CSS'
.doc-sec { padding: var(--padt) 0 var(--padb); }
.doc-sec.ground-surface { background: var(--g-alt); }
.doc-intro { padding-top: calc(var(--nav) + var(--s-8)); }
.doc-intro h1 { font-size: clamp(38px, 4.4vw, 64px); max-width: 16ch; }
.doc-intro .t-lead { margin-top: var(--s-5); max-width: 52ch; }
.doc-foto { aspect-ratio: 4 / 3; }
.doc-head { margin-bottom: var(--s-7); max-width: 62ch; }
.doc-head p { margin: var(--s-3) 0 0; color: var(--ink); font-size: 18px; line-height: 1.6; }
.doc-prosa p { margin: 0 0 var(--s-4); font-size: 18px; line-height: 1.7; color: var(--ink); max-width: 62ch; }
.doc-no { display: block; font: var(--display-weight) 32px/1 var(--font-display); letter-spacing: -0.03em; color: var(--ink); margin-bottom: var(--s-4); }
.doc-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.doc-tags span { padding: 8px 14px; border: 1px solid var(--line); border-radius: 99px; font: 400 15px/1.3 var(--font-text); color: var(--ink); background: var(--bg); }
.doc-contoh { margin-top: var(--s-4); padding: 12px 14px; border-radius: var(--r-md); background: var(--g-alt); font-size: 16px; color: var(--ink); }
.doc-contoh span { display: block; font: 500 13px/1.4 var(--font-text); color: var(--muted); margin-bottom: 2px; }
.card.k-hate { box-shadow: inset 3px 0 0 #a3271b; } .card.k-kasar { box-shadow: inset 3px 0 0 #2e4462; } .card.k-aman { box-shadow: inset 3px 0 0 #17603b; }
.doc-spek { width: 100%; border-collapse: collapse; font-size: 16px; }
.doc-spek th, .doc-spek td { padding: 14px 0; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
.doc-spek th { width: 38%; padding-right: var(--s-5); font-weight: 400; color: var(--muted); }
.doc-spek tr:last-child th, .doc-spek tr:last-child td { border-bottom: 0; }
.doc-metrik { font: var(--display-weight) var(--t-stat)/1 var(--font-display); letter-spacing: -0.03em; color: var(--ink); }
.doc-catatan { margin-top: var(--s-5); color: var(--muted); max-width: 70ch; }
.doc-profil { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: var(--s-8); align-items: start; }
.doc-avatar { aspect-ratio: 3 / 4; border-radius: var(--r-lg); display: grid; place-items: center; background: var(--ink); color: var(--bg); font: var(--display-weight) 76px/1 var(--font-display); letter-spacing: -0.03em; overflow: hidden; }
.doc-avatar img { width: 100%; height: 100%; object-fit: cover; }
.doc-judul-ta { margin: 0 0 var(--s-6); font: var(--display-weight) 26px/1.3 var(--font-display); letter-spacing: -0.02em; color: var(--ink); max-width: 40ch; }
.doc-kampus { display: flex; align-items: center; gap: var(--s-5); margin-top: var(--s-6); padding-top: var(--s-5); border-top: 1px solid var(--line); }
.doc-kampus img { width: 72px; height: 88px; object-fit: contain; }
.doc-kampus strong { display: block; font-weight: 500; }
@media (max-width: 639px) { .doc-profil { grid-template-columns: minmax(0, 1fr); gap: var(--s-6); } .doc-avatar { max-width: 220px; } .doc-foto { margin-top: var(--s-6); } }
CSS;

kepala([
    'judul'     => 'Tentang Cek Hatespeech: aplikasi tugas akhir deteksi ujaran kebencian',
    'deskripsi' => 'Latar belakang, cara kerja model XLM-RoBERTa, keterbatasan dan pengembang aplikasi web Cek Hatespeech.',
    'css'       => $css,
    'kanonik'   => '/tentang',
]);
navigasi('tentang');
?>
<main id="top">

  <section class="doc-sec doc-intro" aria-labelledby="tt-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-6">
          <h1 id="tt-h">Tentang aplikasi Cek Hatespeech</h1>
          <p class="t-lead">Cek Hatespeech membantu mahasiswa Telkom University Purwokerto yang mengalami perundungan atau ujaran kebencian menyusun laporan: bukti diperiksa model AI, lalu semuanya dirangkum dalam PDF untuk diserahkan ke Satgas PPKPT kampus. Aplikasi ini dibuat sebagai tugas akhir.</p>
          <p class="btn-row" style="margin-top: var(--s-6)"><a class="btn btn--primary" href="/lapor">Buat laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div class="frame frame--photo doc-foto span-6 start-7">
          <img class="frame__img" src="<?= e(urlFoto('tentang')) ?>" alt="Empat orang berdiri sambil membaca layar ponsel masing-masing" fetchpriority="high" data-source="library" data-subject="orang memakai media sosial">
        </div>
      </div>
    </div>
  </section>

  <section class="doc-sec ground-surface" id="latar-belakang" aria-labelledby="lb-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-5"><h2 id="lb-h">Latar belakang dan tujuan</h2></div>
        <div class="span-6 start-7 doc-prosa">
          <p>Perundungan di kalangan mahasiswa banyak terjadi lewat grup kelas, kolom komentar dan pesan pribadi. Banyak korban tidak yakin apakah yang dialaminya termasuk perundungan, tidak tahu harus melapor ke mana, dan tidak tahu bukti apa yang perlu disiapkan.</p>
          <p>Cek Hatespeech menjawab tiga kebingungan itu: menjelaskan tanda perundungan, menilai teks bukti dengan model XLM-RoBERTa yang dilatih untuk ujaran kebencian dan bahasa kasar berbahasa Indonesia, lalu menyusun laporan PDF yang rapi untuk Satgas PPKPT Telkom University Purwokerto.</p>
        </div>
      </div>
      <div class="grid grid--top" style="margin-top: var(--s-8)">
        <article class="card m-nolift span-3"><span class="doc-no">1</span><p>Membantu mahasiswa mengenali tanda perundungan dan ujaran kebencian.</p></article>
        <article class="card m-nolift span-3"><span class="doc-no">2</span><p>Menerapkan model XLM-RoBERTa untuk menilai teks bukti secara otomatis.</p></article>
        <article class="card m-nolift span-3"><span class="doc-no">3</span><p>Menyusun laporan PDF berisi data pelapor, kronologi, bukti dan hasil AI.</p></article>
        <article class="card m-nolift span-3"><span class="doc-no">4</span><p>Menjaga privasi pelapor: data diri tidak dikirim ke server.</p></article>
      </div>
    </div>
  </section>

  <section class="doc-sec" id="pengguna" aria-labelledby="pg-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-6">
          <div class="doc-head"><h2 id="pg-h">Siapa yang memakai Cek Hatespeech</h2></div>
          <div class="grid grid--top">
            <article class="card m-nolift span-12"><h3>Mahasiswa yang menjadi korban</h3><p>Menyusun bukti dan laporan tanpa harus tahu format resmi lebih dulu.</p></article>
            <article class="card m-nolift span-12"><h3>Teman yang menjadi saksi</h3><p>Melaporkan perundungan yang dilihatnya di grup kelas atau di kampus.</p></article>
            <article class="card m-nolift span-12"><h3>Satgas PPKPT dan dosen wali</h3><p>Menerima laporan yang sudah tersusun: identitas, kronologi, bukti dan hasil pemeriksaan AI.</p></article>
          </div>
        </div>
        <div class="frame frame--photo span-6 start-7" style="aspect-ratio: 4 / 5">
          <img class="frame__img" src="<?= e(urlFoto('hero')) ?>" alt="Tangan memegang ponsel yang membuka media sosial" loading="lazy" data-source="library" data-subject="ponsel dengan media sosial">
        </div>
      </div>
    </div>
  </section>

  <section class="doc-sec ground-surface" id="ujaran-kebencian" aria-labelledby="uk-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-6">
          <div class="doc-head">
            <h2 id="uk-h">Mengenal ujaran kebencian</h2>
            <p>Ujaran kebencian adalah provokasi, hasutan atau hinaan kepada orang atau kelompok karena identitasnya. Dampaknya bisa sampai ke diskriminasi dan kekerasan di dunia nyata.</p>
          </div>
          <h3>Aspek yang biasa disasar</h3>
          <p class="muted" style="margin: var(--s-2) 0 var(--s-4)">Menurut Surat Edaran Kapolri No. SE/6/X/2015.</p>
          <div class="doc-tags">
            <span>Suku</span><span>Agama</span><span>Aliran keagamaan</span><span>Keyakinan</span><span>Ras</span><span>Antargolongan</span>
            <span>Warna kulit</span><span>Etnis</span><span>Gender</span><span>Kaum difabel</span><span>Orientasi seksual</span>
          </div>
        </div>
        <div class="frame frame--photo span-6 start-7" style="aspect-ratio: 4 / 3">
          <img class="frame__img" src="<?= e(urlFoto('hate-speech')) ?>" alt="Seorang perempuan menunduk sambil memegang kepala dan ponselnya" loading="lazy" data-source="library" data-subject="perundungan daring">
        </div>
      </div>
      <div class="grid grid--top" style="margin-top: var(--s-8)">
        <article class="card m-nolift k-hate span-4">
          <h3>Ujaran kebencian</h3>
          <p>Menyerang, merendahkan atau menghasut kebencian terhadap orang atau kelompok karena identitasnya.</p>
          <p class="doc-contoh"><span>Contoh, skor model 99,33%</span>"Semua pendatang dari suku itu pencuri, jangan kasih tempat"</p>
        </article>
        <article class="card m-nolift k-kasar span-4">
          <h3>Bahasa kasar</h3>
          <p>Memakai makian atau kata kotor, tetapi tidak menyerang identitas kelompok tertentu.</p>
          <p class="doc-contoh"><span>Contoh, skor kasar 98,52%</span>"Anjing, macet parah lagi pagi ini"</p>
        </article>
        <article class="card m-nolift k-aman span-4">
          <h3>Kritik dan pendapat</h3>
          <p>Ketidaksetujuan terhadap gagasan atau kebijakan tanpa merendahkan identitas. Model masih bisa salah menandainya.</p>
          <p class="doc-contoh"><span>Contoh, skor model 83,02%</span>"Menurut saya kebijakan ini kurang tepat karena memberatkan rakyat kecil"</p>
        </article>
      </div>
    </div>
  </section>

  <section class="doc-sec" id="model" aria-labelledby="md-h">
    <div class="wrap">
      <div class="doc-head">
        <h2 id="md-h">Model XLM-RoBERTa yang dipakai</h2>
        <p>XLM-RoBERTa dilatih pada teks dari sekitar 100 bahasa, sehingga memahami konteks kalimat, bukan sekadar mencocokkan kata.</p>
      </div>
      <div class="grid grid--top">
        <div class="card m-nolift span-6">
          <table class="doc-spek">
            <tr><th scope="row">Nama model</th><td><a class="link" href="https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1" rel="noopener">nahiar/hatespeech-abusive-xlm-roberta-v1</a></td></tr>
            <tr><th scope="row">Model dasar</th><td>XLM-RoBERTa base (FacebookAI)</td></tr>
            <tr><th scope="row">Pelatihan</th><td>Fine-tuning, learning rate 2e-5, batch size 16</td></tr>
            <tr><th scope="row">Klasifikasi</th><td>Multilabel: HATESPEECH dan ABUSIVE</td></tr>
            <tr><th scope="row">Bahasa</th><td>Indonesia, Melayu, Jawa, Sunda, Madura, Minangkabau, Banjar, Bugis, Aceh dan Inggris</td></tr>
            <tr><th scope="row">Lisensi</th><td>Apache-2.0</td></tr>
          </table>
        </div>
        <div class="span-6 start-7">
          <div class="grid grid--top">
            <div class="card m-nolift span-6"><p class="doc-metrik">92,49%</p><p class="muted">Precision</p></div>
            <div class="card m-nolift span-6"><p class="doc-metrik">93,00%</p><p class="muted">Recall</p></div>
            <div class="card m-nolift span-6"><p class="doc-metrik">92,74%</p><p class="muted">F1 macro</p></div>
            <div class="card m-nolift span-6"><p class="doc-metrik">92,69%</p><p class="muted">F1 weighted</p></div>
          </div>
          <p class="doc-catatan">Metrik dilaporkan pengembang model pada data validasi di kartu model Hugging Face, bukan hasil pengujian aplikasi ini.</p>
        </div>
      </div>
      <h3 style="margin-top: var(--s-8)">Teknologi yang dipakai</h3>
      <div class="doc-tags" style="margin-top: var(--s-4)">
        <span>PHP 8</span><span>Vercel</span><span>Python</span><span>FastAPI</span><span>Hugging Face Transformers</span><span>XLM-RoBERTa</span><span>Google Colab</span><span>Cloudflare Tunnel</span>
      </div>
    </div>
  </section>

  <section class="doc-sec ground-surface" id="keterbatasan" aria-labelledby="kb-h">
    <div class="wrap">
      <div class="doc-head">
        <h2 id="kb-h">Keterbatasan Cek Hatespeech</h2>
        <p>Putusan dibuat mesin. Baca sebagai bahan pertimbangan, bukan keputusan akhir.</p>
      </div>
      <div class="grid grid--top">
        <article class="card m-nolift span-4"><h3>Konteks dan sarkasme</h3><p>Sindiran, ironi atau kalimat yang justru mengecam kebencian kadang dinilai keliru.</p></article>
        <article class="card m-nolift span-4"><h3>Bukan alat bukti hukum</h3><p>Putusan tidak dapat menjadi dasar penegakan hukum tanpa peninjauan oleh ahli.</p></article>
        <article class="card m-nolift span-4"><h3>Bias data latih</h3><p>Istilah baru atau sangat lokal mungkin belum dikenali model.</p></article>
        <article class="card m-nolift span-4"><h3>Sasaran dan kategori</h3><p>Model hanya menilai dua label; sasaran dan kategori diperkirakan kamus kata.</p></article>
        <article class="card m-nolift span-4"><h3>Server model terpisah</h3><p>Jika server model sedang mati, pengecekan untuk sementara tidak bisa dilakukan.</p></article>
        <article class="card m-nolift span-4"><h3>Bukan kanal resmi</h3><p>Web ini tidak mengirim laporan ke kampus. Pelapor sendiri yang menyerahkan PDF ke Satgas PPKPT.</p></article>
      </div>
    </div>
  </section>

  <section class="doc-sec" id="pengembang" aria-labelledby="dev-h">
    <div class="wrap">
      <div class="doc-head"><h2 id="dev-h">Pengembang tugas akhir</h2></div>
      <div class="doc-profil">
        <div class="doc-avatar" <?= adaFoto('pengembang') ? '' : 'role="img" aria-label="' . e(PENGEMBANG['nama']) . '"' ?>>
          <?php if (adaFoto('pengembang')): ?>
            <img src="<?= e(urlFoto('pengembang')) ?>" alt="Foto <?= e(PENGEMBANG['nama']) ?>" loading="lazy" data-source="client" data-subject="pengembang">
          <?php else: ?>
            <?= e(inisial(PENGEMBANG['nama'])) ?>
          <?php endif; ?>
        </div>
        <div>
          <p class="doc-judul-ta"><?= e(PENGEMBANG['judul']) ?></p>
          <table class="doc-spek">
            <tr><th scope="row">Nama</th><td><?= e(PENGEMBANG['nama']) ?></td></tr>
            <tr><th scope="row">NIM</th><td><?= e(PENGEMBANG['nim']) ?></td></tr>
            <tr><th scope="row">Program studi</th><td><?= e(PENGEMBANG['prodi']) ?></td></tr>
            <?php if (PENGEMBANG['pembimbing'] !== ''): ?>
              <tr><th scope="row">Dosen pembimbing</th><td><?= e(PENGEMBANG['pembimbing']) ?></td></tr>
            <?php endif; ?>
            <tr><th scope="row">Tahun</th><td><?= e(PENGEMBANG['tahun']) ?></td></tr>
          </table>
          <div class="doc-kampus">
            <img src="<?= e(urlFoto('logo-kampus')) ?>" alt="Logo Telkom University" loading="lazy">
            <div><strong><?= e(PENGEMBANG['kampus']) ?></strong><span class="muted"><?= e(PENGEMBANG['prodi']) ?></span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>
<?php
penutup();
