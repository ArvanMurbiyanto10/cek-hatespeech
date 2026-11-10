<?php
/*
 * Beranda: alat bantu melapor perundungan untuk mahasiswa Telkom University Purwokerto.
 * Alur: kenali tanda -> 4 langkah -> serahkan ke Satgas PPKPT -> privasi -> pertanyaan.
 * Warna merah putih. Semua bagian setelah hero memakai doc-sec (tinggi mengikuti isi).
 */

$tujuan = KAMPUS['tujuan'];
$kontak = array_filter([
    'Email'    => KAMPUS['email'],
    'WhatsApp' => KAMPUS['whatsapp'],
    'Tempat'   => KAMPUS['tempat'],
]);

$css = <<<'CSS'
/* ---------- hero ---------- */
.hero--split .hero__kicker { display: inline-flex; align-items: center; gap: 8px; font: 500 14px/1.3 var(--font-text); color: var(--p-accent-ink); background: var(--p-accent); padding: 6px 12px; border-radius: 99px; }
.hero--split .hero__kicker .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--p-action); }
.hero__fakta { margin-top: var(--s-5); display: flex; flex-wrap: wrap; gap: 6px 18px; font: 400 14px/1.4 var(--font-text); color: #4a4545; }
.hero__fakta span { display: inline-flex; align-items: center; gap: 8px; }
.hero__fakta span::before { content: ""; width: 14px; height: 14px; border-radius: 50%; background: var(--p-accent) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 14'%3E%3Cpath d='M4 7.2l2 2 4-4.4' fill='none' stroke='%23a8101a' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center/14px no-repeat; }
.hero--split .hero__media img { object-position: 40% 40%; }
.hero--split .ly-float__title { max-width: 26ch; }
.hero--split h1 { font-size: clamp(38px, 4.3vw, 62px); line-height: 1.04; max-width: 14ch; }

/* ---------- bagian umum ---------- */
.sec-head { display: grid; grid-template-columns: minmax(0, 6fr) minmax(0, 5fr); gap: var(--s-6) var(--gutter); align-items: end; margin-bottom: var(--s-8); }
.sec-head p { margin: 0; font-size: 18px; line-height: 1.6; color: #4a4545; max-width: 52ch; }
@media (max-width: 899px) { .sec-head { grid-template-columns: minmax(0, 1fr); } }

/* ---------- #tanda: percakapan grup + daftar tanda ---------- */
.tanda { display: grid; grid-template-columns: minmax(0, 6fr) minmax(0, 6fr); gap: var(--gutter); align-items: start; }
.chat { background: #fff; border: 1px solid #ece6e5; border-radius: 18px; overflow: hidden; box-shadow: 0 30px 60px -40px rgb(80 10 15 / .35); font: 400 15px/1.45 var(--font-text); }
.chat__bar { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #f0eaea; }
.chat__ava { width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; background: #f3dcdc; color: #8c0d15; font: 600 13px/1 var(--font-text); flex: none; }
.chat__bar b { display: block; font-weight: 600; font-size: 15px; color: #1d1a1a; }
.chat__bar small { font-size: 13px; color: #6a6262; }
.chat__body { display: grid; gap: 12px; padding: 18px; background: #fbf8f7; }
.msg { max-width: 84%; padding: 9px 13px 10px; border-radius: 4px 14px 14px 14px; background: #fff; border: 1px solid #efe8e7; color: #1d1a1a; }
.msg b { display: block; font-size: 13px; font-weight: 600; margin-bottom: 2px; }
.msg time { display: block; margin-top: 4px; font-size: 12px; color: #8a8181; text-align: right; }
.msg--foto .msg__img { margin: 4px 0 2px; height: 96px; border-radius: 8px; background: linear-gradient(135deg, #d9cfcd, #bfb1ae); display: grid; place-items: center; color: #fff; font-size: 13px; font-weight: 500; }
.msg__tag { display: inline-block; margin-top: 6px; padding: 2px 8px; border-radius: 99px; background: #fbe5e5; color: #8c0d15; font-size: 12px; font-weight: 500; }
.msg:nth-child(2) b { color: #9b4a00; } .msg:nth-child(3) b { color: #23608f; } .msg:nth-child(4) b { color: #9b4a00; } .msg:nth-child(5) b { color: #5b3b8c; }
.chat__info { margin: 0; text-align: center; font-size: 13px; color: #6a6262; }
.tanda__list { list-style: none; margin: 0; padding: 0; border-top: 1px solid #ece6e5; }
.tanda__list li { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 14px; padding: 16px 0; border-bottom: 1px solid #ece6e5; }
.tanda__list li > span { width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; background: var(--p-accent); color: var(--p-accent-ink); font: 600 14px/1 var(--font-text); }
.tanda__list h3 { font: 500 18px/1.35 var(--font-text); margin: 0; }
.tanda__list p { margin: 2px 0 0; font-size: 15px; line-height: 1.55; color: #4a4545; }
.tanda__catatan { margin-top: var(--s-5); padding: 14px 16px; border-left: 3px solid var(--p-action); background: #fdf6f6; font-size: 15px; line-height: 1.6; color: #2c2727; }
@media (max-width: 899px) { .tanda { grid-template-columns: minmax(0, 1fr); } .msg { max-width: 92%; } }

/* ---------- #alur: langkah + pratinjau PDF ---------- */
.alur { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: var(--gutter); align-items: center; }
.alur__steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; counter-reset: lk; }
.alur__steps li { position: relative; padding: 16px 0 16px 60px; counter-increment: lk; }
.alur__steps li::before { content: counter(lk); position: absolute; left: 0; top: 14px; width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; background: var(--p-action); color: #fff; font: 600 16px/1 var(--font-text); }
.alur__steps li:not(:last-child)::after { content: ""; position: absolute; left: 19px; top: 58px; bottom: -14px; width: 2px; background: #ecd2d3; }
.alur__steps h3 { font: 500 19px/1.35 var(--font-text); margin: 0; }
.alur__steps p { margin: 4px 0 0; font-size: 15px; line-height: 1.55; color: #4a4545; }
.kertas-wrap { background: #efe7e5; border-radius: 22px; padding: clamp(20px, 4vw, 48px); display: grid; place-items: center; }
.kertas { width: min(100%, 440px); aspect-ratio: 210 / 297; background: #fff; border-radius: 4px; box-shadow: 0 1px 2px rgb(0 0 0 / .08), 0 30px 60px -30px rgb(60 10 15 / .45); padding: 0 28px 24px; font: 400 10.5px/1.45 var(--font-text); color: #1d1a1a; overflow: hidden; }
.kertas p { margin: 0; }
.kertas__pita { height: 8px; margin: 0 -28px 18px; background: #a8101a; }
.kertas h4 { margin: 0; font: 700 14px/1.25 var(--font-text); letter-spacing: .01em; }
.kertas__sub { margin: 4px 0 12px !important; color: #6a6262; }
.kertas__judul { margin: 12px 0 6px !important; font: 600 11px/1.3 var(--font-text); color: #a8101a; text-transform: uppercase; letter-spacing: .04em; }
.kertas dl { display: grid; grid-template-columns: 34% 1fr; gap: 3px 8px; margin: 0; }
.kertas dt { color: #6a6262; } .kertas dd { margin: 0; }
.kertas__kutip { padding: 8px 10px; border-left: 2px solid #a8101a; background: #faf5f4; }
.kertas__status { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 7px 10px; border-radius: 6px; background: #fdf2e0; color: #7d4a00; font-weight: 600; }
.kertas__bar { height: 5px; margin-top: 6px; border-radius: 9px; background: #eee; overflow: hidden; } .kertas__bar i { display: block; height: 100%; width: 65%; background: #1d1a1a; }
.kertas__ttd { display: flex; justify-content: flex-end; margin-top: 16px; text-align: center; } .kertas__ttd div { width: 46%; } .kertas__ttd span { display: block; height: 30px; }
@media (max-width: 899px) { .alur { grid-template-columns: minmax(0, 1fr); } }

/* ---------- #serahkan: pita merah ---------- */
#serahkan .sec-head p { color: #f6d2d4; }
.tujuan { display: grid; grid-template-columns: minmax(0, 6fr) minmax(0, 3fr) minmax(0, 3fr); gap: var(--s-5); }
.tujuan article { padding: 24px; border-radius: 16px; background: rgb(255 255 255 / .08); border: 1px solid rgb(255 255 255 / .2); }
.tujuan article.utama { background: #fff; color: #1d1a1a; border-color: #fff; }
.tujuan .label { display: inline-block; font: 500 12px/1 var(--font-text); letter-spacing: .04em; text-transform: uppercase; padding: 5px 8px; border-radius: 99px; background: rgb(255 255 255 / .16); color: #fff; }
.tujuan .utama .label { background: #fbe5e5; color: #8c0d15; }
.tujuan h3 { margin: 14px 0 6px; font: 500 22px/1.25 var(--font-display); letter-spacing: -0.01em; color: inherit; }
.tujuan p { margin: 0; font-size: 15px; line-height: 1.6; color: #f6d2d4; }
.tujuan .utama p { color: #4a4545; }
.tujuan dl { display: grid; grid-template-columns: auto 1fr; gap: 6px 14px; margin: 14px 0 0; font-size: 15px; } .tujuan dt { color: #6a6262; } .tujuan dd { margin: 0; font-weight: 500; }
.tujuan .utama .tujuan__kosong { margin-top: 14px; padding: 10px 12px; border-radius: 10px; background: #f7f3f2; font-size: 14px; color: #2c2727; }
.aturan { margin: var(--s-6) 0 0; font-size: 14px; line-height: 1.6; color: #f6d2d4; max-width: 80ch; }
.aturan a { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
@media (max-width: 899px) { .tujuan { grid-template-columns: minmax(0, 1fr); } }

/* ---------- #privasi ---------- */
.privasi { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: var(--gutter); align-items: center; }
.privasi__foto { border-radius: 20px; overflow: hidden; aspect-ratio: 4 / 3.4; }
.privasi__foto img { width: 100%; height: 100%; object-fit: cover; object-position: 70% 50%; display: block; }
.privasi h2 { margin-bottom: var(--s-5); }
.data-rows { list-style: none; margin: 0; padding: 0; }
.data-rows li { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 16px; padding: 16px 0; border-top: 1px solid #ece6e5; font-size: 16px; line-height: 1.55; }
.data-rows li:last-child { border-bottom: 1px solid #ece6e5; }
.data-rows b { font-weight: 600; }
.data-rows .ya { color: #17603b; } .data-rows .tidak { color: #a8101a; }
@media (max-width: 899px) { .privasi { grid-template-columns: minmax(0, 1fr); } .data-rows li { grid-template-columns: minmax(0, 1fr); gap: 2px; } }

/* ---------- #faq ---------- */
.faq-wrap { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: var(--gutter); align-items: start; }
.darurat { margin: var(--s-6) 0; padding: 18px 20px; border-radius: 14px; background: #fff; border: 1px solid #ecd2d3; }
.darurat b { display: block; font-size: 17px; color: #a8101a; margin-bottom: 4px; }
.darurat p { margin: 0; font-size: 15px; line-height: 1.6; color: #2c2727; }
.darurat p + p { margin-top: 8px; }
@media (max-width: 899px) { .faq-wrap { grid-template-columns: minmax(0, 1fr); } }
/* ---------- #pasal ---------- */
.pasal-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: var(--s-5); }
.pasal-kartu { display: flex; flex-direction: column; padding: 24px; border-radius: 16px; background: #fff; border: 1px solid #ece6e5; border-top: 4px solid #a8101a; }
.pasal-kartu .kapan { align-self: flex-start; padding: 4px 10px; border-radius: 99px; background: #fbe5e5; color: #8c0d15; font: 500 13px/1.3 var(--font-text); }
.pasal-kartu h3 { margin: 14px 0 4px; font: 600 18px/1.3 var(--font-text); color: #1d1a1a; }
.pasal-kartu .sub { margin: 0; font-size: 15px; color: #5c5656; }
.pasal-kartu .isi { margin: 12px 0 0; font-size: 15px; line-height: 1.6; color: #2c2727; }
.pasal-kartu .ancaman { margin: auto 0 0; padding-top: 16px; }
.pasal-kartu .ancaman span { display: block; padding: 10px 12px; border-radius: 10px; background: #fdf3f3; color: #8c0d15; font-weight: 600; font-size: 15px; line-height: 1.45; }
.pasal-kartu .catatan { margin: 10px 0 0; font-size: 13px; line-height: 1.5; color: #5c5656; }
.pasal-kampus { margin-top: var(--s-5); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px 24px; align-items: center; padding: 18px 22px; border-radius: 14px; background: #f7f3f2; }
.pasal-kampus p { margin: 0; font-size: 15px; line-height: 1.6; color: #2c2727; }
.pasal-kampus b { color: #1d1a1a; }
@media (max-width: 899px) { .pasal-grid, .pasal-kampus { grid-template-columns: minmax(0, 1fr); } }
CSS;

kepala([
    'judul'     => 'Cek Hatespeech: lapor perundungan untuk mahasiswa Telkom University Purwokerto',
    'deskripsi' => 'Tempel pesan atau komentar yang menyerangmu, cek dengan AI, lalu unduh PDF laporan untuk diserahkan ke Satgas PPKPT Telkom University Purwokerto.',
    'css'       => $css,
    'kanonik'   => '/',
]);
navigasi('beranda');
?>
<main id="top">

  <!-- HERO (split): teks di kiri, foto mahasiswi yang membaca pesan di kanan -->
  <section class="hero hero--split" data-enter aria-labelledby="hero-h">
    <div class="wrap"><div class="hero-text grid hero__grid">
      <div class="hero__text span-5">
        <p class="hero__kicker"><span class="dot" aria-hidden="true"></span>Untuk mahasiswa Telkom University Purwokerto</p>
        <h1 id="hero-h">Lapor perundungan di Telkom University Purwokerto</h1>
        <p class="hero__lead">Tempel pesan atau komentar yang menyerangmu. AI menilai apakah isinya ujaran kebencian atau kata kasar, lalu kamu mengunduh PDF laporan untuk diserahkan ke <?= e($tujuan) ?>.</p>
        <div class="btn-row">
          <a class="btn btn--primary" href="/lapor">Buat laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a>
          <a class="btn btn--secondary" href="#tanda">Kenali tanda perundungan</a>
        </div>
        <p class="hero__fakta"><span>Gratis, tanpa akun</span><span>Nama dan NIM tidak dikirim ke server</span><span>PDF A4 siap cetak</span></p>
      </div>
      <div class="hero__media span-6 start-7">
        <img src="<?= e(urlFoto('hate-speech')) ?>" alt="Mahasiswi menunduk sambil membaca pesan di ponselnya" fetchpriority="high">
        <div class="ly-float">
          <div class="ly-float__row"><span class="ly-live">PDF laporan</span><span>1 halaman A4</span></div>
          <div class="ly-float__title">Siap diserahkan ke <?= e($tujuan) ?></div>
        </div>
      </div>
    </div></div>
  </section>

  <!-- TANDA: contoh percakapan grup kelas dan lima tanda perundungan -->
  <section class="doc-sec" id="tanda" aria-labelledby="tanda-h">
    <div class="wrap">
      <div class="sec-head">
        <h2 id="tanda-h">Tanda kamu sedang dirundung</h2>
        <p>Perundungan sering dianggap bercanda oleh pelakunya. Kalau perlakuan ini membuatmu takut, malu atau enggan datang ke kampus, itu sudah cukup alasan untuk melapor.</p>
      </div>
      <div class="tanda">
        <div class="chat" role="img" aria-label="Contoh percakapan grup kelas: seorang mahasiswa dikucilkan dari kelompok, logatnya diejek, fotonya disebar dan ia diancam">
          <div class="chat__bar"><span class="chat__ava" aria-hidden="true">IF</span><div><b>Grup Kelas IF-23-B</b><small>38 anggota</small></div></div>
          <div class="chat__body" aria-hidden="true">
            <p class="chat__info">Hari ini</p>
            <div class="msg"><b>Raka</b>Kelompok 3 udah penuh ya. Dimas ga usah diajak, nanti nyusahin<time>20.14</time><span class="msg__tag">Pengucilan</span></div>
            <div class="msg"><b>Sinta</b>wkwk setuju, ngomongnya ngapak banget pas presentasi, malu-maluin<time>20.15</time><span class="msg__tag">Ejekan logat</span></div>
            <div class="msg msg--foto"><b>Raka</b><div class="msg__img">Foto Dimas saat presentasi</div>liat mukanya pas ditanya dosen<time>20.17</time><span class="msg__tag">Foto disebar</span></div>
            <div class="msg"><b>Bayu</b>besok kalau dia masih nongol di lab, kita kerjain lagi<time>20.21</time><span class="msg__tag">Ancaman</span></div>
          </div>
        </div>
        <div>
          <ul class="tanda__list">
            <li><span>1</span><div><h3>Dihina berulang di grup kelas atau angkatan</h3><p>Ejekan soal logat, fisik, nilai atau asal daerah yang terus diulang.</p></div></li>
            <li><span>2</span><div><h3>Diserang karena suku, agama atau ras</h3><p>Kalimat yang merendahkan identitasmu termasuk ujaran kebencian.</p></div></li>
            <li><span>3</span><div><h3>Sengaja dikucilkan</h3><p>Tidak diajak kelompok tugas, dikeluarkan dari grup atau disuruh menjauh.</p></div></li>
            <li><span>4</span><div><h3>Foto atau data pribadimu disebar</h3><p>Diedit, dijadikan bahan tertawaan atau diunggah tanpa izin.</p></div></li>
            <li><span>5</span><div><h3>Diancam lewat pesan atau langsung</h3><p>Ancaman untuk "dikerjain", dipukul atau dipermalukan.</p></div></li>
          </ul>
          <p class="tanda__catatan">Satu kejadian yang berat tetap boleh dilaporkan. Kamu tidak harus menunggu sampai terjadi berulang kali.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ALUR: empat langkah dan pratinjau PDF yang dihasilkan -->
  <section class="doc-sec ground-surface" id="alur" aria-labelledby="alur-h">
    <div class="wrap">
      <div class="alur">
        <div>
          <h2 id="alur-h" style="margin-bottom: var(--s-6)">4 langkah dari bukti ke laporan</h2>
          <ol class="alur__steps">
            <li><h3>Isi data dirimu</h3><p>Nama, NIM, program studi dan kontak yang bisa dihubungi Satgas.</p></li>
            <li><h3>Ceritakan kejadiannya</h3><p>Kapan, di mana atau lewat aplikasi apa, dan siapa yang terlibat kalau kamu tahu.</p></li>
            <li><h3>Tempel bukti, AI memeriksa</h3><p>Salin pesan atau komentarnya. Model XLM-RoBERTa memberi skor ujaran kebencian dan kata kasar, lalu aplikasi menunjukkan pasal UU ITE yang mungkin terkait.</p></li>
            <li><h3>Unduh PDF, serahkan ke kampus</h3><p>Cetak atau kirim PDF ke <?= e($tujuan) ?>. Simpan juga bukti aslinya.</p></li>
          </ol>
          <p class="btn-row" style="margin-top: var(--s-6)"><a class="btn btn--primary" href="/lapor">Buat laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div class="kertas-wrap">
          <div class="kertas" role="img" aria-label="Contoh halaman PDF laporan dugaan perundungan berisi data pelapor, bukti dan hasil pemeriksaan AI">
            <div class="kertas__pita"></div>
            <h4>LAPORAN DUGAAN PERUNDUNGAN</h4>
            <p class="kertas__sub">Kepada: <?= e($tujuan) ?><br>No. LP-20261111-2014</p>
            <p class="kertas__judul">1. Data pelapor</p>
            <dl><dt>Nama</dt><dd>Dimas Pratama</dd><dt>NIM</dt><dd>23111020xx</dd><dt>Program studi</dt><dd>S1 Teknik Informatika</dd><dt>Melapor sebagai</dt><dd>Korban</dd></dl>
            <p class="kertas__judul">2. Kejadian</p>
            <dl><dt>Tanggal</dt><dd>10 November 2026</dd><dt>Tempat</dt><dd>Grup WhatsApp kelas</dd></dl>
            <p class="kertas__judul">3. Bukti dan pemeriksaan AI</p>
            <p class="kertas__kutip">"Kaum mereka selalu bikin masalah di kampung ini"</p>
            <div class="kertas__status"><span>Perlu ditinjau manusia</span><span>65,21%</span></div>
            <div class="kertas__bar"><i></i></div>
            <p class="kertas__judul">4. Dasar hukum yang mungkin terkait</p>
            <p>UU ITE Pasal 28 ayat (2) jo. Pasal 45A ayat (2)<br>Permendikbudristek No. 55 Tahun 2024</p>
            <div class="kertas__ttd"><div>Purwokerto, 11 November 2026<span></span>( Dimas Pratama )</div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PASAL: UU ITE yang bisa menjerat pelaku, dipilih otomatis di laporan -->
  <section class="doc-sec" id="pasal" aria-labelledby="pasal-h">
    <div class="wrap">
      <div class="sec-head">
        <h2 id="pasal-h">Pasal UU ITE yang bisa menjerat pelaku</h2>
        <p>Saat kamu membuat laporan, aplikasi memilih pasal yang mungkin terkait dari hasil AI dan bentuk perundungan yang kamu centang, lalu mencetaknya di PDF.</p>
      </div>
      <div class="pasal-grid">
<?php foreach ([['28-2', 'Kalau kamu diserang karena suku, agama atau ras'], ['29', 'Kalau kamu diancam lewat pesan'], ['27a', 'Kalau kamu dihina atau dituduh di depan umum']] as [$k, $kapan]): $p = PASAL[$k]; ?>
        <article class="pasal-kartu">
          <span class="kapan"><?= e($kapan) ?></span>
          <h3><?= e($p['nama']) ?></h3>
          <p class="sub"><?= e($p['judul']) ?></p>
          <p class="isi"><?= e($p['isi']) ?></p>
          <p class="ancaman"><span><?= e($p['ancaman']) ?></span></p>
          <?php if ($p['catatan']): ?><p class="catatan"><?= e($p['catatan']) ?></p><?php endif; ?>
        </article>
<?php endforeach; ?>
      </div>
      <div class="pasal-kampus">
        <p><b><?= e(PASAL['kampus']['nama']) ?></b> mewajibkan kampus menangani perundungan meski tidak ada unsur pidana. Sanksi bagi pelaku: <?= e(lcfirst(PASAL['kampus']['ancaman'])) ?>.</p>
        <a class="link" href="/dasar-hukum">Baca semua pasal dan unsurnya</a>
      </div>
    </div>
  </section>

  <!-- SERAHKAN: tujuan laporan di kampus, di pita merah -->
  <section class="doc-sec ground-inverse" id="serahkan" aria-labelledby="serahkan-h">
    <div class="wrap">
      <div class="sec-head">
        <h2 id="serahkan-h">Ke mana PDF diserahkan</h2>
        <p>Web ini tidak mengirim laporan secara otomatis. Kamu yang menyerahkan PDF, jadi kamu tetap memegang kendali atas laporanmu.</p>
      </div>
      <div class="tujuan">
        <article class="utama">
          <span class="label">Tujuan utama</span>
          <h3><?= e($tujuan) ?></h3>
          <p><?= e(KAMPUS['tujuan_lengkap']) ?> menerima dan menangani laporan kekerasan di kampus, termasuk perundungan.</p>
          <?php if ($kontak): ?>
            <dl><?php foreach ($kontak as $k => $v): ?><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd><?php endforeach; ?></dl>
          <?php else: ?>
            <p class="tujuan__kosong">Tanyakan kontak resmi Satgas ke bagian kemahasiswaan atau lewat akun resmi kampus.</p>
          <?php endif; ?>
        </article>
        <article>
          <span class="label">Atau</span>
          <h3>Dosen wali atau Kaprodi</h3>
          <p>Kalau kamu belum siap ke Satgas, dosen wali bisa mendampingimu melapor.</p>
        </article>
        <article>
          <span class="label">Pendampingan</span>
          <h3>Layanan konseling</h3>
          <p>Untuk bercerita dan menenangkan diri, sebelum atau sesudah melapor.</p>
        </article>
      </div>
      <p class="aturan">Kalau pelaku juga perlu diproses pidana menurut UU ITE, Satgas dapat mendampingimu melapor ke kepolisian. <a href="/dasar-hukum">Baca dasar hukumnya</a></p>
    </div>
  </section>

  <!-- PRIVASI: apa yang dikirim dan tidak -->
  <section class="doc-sec" id="privasi" aria-labelledby="privasi-h">
    <div class="wrap">
      <div class="privasi">
        <div class="privasi__foto"><img src="<?= e(urlFoto('hero')) ?>" alt="Tangan memegang ponsel yang menampilkan media sosial" loading="lazy"></div>
        <div>
          <h2 id="privasi-h">Nama dan NIM tetap di perangkatmu</h2>
          <ul class="data-rows">
            <li><b class="ya">Dikirim</b><span>Hanya teks bukti, ke server model AI untuk dinilai. Teks tidak disimpan.</span></li>
            <li><b class="tidak">Tidak dikirim</b><span>Nama, NIM, kontak, kronologi dan tangkapan layar. Semuanya diolah di browsermu.</span></li>
            <li><b>Disimpan</b><span>Tidak ada. PDF dibuat langsung di perangkatmu. Tutup tab dan isian ikut hilang.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ: pertanyaan sebelum melapor, dengan nomor darurat -->
  <section class="doc-sec ground-surface" id="faq" aria-labelledby="faq-h">
    <div class="wrap">
      <div class="faq-wrap">
        <div>
          <h2 id="faq-h">Pertanyaan sebelum melapor</h2>
          <div class="darurat">
            <b>Dalam bahaya sekarang?</b>
            <p>Hubungi 112 atau minta tolong orang terdekat. Jangan tunggu sampai laporan selesai.</p>
            <p>Kalau pikiranmu terasa sangat berat, hubungi layanan kesehatan jiwa 119 ekstensi 8 atau orang yang kamu percaya.</p>
          </div>
          <p class="btn-row"><a class="btn btn--primary" href="/lapor">Buat laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div class="faq">
          <div class="faq__item"><h3>Apakah laporanku langsung terkirim ke kampus?</h3><p>Tidak. Web ini membuat PDF. Kamu yang menyerahkannya ke <?= e($tujuan) ?>, langsung atau lewat kanal resmi mereka.</p></div>
          <div class="faq__item"><h3>Bagaimana kalau AI bilang tidak terindikasi?</h3><p>Kamu tetap boleh melapor. AI hanya menilai teks, sedangkan perundungan juga bisa berupa pengucilan, ancaman langsung atau kekerasan fisik.</p></div>
          <div class="faq__item"><h3>Bolehkah melapor untuk teman?</h3><p>Boleh. Pilih "Saksi" di formulir, isi data dirimu, lalu ceritakan apa yang kamu lihat.</p></div>
          <div class="faq__item"><h3>Bukti apa yang sebaiknya kusimpan?</h3><p>Tangkapan layar utuh yang memperlihatkan nama akun, isi pesan dan tanggal. Jangan hapus percakapan aslinya.</p></div>
          <div class="faq__item"><h3>Apakah identitasku akan dirahasiakan?</h3><p>Sampaikan ke Satgas kalau kamu ingin identitasmu dijaga. Web ini sendiri tidak menyimpan data apa pun.</p></div>
        </div>
      </div>
    </div>
  </section>

</main>
<?php
penutup();
