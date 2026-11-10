<?php
/*
 * Beranda Cek Hatespeech.
 * type: software (alat AI) · kind software
 * deal: hero centred · palette white-mint-tiles (dasar putih seperti konsol moderasi, mint menjawab
 *       status "aman" yang dicari pengguna) · type host · nav-centre · close-word · footer-dark ·
 *       rhythm dark-band (#hasil) · buttons arrow · mark dots · lit ground sand ·
 *       effect grain-gradient (bingkai hero) · card grounds: tidak ada yang cocok (slot dilepas) ·
 *       grounds: #cara-kerja bg-mist, #faq bg-band
 * moments: liquid-type (#pernyataan: ancaman pidana Pasal 28 ayat (2) sebagai satu kalimat besar)
 * screens @1440: hero 1 · cek 1 · cara-kerja 2 · hasil 2 · pernyataan 1 · hukum 1 · model 1 · faq 1 · closing 1
 */

$contoh = [
    'Netral'    => 'Terima kasih banyak atas bantuannya, semoga hari kalian menyenangkan!',
    'Kasar'     => 'Anjing, macet parah lagi pagi ini',
    'Kebencian' => 'Semua pendatang dari suku itu pencuri, jangan kasih tempat',
];

$css = <<<'CSS'
/* ---------- hero: jendela antrean cek di bingkai bercahaya (page CSS di dalam visual) ---------- */
.hero__window { background:
  radial-gradient(70% 90% at 22% 18%, #f4ede2 0%, #e9dfcf 38%, transparent 70%),
  radial-gradient(80% 90% at 82% 90%, #cfdcc9 0%, transparent 62%),
  linear-gradient(160deg, #e7dccb, #d9ccb7 55%, #cdbfa6); }
.hero--centred h1 .mark { width: .7em; height: .7em; vertical-align: -.04em; margin-inline: .04em .12em; }
.hw { background: #fff; color: #1e1f21; border-radius: 14px; overflow: hidden; font: 400 14px/1.45 var(--font-text);
      box-shadow: 0 0 0 1px rgb(0 0 0 / .08), 0 28px 64px -30px rgb(0 0 0 / .45); text-align: left; }
.hw__bar { display: flex; align-items: center; gap: 10px; height: 40px; padding: 0 14px; border-bottom: 1px solid rgb(0 0 0 / .07); font-size: 13px; color: #5d6268; }
.hw__bar b { color: #1e1f21; font-weight: 500; } .hw__sp { flex: 1; }
.hw__dots { display: flex; gap: 6px; } .hw__dots i { width: 10px; height: 10px; border-radius: 50%; background: #dcdfe2; }
.hw__body { display: grid; grid-template-columns: 272px minmax(0, 1fr); min-height: 372px; }
.hw__list { border-right: 1px solid rgb(0 0 0 / .07); padding: 10px; display: grid; align-content: start; gap: 2px; }
.hw__q { height: 34px; margin-bottom: 6px; border-radius: 9px; background: #f3f3f3; display: flex; align-items: center; padding: 0 10px; color: #6e7378; font-size: 13px; }
.hw__t { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 9px; font-size: 13px; }
.hw__t.is-on { background: #f3f3f3; }
.hw__t span { color: #33363a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hw__t time { color: #6e7378; }
.hw__convo { display: grid; gap: 10px; align-content: start; padding: 16px 18px; }
.hw__me { justify-self: end; max-width: 86%; padding: 10px 14px; border-radius: 16px 16px 6px 16px; background: #f2f2f0; color: #121314; }
.hw__tool { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6e7378; }
.hw__tool::before { content: ""; width: 12px; height: 12px; border-radius: 50%; border: 2px solid currentColor; border-right-color: transparent; }
.hw__bot { max-width: 90%; padding: 10px 14px; border-radius: 16px 16px 16px 6px; background: #232323; color: #f5f5f5; }
.hw__bot small { display: inline; margin-left: 6px; font-size: 13px; color: #c9c9c9; white-space: nowrap; }
.hw__acts { display: flex; gap: 8px; } .hw__acts span { height: 30px; padding: 0 12px; border-radius: 99px; display: inline-flex; align-items: center; font-size: 13px; font-weight: 500; background: #a1ffcb; color: #1b3a29; }
.hw__acts span + span { background: #f0f0f0; color: #232323; }
.st { display: inline-flex; align-items: center; gap: 6px; height: 22px; padding: 0 8px; border-radius: 99px; font: 500 12px/1 var(--font-text); white-space: nowrap; background: #efefef; color: #3c3f43; }
.st::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.st--kuat { background: #fbe6e3; color: #a3271b; } .st--tinjau { background: #fdf0dc; color: #7d4a00; }
.st--kasar { background: #e9eef5; color: #2e4462; } .st--aman { background: #e1f5e8; color: #17603b; }
@media (max-width: 639px) { .hw__body { grid-template-columns: 1fr; min-height: 268px; } .hw__list, .hw__tool { display: none; } .hw__bar .hw__sp ~ span { display: none; } .hw__bot small { display: none; } }

/* ---------- #cek: jendela aplikasi (resep app-frame) berisi form asli ---------- */
.app.ui { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); margin-top: var(--s-7); background: var(--u-bg); border: 1px solid var(--u-border); border-radius: var(--u-r); box-shadow: var(--u-shadow); }
.app > .app__pane { position: relative; min-width: 0; min-height: 0; overflow: hidden; display: flex; flex-direction: column; }
.app > .app__pane + .app__pane { border-left: 1px solid var(--u-line); }
.cek-bar { display: flex; align-items: center; gap: 10px; flex: none; height: 44px; padding: 0 18px; border-bottom: 1px solid var(--u-line); font: 400 13px/1 var(--font-text); color: var(--muted); }
.cek-bar b { font-weight: 500; color: var(--ink); } .cek-bar .hw__sp { flex: 1; }
.cek-form { display: grid; gap: 12px; padding: 20px 20px 24px; align-content: start; }
.cek-form label { font: 500 14px/1.3 var(--font-text); color: var(--ink); }
.cek-form textarea { width: 100%; min-height: 188px; resize: vertical; padding: 14px 16px; border: 1px solid rgb(35 35 35 / .18); border-radius: 12px; background: #fff; color: var(--ink); font: 400 16px/1.55 var(--font-text); }
.cek-form textarea:focus-visible { outline: 2px solid var(--action); outline-offset: 2px; }
.cek-meta { display: flex; justify-content: space-between; gap: 12px; font: 400 13px/1.4 var(--font-text); color: var(--muted); }
.cek-contoh { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.cek-contoh span { font: 400 13px/1 var(--font-text); color: #4a4a4a; }
.cek-contoh button { min-height: 44px; padding: 0 4px; border: 0; background: none; color: var(--ink); font: 500 14px/1 var(--font-text); text-decoration: underline; text-decoration-color: rgb(35 35 35 / .35); text-underline-offset: 4px; cursor: pointer; }
.cek-contoh button:hover { text-decoration-color: var(--ink); }
.cek-form .btn-row { margin-top: 4px; }
.cek-out { padding: 20px 24px 24px; overflow-y: auto; display: grid; gap: 16px; align-content: start; }
.cek-label { margin: 0; font: 500 13px/1.3 var(--font-text); color: var(--muted); }
.cek-panduan { list-style: none; margin: 0; padding: 0; display: grid; }
.cek-panduan li { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 12px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--u-line); font: 400 16px/1.5 var(--font-text); color: var(--ink); }
.cek-panduan li span { color: var(--ink); }
.cek-note { margin: 0; font: 400 16px/1.6 var(--font-text); color: var(--ink); max-width: 52ch; }
.cek-vonis { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 14px; align-items: start; padding: 16px 18px; border-radius: 14px; background: #f4f4f4; }
.cek-vonis--hate { background: #fbe9e6; } .cek-vonis--tinjau { background: #fdf2e0; } .cek-vonis--abusive { background: #ebf0f6; } .cek-vonis--aman { background: #e4f6ea; }
.cek-ikon { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font: 600 18px/1 var(--font-text); color: #fff; background: #232323; }
.cek-vonis--hate .cek-ikon { background: #a3271b; } .cek-vonis--tinjau .cek-ikon { background: #8a5300; } .cek-vonis--abusive .cek-ikon { background: #2e4462; } .cek-vonis--aman .cek-ikon { background: #17603b; }
.cek-judul { margin: 0; font: 500 20px/1.3 var(--font-text); color: var(--ink); }
.cek-sub { margin: 4px 0 0; font: 400 16px/1.5 var(--font-text); color: var(--ink); }
.cek-skor { display: grid; gap: 12px; }
.cek-skor div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; font: 400 16px/1.4 var(--font-text); color: var(--ink); }
.cek-skor b { font-weight: 500; font-variant-numeric: tabular-nums; }
.cek-skor i { grid-column: 1 / -1; display: block; height: 8px; border-radius: 99px; background: #ececec; overflow: hidden; }
.cek-skor i::after { content: ""; display: block; height: 100%; width: var(--v); border-radius: inherit; background: var(--ink); }
.cek-fakta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 0; }
.cek-fakta div { padding: 12px 14px; border: 1px solid var(--u-line); border-radius: 12px; }
.cek-fakta dt { font: 500 13px/1.3 var(--font-text); color: var(--muted); } .cek-fakta dd { margin: 4px 0 0; font: 500 16px/1.35 var(--font-text); color: var(--ink); }
.cek-teks { margin: 0; padding: 14px 16px; border-radius: 12px; background: #f6f6f6; font: 400 16px/1.6 var(--font-text); color: var(--ink); white-space: pre-wrap; overflow-wrap: anywhere; }
.cek-teks mark { padding: 1px 3px; border-radius: 4px; font-weight: 500; }
.hl-hate { background: #fbe6e3; color: #8f2016; box-shadow: inset 0 -2px 0 #a3271b; }
.hl-threat { background: #a3271b; color: #fff; }
.hl-abuse { background: #fdf0dc; color: #6e4100; box-shadow: inset 0 -2px 0 #8a5300; }
.hl-target { background: #e9eef5; color: #233a58; }
.cek-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.cek-tags span { padding: 4px 10px; border: 1px solid var(--u-line); border-radius: 99px; font: 400 13px/1.5 var(--font-text); color: var(--ink); }
.cek-galat { margin: 0; padding: 14px 16px; border-radius: 12px; background: #fbe9e6; color: #8f2016; font: 500 16px/1.5 var(--font-text); }
@media (max-width: 1023px) {
  .app.ui { grid-template-columns: minmax(0, 1fr); }
  .app > .app__pane + .app__pane { border-left: 0; border-top: 1px solid var(--u-line); }
  #cek .app > .app__pane, #cek .cek-out { overflow: visible; min-height: auto; }
}
@media (max-width: 639px) {
  .cek-panduan li { grid-template-columns: minmax(0, 1fr); gap: 2px; }
  .cek-fakta { grid-template-columns: minmax(0, 1fr); }
  .cek-form, .cek-out { padding: 16px; }
}

/* ---------- #cara-kerja: jendela yang digambar untuk tiap langkah ---------- */
.pcw { width: 100%; height: 100%; background: #fff; color: #1e1f21; border-radius: 14px; overflow: hidden; font: 400 14px/1.5 var(--font-text); box-shadow: 0 0 0 1px rgb(0 0 0 / .08), 0 24px 56px -30px rgb(0 0 0 / .4); }
.pcw__code { margin: 0; padding: 20px 22px; font: 400 14px/1.7 var(--font-mono); color: #232323; white-space: pre-wrap; overflow-wrap: anywhere; }
.pcw__code .k { color: #2e4462; } .pcw__code .s { color: #17603b; } .pcw__code .n { color: #a3271b; }
.pcw__rows { display: grid; padding: 0 22px 20px; }
.pcw__rows div { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid rgb(0 0 0 / .07); font-size: 14px; }
.pcw__rows b { font-weight: 500; font-variant-numeric: tabular-nums; }
@media (max-width: 359px) { .nav__bar { gap: 8px; } .nav__logo { font-size: 15px; } }

/* ---------- #hasil: putusan kecil di panel kartu (gelap) ---------- */
#hasil .panel { --p-h: 300px; }
#hasil .ui-panel.vd { --u-bg: #fff; background: #fff; color: #1e1f21; border-radius: 14px; min-height: 0; box-shadow: 0 0 0 1px rgb(0 0 0 / .06), 0 24px 48px -24px rgb(0 0 0 / .5); }
#hasil .vd__head span, #hasil .vd__row span { color: #4a4f55; }
/* #cara-kerja: kartu langkah */
#cara-kerja .langkah .card { display: flex; flex-direction: column; }
#cara-kerja .langkah .card > .panel { margin-top: auto; }
#cara-kerja .langkah .card > p { margin-bottom: 28px; }
#cara-kerja .langkah .panel { --p-h: 280px; padding: 18px; background: #e9eee9; overflow: hidden; }
#cara-kerja .langkah .panel--flush { padding: 0; }
.langkah .pcw { box-shadow: 0 0 0 1px rgb(0 0 0 / .08); }
.langkah .pcw__code { padding: 14px 16px; font-size: 13px; line-height: 1.6; }
.langkah .pcw__rows { padding: 0 16px 12px; } .langkah .pcw__rows div { padding: 8px 0; font-size: 13px; }
.langkah__img { width: 100%; height: 100%; object-fit: cover; object-position: top left; display: block; }
/* #pernyataan: kalimat besar di tengah */
section#pernyataan { display: grid; align-content: center; }
#pernyataan > .wrap { width: 100%; }
#pernyataan .section-head { max-width: none; margin-inline: auto; text-align: center; display: grid; justify-items: center; gap: var(--s-5); }
#pernyataan .mo-lt__type { font-size: clamp(56px, 10.5vw, 168px); line-height: .92; max-width: 12ch; }
#pernyataan .t-lead { margin-inline: auto; }
.vd { width: 100%; min-height: 330px; padding: 16px 18px; font: 400 13px/1.45 var(--font-text); color: #1e1f21; }
.vd__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; font-size: 13px; color: #6e7378; }
.vd__teks { margin: 0 0 12px; font-size: 14px; color: #232323; }
.vd__row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 10px; margin-top: 10px; font-size: 13px; color: #33363a; }
.vd__row b { font-weight: 500; color: #1e1f21; font-variant-numeric: tabular-nums; }
.vd__row i { grid-column: 1 / -1; display: block; height: 6px; border-radius: 99px; background: #ececec; overflow: hidden; }
.vd__row i::after { content: ""; display: block; height: 100%; width: var(--v); border-radius: inherit; background: #232323; }
.vd__row--on i::after { background: #1f9d5c; }
.vd__ref { margin-top: 14px; padding-top: 10px; border-top: 1px solid rgb(0 0 0 / .07); font-size: 13px; color: #33363a; }
@media (max-width: 639px) { #hasil .panel { --p-h: 250px; } #cara-kerja .langkah .panel { --p-h: 240px; } }

/* ---------- #model: diagram alur ---------- */
#model .dg-frame { margin-top: var(--s-7); height: min(520px, calc(100svh - 380px)); min-height: 380px; border-radius: var(--r-lg); background: var(--g-alt); overflow: hidden; }
@media (max-width: 639px) { #model .dg-frame { height: 440px; } #hukum .grid.fill { height: auto; flex: none; } #hukum .frame--photo { position: relative; aspect-ratio: 4 / 3; height: auto; min-height: 0; } .nav__actions { display: none; } }
CSS;

$js = <<<'JS'
(function () {
  var f = document.getElementById('form-cek'); if (!f) return;
  var t = document.getElementById('teks'), n = document.getElementById('hitung'), b = document.getElementById('tombol-cek');
  var maks = +t.getAttribute('maxlength');
  function hitung() { n.textContent = t.value.length.toLocaleString('id-ID') + ' / ' + maks.toLocaleString('id-ID') + ' karakter'; }
  t.addEventListener('input', hitung); hitung();
  document.querySelectorAll('[data-contoh]').forEach(function (c) {
    c.addEventListener('click', function () { t.value = c.getAttribute('data-contoh'); hitung(); t.focus(); });
  });
  document.getElementById('tombol-hapus').addEventListener('click', function () { t.value = ''; hitung(); t.focus(); });
  t.addEventListener('keydown', function (e) { if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') f.requestSubmit(); });
  f.addEventListener('submit', function () { b.setAttribute('aria-busy', 'true'); b.firstChild.nodeValue = 'Sedang dicek '; });
})();
JS;

kepala([
    'judul'     => 'Cek Hatespeech: cek ujaran kebencian untuk teks berbahasa Indonesia',
    'deskripsi' => 'Tempel komentar atau postingan, lalu model XLM-RoBERTa menilai skor ujaran kebencian dan bahasa kasar dalam hitungan detik.',
    'momen'     => ['liquid-type'],
    'diagram'   => true,
    'css'       => $css,
    'kanonik'   => '/',
]);
navigasi();
?>
<main id="top">

  <!-- HERO (centred): pill, judul dengan tanda di dalamnya, dua tombol, lalu jendela antrean cek -->
  <section class="hero hero--centred" data-enter aria-labelledby="hero-h">
    <div class="wrap"><div class="hero-text">
      <div class="hero__c">
        <a class="pill" href="#model"><span>XLM-RoBERTa, F1 92,74%</span><span class="muted">Lihat modelnya</span></a>
        <h1 id="hero-h" aria-label="Deteksi ujaran kebencian teks Indonesia"><span class="mark" data-mark="dots" aria-hidden="true"></span>Deteksi ujaran kebencian teks Indonesia</h1>
        <p class="hero__lead">Tempel komentar hingga 2.000 karakter. Model menilai skor ujaran kebencian dan bahasa kasar secara terpisah.</p>
        <div class="btn-row">
          <a class="btn btn--primary" href="#cek">Cek teks<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a>
          <a class="btn btn--secondary" href="/dasar-hukum">Baca dasar hukum</a>
        </div>
      </div>
      <div class="hero__window" data-fx="grain-gradient" data-fx-seed="11" role="img" aria-label="Antrean cek: kalimat tentang pendatang dari suatu suku dinilai indikasi kuat ujaran kebencian dengan skor 99,33 persen">
        <div class="ly-piece hw" aria-hidden="true">
          <div class="hw__bar"><span class="hw__dots"><i></i><i></i><i></i></span><span><b>Antrean cek</b> · 4 teks</span><span class="hw__sp"></span><span>RuangAman API</span></div>
          <div class="hw__body">
            <div class="hw__list">
              <div class="hw__q">Cari teks</div>
              <div class="hw__t is-on"><span>Semua pendatang dari suku itu pencuri…</span><em class="st st--kuat">Indikasi kuat</em></div>
              <div class="hw__t"><span>Kaum mereka selalu bikin masalah di…</span><em class="st st--tinjau">Perlu ditinjau</em></div>
              <div class="hw__t"><span>Anjing, macet parah lagi pagi ini</span><em class="st st--kasar">Bahasa kasar</em></div>
              <div class="hw__t"><span>Terima kasih banyak atas bantuannya…</span><em class="st st--aman">Aman</em></div>
            </div>
            <div class="hw__convo" data-ly-steps="900">
              <div class="hw__me">Semua pendatang dari suku itu pencuri, jangan kasih tempat</div>
              <div class="hw__tool" data-step>XLM-RoBERTa membaca 9 kata</div>
              <div class="hw__bot" data-step>Indikasi kuat ujaran kebencian. Skor 99,33%, bahasa kasar 0,27%.<small>Pasal 28 ayat (2) UU ITE</small></div>
            </div>
          </div>
        </div>
      </div>
    </div></div>
  </section>

  <!-- CEK (app-frame): form asli di kiri, putusan model di kanan, satu jendela -->
  <section class="section" id="cek" style="--h: 1; --hm: 2" aria-labelledby="cek-h">
    <div class="wrap">
      <div class="grid">
        <h2 id="cek-h" class="span-5">Cek ujaran kebencian di teks Anda</h2>
        <p class="span-6 start-7 muted">Tulis atau tempel teks, tekan Cek teks, lalu baca putusannya: status, dua skor dari model dan kata yang disorot kamus bantu. <a class="link" href="#hasil">Lihat arti tiap status</a></p>
      </div>
      <div class="ui ui-app app">
        <div class="app__pane app__pane--list">
          <div class="cek-bar"><span class="hw__dots"><i></i><i></i><i></i></span><b>Teks</b><span class="hw__sp"></span><span>Maks. 2.000 karakter</span></div>
          <form class="cek-form" id="form-cek" method="post" action="/#cek" autocomplete="off">
            <label for="teks">Teks yang dicek</label>
            <textarea id="teks" name="teks" maxlength="<?= MAKS_KARAKTER ?>" required><?= e($teks) ?></textarea>
            <div class="cek-meta"><span id="hitung" aria-live="polite">0 / 2.000 karakter</span><span>Ctrl + Enter untuk cek</span></div>
            <div class="cek-contoh"><span>Contoh:</span>
              <?php foreach ($contoh as $nama => $kalimat): ?>
                <button type="button" data-contoh="<?= e($kalimat) ?>"><?= e($nama) ?></button>
              <?php endforeach; ?>
            </div>
            <div class="btn-row">
              <button class="btn btn--primary" type="submit" id="tombol-cek">Cek teks<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></button>
              <button class="btn btn--secondary" type="button" id="tombol-hapus">Hapus teks</button>
            </div>
          </form>
        </div>
        <div class="app__pane app__pane--open" aria-live="polite">
          <div class="cek-bar"><b>Putusan</b><span class="hw__sp"></span><span>nahiar/hatespeech-abusive-xlm-roberta-v1</span></div>
          <div class="cek-out" id="hasil-cek">
            <?php if ($galat !== ''): ?>
              <p class="cek-galat" role="alert"><?= e($galat) ?></p>
            <?php endif; ?>
            <?php if ($hasil !== null): $info = $infoLabel[$hasil['label']]; ?>
              <div class="cek-vonis cek-vonis--<?= e($hasil['label']) ?>">
                <span class="cek-ikon" aria-hidden="true"><?= $info['ikon'] ?></span>
                <div><p class="cek-judul"><?= e($info['judul']) ?></p><?php if ($hasil['penjelasan'] !== ''): ?><p class="cek-sub"><?= e($hasil['penjelasan']) ?></p><?php endif; ?></div>
              </div>
              <div class="cek-skor">
                <div><span>Skor ujaran kebencian</span><b><?= persen($hasil['hs']) ?></b><i style="--v: <?= round($hasil['hs'] * 100, 2) ?>%"></i></div>
                <div><span>Skor bahasa kasar</span><b><?= persen($hasil['ab']) ?></b><i style="--v: <?= round($hasil['ab'] * 100, 2) ?>%"></i></div>
              </div>
              <dl class="cek-fakta">
                <div><dt>Sasaran (kamus)</dt><dd><?= e($hasil['target']) ?></dd></div>
                <div><dt>Kategori (kamus)</dt><dd><?= e($hasil['kategori'] ? implode(', ', $hasil['kategori']) : '-') ?></dd></div>
                <div><dt>Tingkat</dt><dd><?= e($hasil['tingkat']) ?></dd></div>
              </dl>
              <p class="cek-label">Teks yang dicek</p>
              <div class="cek-teks"><?= $hasil['html'] ?></div>
              <?php if ($hasil['temuan']): ?>
                <div class="cek-tags">
                  <?php foreach ($hasil['temuan'] as $t): ?>
                    <span><b><?= e($t['kata']) ?></b> · <?= e($namaTipe[$t['tipe']]) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php elseif ($galat === ''): ?>
              <p class="cek-label">Status yang bisa muncul</p>
              <ul class="cek-panduan">
                <li><em class="st st--kuat">Indikasi kuat</em><span>Skor ujaran kebencian 0,8 atau lebih.</span></li>
                <li><em class="st st--tinjau">Perlu ditinjau</em><span>Skor ujaran kebencian 0,5 sampai di bawah 0,8.</span></li>
                <li><em class="st st--kasar">Bahasa kasar</em><span>Skor bahasa kasar 0,8 atau lebih, kebencian di bawah 0,5.</span></li>
                <li><em class="st st--aman">Tidak terindikasi</em><span>Kedua skor di bawah ambang batas.</span></li>
              </ul>
              <p class="cek-note">Putusan muncul di panel ini dalam beberapa detik setelah Anda menekan Cek teks.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CARA KERJA: tiga kartu langkah, masing-masing dengan jendela atau tangkapan layar aslinya -->
  <section class="section bg-mist" id="cara-kerja" aria-labelledby="cara-h">
    <div class="wrap">
      <div class="section-head">
        <h2 id="cara-h">3 langkah deteksi, dari teks ke putusan</h2>
      </div>
      <div class="grid langkah">
        <article class="card m-nolift span-4">
          <h3>1. Mengirim teks ke API model</h3>
          <p>Server PHP di Vercel memeriksa panjang teks, lalu mengirimnya sebagai JSON ke RuangAman API.</p>
          <div class="panel"><div class="pcw" role="img" aria-label="Permintaan POST ke RuangAman API berisi teks yang dicek">
                <div class="hw__bar"><span class="hw__dots"><i></i><i></i><i></i></span><span><b>POST /predict</b></span><span class="hw__sp"></span><span>RuangAman API</span></div>
                <pre class="pcw__code">{
  <span class="k">"teks"</span>: <span class="s">"Semua pendatang dari suku itu pencuri, jangan kasih tempat"</span>
}</pre>
                <div class="pcw__rows"><div><span>Panjang teks</span><b>58 karakter</b></div><div><span>Batas</span><b>2.000 karakter</b></div><div><span>Batas tunggu</span><b>25 detik</b></div></div>
              </div></div>
        </article>
        <article class="card m-nolift span-4">
          <h3>2. Menilai 2 label dengan XLM-RoBERTa</h3>
          <p>Model membaca seluruh kalimat dan memberi skor 0 sampai 1 untuk HATESPEECH dan ABUSIVE, masing-masing terpisah.</p>
          <div class="panel panel--flush"><img class="langkah__img" src="<?= e(urlFoto('colab')) ?>" alt="Dokumentasi RuangAman API dengan endpoint GET / dan POST /predict" width="1280" height="720" loading="lazy" data-source="client" data-subject="dokumentasi API model"></div>
        </article>
        <article class="card m-nolift span-4">
          <h3>3. Menentukan status dari ambang skor</h3>
          <p>Skor 0,8 ke atas menjadi indikasi kuat, 0,5 sampai 0,8 perlu ditinjau, lalu kamus menyorot kata pemicunya.</p>
          <div class="panel"><div class="pcw" role="img" aria-label="Jawaban API: skor HATESPEECH 0,9933, ABUSIVE 0,0027, status INDIKASI_KUAT">
                <div class="hw__bar"><span class="hw__dots"><i></i><i></i><i></i></span><span><b>200 OK</b> · 0,9933</span><span class="hw__sp"></span><span>JSON</span></div>
                <pre class="pcw__code">{
  <span class="k">"skor"</span>: {
    <span class="k">"HATESPEECH"</span>: <span class="n">0.9933</span>,
    <span class="k">"ABUSIVE"</span>: <span class="n">0.0027</span>
  },
  <span class="k">"status"</span>: <span class="s">"INDIKASI_KUAT"</span>,
  <span class="k">"prioritas"</span>: <span class="s">"Tinggi"</span>
}</pre>
              </div></div>
        </article>
      </div>
    </div>
  </section>

  <!-- HASIL (dark-band, features-2x2): empat status, contoh nyata dari model -->
  <section class="section g2 ground-inverse" id="hasil" aria-labelledby="hasil-h">
    <div class="wrap">
      <div class="group">
        <div class="section-head">
          <h2 id="hasil-h">4 status hasil deteksi. <span class="muted">Ditentukan dari dua skor yang dihitung model.</span></h2>
        </div>
        <div class="grid">
          <article class="card card--row m-nolift span-6">
            <h3>Menandai ujaran kebencian</h3>
            <p>Skor kebencian 0,8 atau lebih: teks menyerang kelompok karena suku, agama, ras atau identitas lain.</p>
            <div class="panel panel--flush">
              <div class="stage stage--fade-b"><div class="at-rise fit" style="--fit-max:420px;--ax:max(24px, 50% - 210px);--ay:40px;--ay-sm:20px">
                <div class="ui ui-panel vd" role="img" aria-label="Contoh: Semua pendatang dari suku itu pencuri, jangan kasih tempat. Skor kebencian 99,33 persen.">
                  <div class="vd__head"><span>Teks 1 dari 4</span><em class="st st--kuat">Indikasi kuat</em></div>
                  <p class="vd__teks">Semua pendatang dari suku itu pencuri, jangan kasih tempat</p>
                  <div class="vd__row vd__row--on"><span>Ujaran kebencian</span><b>99,33%</b><i style="--v:99.33%"></i></div>
                  <div class="vd__row"><span>Bahasa kasar</span><b>0,27%</b><i style="--v:0.27%"></i></div>
                  <p class="vd__ref">Kamus: pendatang, suku · Pasal 28 ayat (2) UU ITE</p>
                </div>
              </div></div>
            </div>
          </article>
          <article class="card card--row m-nolift span-6">
            <h3>Meminta tinjauan manusia</h3>
            <p>Skor kebencian 0,5 sampai di bawah 0,8: model belum yakin, jadi keputusan diserahkan ke moderator.</p>
            <div class="panel panel--flush">
              <div class="stage stage--fade-br"><div class="at-corner" style="--ax:32px;--ay:40px;--w:440px;width:440px">
                <div class="ui ui-panel vd" role="img" aria-label="Contoh: Kaum mereka selalu bikin masalah di kampung ini. Skor kebencian 65,21 persen.">
                  <div class="vd__head"><span>Teks 2 dari 4</span><em class="st st--tinjau">Perlu ditinjau</em></div>
                  <p class="vd__teks">Kaum mereka selalu bikin masalah di kampung ini</p>
                  <div class="vd__row vd__row--on"><span>Ujaran kebencian</span><b>65,21%</b><i style="--v:65.21%"></i></div>
                  <div class="vd__row"><span>Bahasa kasar</span><b>0,12%</b><i style="--v:0.12%"></i></div>
                  <p class="vd__ref">Kamus: kaum, mereka · menunggu moderator</p>
                </div>
              </div></div>
            </div>
          </article>
        </div>
      </div>
      <div class="grid">
        <article class="card card--row m-nolift span-6">
          <h3>Memisahkan bahasa kasar</h3>
          <p>Skor kasar 0,8 atau lebih tetapi kebencian di bawah 0,5: makian yang tidak menyerang identitas.</p>
          <div class="panel panel--flush">
            <div class="stage stage--fade-b"><div class="at-right to-edge" style="--ax:32px;--ay:48px;--w:400px;width:400px">
              <div class="ui ui-panel vd" role="img" aria-label="Contoh: Anjing, macet parah lagi pagi ini. Skor bahasa kasar 98,52 persen.">
                <div class="vd__head"><span>Teks 3 dari 4</span><em class="st st--kasar">Bahasa kasar</em></div>
                <p class="vd__teks">Anjing, macet parah lagi pagi ini</p>
                <div class="vd__row"><span>Ujaran kebencian</span><b>7,48%</b><i style="--v:7.48%"></i></div>
                <div class="vd__row vd__row--on"><span>Bahasa kasar</span><b>98,52%</b><i style="--v:98.52%"></i></div>
                <p class="vd__ref">Kamus: anjing · tanpa sasaran kelompok</p>
              </div>
            </div></div>
          </div>
        </article>
        <article class="card card--row m-nolift span-6">
          <h3>Meloloskan teks yang aman</h3>
          <p>Kedua skor di bawah ambang: teks tidak terindikasi ujaran kebencian maupun bahasa kasar.</p>
          <div class="panel panel--flush">
            <div class="stage stage--fade-b"><div class="at-rise fit" style="--fit-max:420px;--ax:max(24px, 50% - 210px);--ay:calc(100% - 196px);--ay-sm:24px">
              <div class="ui ui-panel vd" role="img" aria-label="Contoh: Terima kasih banyak atas bantuannya, semoga hari kalian menyenangkan. Skor kebencian 0,17 persen.">
                <div class="vd__head"><span>Teks 4 dari 4</span><em class="st st--aman">Tidak terindikasi</em></div>
                <p class="vd__teks">Terima kasih banyak atas bantuannya, semoga hari kalian menyenangkan!</p>
                <div class="vd__row vd__row--on"><span>Ujaran kebencian</span><b>0,17%</b><i style="--v:0.17%"></i></div>
                <div class="vd__row"><span>Bahasa kasar</span><b>0,30%</b><i style="--v:0.30%"></i></div>
                <p class="vd__ref">Kamus: tidak ada kata yang disorot</p>
              </div>
            </div></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- PERNYATAAN (liquid-type): satu kalimat besar dari Pasal 28 ayat (2) UU ITE -->
  <section class="section" id="pernyataan" aria-labelledby="pernyataan-h">
    <div class="wrap">
      <div data-moment="liquid-type" class="section-head" style="max-width:none">
        <h2 id="pernyataan-h" class="mo-lt__type">Hingga 6 tahun penjara</h2>
        <p class="t-lead measure">Pasal 28 ayat (2) UU ITE mengancam penyebar kebencian berdasarkan suku, agama, ras, jenis kelamin atau disabilitas, dengan denda hingga Rp1 miliar.</p>
        <p><a class="link" href="/dasar-hukum#pasal-28-2">Baca unsur Pasal 28 ayat (2)</a></p>
      </div>
    </div>
  </section>

  <!-- HUKUM (split): pasal utama sebagai baris, foto palu hakim di kanan -->
  <section class="section" id="hukum" style="--hm: 2" aria-labelledby="hukum-h">
    <div class="wrap">
      <div class="grid fill">
        <div class="span-5 stack-6">
          <h2 id="hukum-h">Pasal yang mengatur ujaran kebencian</h2>
          <p class="t-lead">Empat aturan yang paling sering dipakai untuk ujaran kebencian di ruang digital, dengan ancaman terberatnya.</p>
          <ul class="rows">
            <li><span class="rows__name">UU ITE Pasal 28 ayat (2)</span><span class="rows__value">6 tahun, Rp1 miliar</span></li>
            <li><span class="rows__name">UU No. 40 Tahun 2008 Pasal 16</span><span class="rows__value">5 tahun, Rp500 juta</span></li>
            <li><span class="rows__name">KUHP Pasal 243</span><span class="rows__value">4 tahun, Rp200 juta</span></li>
            <li><span class="rows__name">UU ITE Pasal 27A</span><span class="rows__value">2 tahun, Rp400 juta</span></li>
          </ul>
          <p><a class="link" href="/dasar-hukum">Baca dasar hukum</a></p>
        </div>
        <a class="frame frame--photo span-6 start-7" href="/dasar-hukum" aria-label="Baca dasar hukum lengkap">
          <img class="frame__img" src="<?= e(urlFoto('hukum')) ?>" alt="Palu hakim di atas meja sidang dengan tumpukan buku" loading="lazy" data-source="library" data-subject="palu hakim di ruang sidang">
        </a>
      </div>
    </div>
  </section>

  <!-- MODEL: diagram alur, satu masukan, satu model, tiga keluaran -->
  <section class="section" id="model" aria-labelledby="model-h">
    <div class="wrap">
      <div class="section-head">
        <h2 id="model-h">Model deteksi XLM-RoBERTa. <span class="muted">Dilatih ulang untuk ujaran kebencian dan bahasa kasar, 10 bahasa.</span></h2>
      </div>
      <div class="frame dg-frame">
        <div class="dg" data-diagram data-dg-still><script type="application/json">{"layout": "lanes", "alt": "Teks dari web Cek Hatespeech masuk ke model XLM-RoBERTa, yang mengeluarkan skor ujaran kebencian, skor bahasa kasar dan status.", "input": {"label": "Teks", "icon": "php", "sub": "2.000 karakter"}, "pulse": "none", "core": {"label": "XLM-RoBERTa", "icon": "hugging-face"}, "lanes": [{"label": "HATESPEECH", "icon": "ui:shield-alert", "done": true, "sub": "0,9933"}, {"label": "ABUSIVE", "icon": "ui:message-square", "done": true, "sub": "0,0027"}, {"label": "Status", "icon": "ui:check", "done": true, "sub": "Indikasi kuat"}]}</script></div>
      </div>
    </div>
  </section>

  <!-- FAQ (split): pertanyaan tentang hasil, jawaban terbuka -->
  <section class="section bg-band" id="faq" aria-labelledby="faq-h">
    <div class="wrap">
      <div class="grid grid--top">
        <div class="span-5 stack-4">
          <h2 id="faq-h">FAQ hasil deteksi</h2>
          <p class="muted">Yang paling sering ditanyakan saat mencoba Cek Hatespeech.</p>
          <p class="btn-row"><a class="btn btn--primary" href="#cek">Cek teks<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
        </div>
        <div class="faq span-6 start-7">
          <div class="faq__item"><h3>Apakah hasil deteksi selalu benar?</h3><p>Tidak. Kalimat kritik "Menurut saya kebijakan ini kurang tepat karena memberatkan rakyat kecil" mendapat skor 83,02% dan ditandai indikasi kuat.</p></div>
          <div class="faq__item"><h3>Kenapa skor kebencian dan kasar bisa sama-sama tinggi?</h3><p>Model menilai tiap label terpisah. "Kerja apaan sih ini, tolol banget hasilnya" mendapat 99,08% kebencian dan 99,85% kasar.</p></div>
          <div class="faq__item"><h3>Apakah teks saya disimpan saat deteksi?</h3><p>Aplikasi web tidak menyimpan teks. Teks dikirim ke server model untuk dinilai, lalu putusannya ditampilkan.</p></div>
          <div class="faq__item"><h3>Bahasa apa saja yang dibaca model deteksi?</h3><p>Indonesia, Melayu, Jawa, Sunda, Madura, Minangkabau, Banjar, Bugis, Aceh dan Inggris.</p></div>
          <div class="faq__item"><h3>Bolehkah hasil deteksi menjadi bukti hukum?</h3><p>Tidak. Hanya aparat penegak hukum dan pengadilan yang menilai apakah sebuah ujaran memenuhi unsur pasal.</p></div>
        </div>
      </div>
    </div>
  </section>

</main>
<?php
penutup(['diagram' => true, 'js' => $js]);
