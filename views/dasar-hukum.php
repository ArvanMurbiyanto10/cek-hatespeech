<?php
/*
 * Halaman Dasar Hukum: penjelasan pasal yang mengatur ujaran kebencian.
 * Sub-halaman teks panjang (copy.md: "Long copy goes to sub-pages"), memakai sistem yang sama.
 */

$css = <<<'CSS'
.doc-sec { padding: var(--padt) 0 var(--padb); }
.doc-sec.ground-surface { background: var(--g-alt); }
.doc-intro { padding-top: calc(var(--nav) + var(--s-8)); }
.doc-intro h1 { font-size: clamp(38px, 4.4vw, 64px); max-width: 16ch; }
.doc-intro .t-lead { margin-top: var(--s-5); max-width: 52ch; }
.doc-intro .frame { aspect-ratio: 4 / 3; }
.doc-toc { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: var(--s-5); margin-top: var(--s-8); padding-top: var(--s-6); border-top: 1px solid var(--line); }
.doc-toc a { display: grid; gap: 4px; min-height: 44px; text-decoration: none; color: var(--ink); font: 500 16px/1.4 var(--font-text); }
.doc-toc a span { font-weight: 400; color: var(--muted); font-size: 14px; }
.doc-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: var(--s-3); margin-bottom: var(--s-7); }
.doc-head p { margin: 0; color: var(--muted); }
.pasal { display: flex; flex-direction: column; gap: var(--s-4); }
.pasal h3 { font: var(--display-weight) 26px/1.2 var(--font-display); letter-spacing: -0.02em; }
.pasal__topik { margin: -8px 0 0; font: 500 16px/1.4 var(--font-text); color: var(--ink); }
.pasal p, .pasal li { font-size: 16px; line-height: 1.65; color: var(--ink); }
.pasal h4 { margin: var(--s-2) 0 0; font: 500 14px/1.3 var(--font-text); color: var(--muted); }
.pasal ol { margin: 0; padding-left: 22px; display: grid; gap: 4px; }
.pasal__catatan { padding-left: var(--s-4); border-left: 2px solid var(--line); }
.pasal__ancaman { margin-top: auto; padding: var(--s-4) var(--s-5); border-radius: var(--r-md); background: #fbe9e6; color: #8f2016; font: 500 16px/1.5 var(--font-text); }
.pasal__ancaman span { display: block; font: 500 13px/1.4 var(--font-text); color: #8f2016; opacity: .9; }
.doc-tabel { width: 100%; border-collapse: collapse; font-size: 16px; }
.doc-tabel-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: var(--r-lg); background: var(--bg); }
.doc-tabel th, .doc-tabel td { padding: 16px 20px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
.doc-tabel thead th { font: 500 13px/1.4 var(--font-text); color: var(--muted); background: var(--g-alt); }
.doc-tabel tr:last-child td { border-bottom: 0; }
.doc-tabel td:first-child { font-weight: 500; white-space: nowrap; }
.doc-catatan { margin-top: var(--s-6); color: var(--muted); max-width: 70ch; }
@media (max-width: 1023px) { .doc-toc { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 639px) { .doc-toc { grid-template-columns: minmax(0, 1fr); } .doc-tabel { min-width: 620px; } .doc-intro .frame { margin-top: var(--s-6); } }
CSS;

kepala([
    'judul'     => 'Dasar hukum ujaran kebencian: UU ITE, KUHP dan UU 40/2008 | Cek Hatespeech',
    'deskripsi' => 'Inti, unsur dan ancaman pidana setiap pasal yang mengatur ujaran kebencian di Indonesia, dari UU ITE 2024 sampai KUHP baru.',
    'css'       => $css,
    'kanonik'   => '/dasar-hukum',
]);
navigasi('dasar-hukum');
?>
<main id="top">

  <section class="doc-sec doc-intro" aria-labelledby="dh-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-6">
          <h1 id="dh-h">Dasar hukum ujaran kebencian di Indonesia</h1>
          <p class="t-lead">Ujaran kebencian di ruang digital diatur terutama oleh UU ITE yang terakhir diubah dengan UU No. 1 Tahun 2024. KUHP baru, yang berlaku sejak 2 Januari 2026, dan UU No. 40 Tahun 2008 mengatur perbuatan serupa.</p>
        </div>
        <div class="frame frame--photo span-6 start-7">
          <img class="frame__img" src="<?= e(urlFoto('hukum')) ?>" alt="Palu hakim di atas meja sidang dengan tumpukan buku" fetchpriority="high" data-source="library" data-subject="palu hakim di ruang sidang">
        </div>
      </div>
      <nav class="doc-toc" aria-label="Isi halaman">
        <a href="#uu-ite">UU ITE<span>Pasal 28 (2), 27A, 29, 28 (3)</span></a>
        <a href="#kuhp">KUHP baru<span>Pasal 242, 243, 300</span></a>
        <a href="#uu-40-2008">Peraturan lainnya<span>UU 40/2008, SE Kapolri, aturan kampus</span></a>
        <a href="#ringkasan">Ringkasan pidana<span>Semua ancaman dalam satu tabel</span></a>
      </nav>
    </div>
  </section>

  <section class="doc-sec ground-surface" id="uu-ite" aria-labelledby="ite-h">
    <div class="wrap">
      <div class="doc-head"><h2 id="ite-h">UU ITE</h2><p>UU No. 11 Tahun 2008 jo. UU No. 19 Tahun 2016 jo. UU No. 1 Tahun 2024</p></div>
      <div class="grid grid--top">
        <article class="card pasal m-nolift span-6" id="pasal-28-2">
          <h3>Pasal 28 ayat (2) jo. Pasal 45A ayat (2)</h3>
          <p class="pasal__topik">Menyebarkan kebencian berdasarkan identitas</p>
          <p>Melarang setiap orang yang dengan sengaja dan tanpa hak menyebarkan informasi elektronik yang menghasut, mengajak atau memengaruhi orang lain sehingga menimbulkan rasa kebencian atau permusuhan terhadap individu atau kelompok berdasarkan identitasnya.</p>
          <h4>Unsur-unsur</h4>
          <ol>
            <li>Setiap orang</li>
            <li>Dengan sengaja dan tanpa hak</li>
            <li>Mendistribusikan atau mentransmisikan informasi atau dokumen elektronik</li>
            <li>Bersifat menghasut, mengajak atau memengaruhi orang lain</li>
            <li>Menimbulkan rasa kebencian atau permusuhan</li>
            <li>Terhadap individu atau kelompok berdasarkan ras, kebangsaan, etnis, warna kulit, agama, kepercayaan, jenis kelamin, disabilitas mental atau disabilitas fisik</li>
          </ol>
          <p class="pasal__catatan">Putusan MK No. 105/PUU-XXII/2024 menegaskan pasal ini hanya berlaku untuk informasi yang secara substantif memuat kebencian berdasarkan identitas, dilakukan dengan sengaja dan di muka umum, serta menimbulkan risiko nyata diskriminasi, permusuhan atau kekerasan.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</p>
        </article>
        <article class="card pasal m-nolift span-6" id="pasal-27a">
          <h3>Pasal 27A jo. Pasal 45 ayat (4)</h3>
          <p class="pasal__topik">Menyerang kehormatan atau nama baik</p>
          <p>Melarang setiap orang dengan sengaja menyerang kehormatan atau nama baik orang lain dengan cara menuduhkan suatu hal, dengan maksud agar diketahui umum, melalui sistem elektronik. Pasal ini menggantikan Pasal 27 ayat (3) yang lama.</p>
          <h4>Unsur-unsur</h4>
          <ol>
            <li>Setiap orang dengan sengaja</li>
            <li>Menyerang kehormatan atau nama baik orang lain</li>
            <li>Dengan cara menuduhkan suatu hal</li>
            <li>Dengan maksud agar diketahui umum</li>
            <li>Dalam bentuk informasi atau dokumen elektronik melalui sistem elektronik</li>
          </ol>
          <p class="pasal__catatan">Delik aduan: hanya diproses jika korban sendiri yang melapor. Tidak dipidana jika dilakukan untuk kepentingan umum atau karena terpaksa membela diri. Menurut Putusan MK No. 105/PUU-XXII/2024, "orang lain" berarti perseorangan, bukan lembaga pemerintah, institusi, korporasi, profesi atau jabatan.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 2 tahun dan/atau denda paling banyak Rp400 juta</p>
        </article>
        <article class="card pasal m-nolift span-6" id="pasal-29">
          <h3>Pasal 29 jo. Pasal 45B</h3>
          <p class="pasal__topik">Ancaman kekerasan atau menakut-nakuti</p>
          <p>Melarang pengiriman informasi elektronik berisi ancaman kekerasan atau upaya menakut-nakuti yang ditujukan secara pribadi kepada korban.</p>
          <p class="pasal__catatan">Di Cek Hatespeech, kata ancaman seperti "bunuh" atau "bantai" disorot merah oleh kamus bantu.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 4 tahun dan/atau denda paling banyak Rp750 juta</p>
        </article>
        <article class="card pasal m-nolift span-6" id="pasal-28-3">
          <h3>Pasal 28 ayat (3) jo. Pasal 45A ayat (3)</h3>
          <p class="pasal__topik">Berita bohong yang menimbulkan kerusuhan</p>
          <p>Melarang penyebaran informasi elektronik yang diketahui memuat pemberitahuan bohong dan menimbulkan kerusuhan di masyarakat. Hoaks sering dipakai untuk menyulut kebencian terhadap kelompok tertentu.</p>
          <p class="pasal__catatan">Cek Hatespeech tidak memeriksa kebenaran berita; pasal ini dicantumkan sebagai konteks.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</p>
        </article>
      </div>
    </div>
  </section>

  <section class="doc-sec" id="kuhp" aria-labelledby="kuhp-h">
    <div class="wrap">
      <div class="doc-head"><h2 id="kuhp-h">KUHP baru</h2><p>UU No. 1 Tahun 2023, berlaku sejak 2 Januari 2026</p></div>
      <div class="grid grid--top">
        <article class="card pasal m-nolift span-4">
          <h3>Pasal 242</h3>
          <p class="pasal__topik">Penghinaan terhadap golongan penduduk</p>
          <p>Melarang pernyataan permusuhan atau penghinaan di muka umum terhadap satu atau beberapa golongan penduduk Indonesia berdasarkan ras, kebangsaan, etnis, warna kulit, jenis kelamin, disabilitas mental atau disabilitas fisik.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
        <article class="card pasal m-nolift span-4">
          <h3>Pasal 243</h3>
          <p class="pasal__topik">Menyebarluaskan konten permusuhan</p>
          <p>Melarang menyiarkan, mempertunjukkan atau menempelkan tulisan dan gambar, maupun memperdengarkan rekaman, termasuk melalui sarana teknologi informasi, yang berisi permusuhan terhadap golongan sebagaimana Pasal 242.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 4 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
        <article class="card pasal m-nolift span-4">
          <h3>Pasal 300</h3>
          <p class="pasal__topik">Permusuhan atas dasar agama atau kepercayaan</p>
          <p>Melarang perbuatan bersifat permusuhan, pernyataan kebencian, atau hasutan untuk permusuhan, kekerasan atau diskriminasi di muka umum terhadap agama, kepercayaan, atau kelompok atas dasar agama atau kepercayaan.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
      </div>
    </div>
  </section>

  <section class="doc-sec ground-surface" id="uu-40-2008" aria-labelledby="lain-h">
    <div class="wrap">
      <div class="doc-head"><h2 id="lain-h">Peraturan lainnya</h2><p>Undang-undang khusus, pedoman kepolisian dan aturan kampus</p></div>
      <div class="grid grid--top">
        <article class="card pasal m-nolift span-6">
          <h3>UU No. 40 Tahun 2008, Pasal 4 huruf b jo. Pasal 16</h3>
          <p class="pasal__topik">Kebencian karena perbedaan ras dan etnis</p>
          <p>Melarang tindakan yang menunjukkan kebencian atau rasa benci kepada orang karena perbedaan ras dan etnis, antara lain lewat tulisan atau gambar di tempat umum, maupun pidato dan ucapan di tempat umum yang dapat didengar orang lain.</p>
          <p class="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 5 tahun dan/atau denda paling banyak Rp500 juta</p>
        </article>
        <article class="card pasal m-nolift span-6" id="se-kapolri">
          <h3>Surat Edaran Kapolri No. SE/6/X/2015</h3>
          <p class="pasal__topik">Pedoman penanganan ujaran kebencian</p>
          <p>Surat edaran ini bukan undang-undang, melainkan pedoman bagi kepolisian. Isinya menjelaskan pengertian, bentuk dan aspek yang disasar ujaran kebencian, serta mengutamakan pencegahan dan mediasi sebelum penegakan hukum.</p>
          <p class="pasal__catatan">Menjadi acuan definisi dan kategori ujaran kebencian di Cek Hatespeech.</p>
        </article>
        <article class="card pasal m-nolift span-12" id="aturan-kampus">
          <h3><?= e(PASAL['kampus']['nama']) ?></h3>
          <p class="pasal__topik"><?= e(PASAL['kampus']['judul']) ?></p>
          <p><?= e(PASAL['kampus']['isi']) ?> Di Telkom University Purwokerto, laporan diserahkan ke <?= e(KAMPUS['tujuan']) ?>.</p>
          <p class="pasal__catatan">Sanksi bagi pelaku: <?= e(lcfirst(PASAL['kampus']['ancaman'])) ?>. Inilah dasar kampus menangani laporan yang dibuat lewat Cek Hatespeech.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="doc-sec" id="ringkasan" aria-labelledby="ringkasan-h">
    <div class="wrap">
      <div class="doc-head"><h2 id="ringkasan-h">Ringkasan ancaman pidana</h2><p>Ancaman terberat menurut tiap pasal</p></div>
      <div class="doc-tabel-wrap">
        <table class="doc-tabel">
          <thead><tr><th scope="col">Pasal</th><th scope="col">Perbuatan</th><th scope="col">Ancaman maksimal</th></tr></thead>
          <tbody>
            <tr><td>UU ITE Pasal 28 (2)</td><td>Menyebarkan kebencian berdasarkan identitas secara elektronik</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
            <tr><td>UU ITE Pasal 28 (3)</td><td>Berita bohong yang menimbulkan kerusuhan</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
            <tr><td>UU 40/2008 Pasal 16</td><td>Kebencian karena perbedaan ras dan etnis</td><td>5 tahun dan/atau Rp500 juta</td></tr>
            <tr><td>UU ITE Pasal 29</td><td>Ancaman kekerasan atau menakut-nakuti secara pribadi</td><td>4 tahun dan/atau Rp750 juta</td></tr>
            <tr><td>KUHP Pasal 243</td><td>Menyebarluaskan konten permusuhan terhadap golongan</td><td>4 tahun atau Rp200 juta</td></tr>
            <tr><td>KUHP Pasal 242</td><td>Penghinaan terhadap golongan penduduk</td><td>3 tahun atau Rp200 juta</td></tr>
            <tr><td>KUHP Pasal 300</td><td>Permusuhan atas dasar agama atau kepercayaan</td><td>3 tahun atau Rp200 juta</td></tr>
            <tr><td>UU ITE Pasal 27A</td><td>Menyerang kehormatan atau nama baik (delik aduan)</td><td>2 tahun dan/atau Rp400 juta</td></tr>
          </tbody>
        </table>
      </div>
      <p class="doc-catatan">Penjelasan di halaman ini adalah ringkasan untuk edukasi, bukan nasihat hukum. Untuk keperluan hukum, rujuk naskah resmi peraturan dan putusan pengadilan yang berlaku.</p>
    </div>
  </section>

</main>
<?php
penutup();
