<?php
/*
 * Kerangka bersama semua halaman Cek Hatespeech (tasteskill 2.5).
 * type: software · deal: hero centred, nav-centre, close-word, footer-dark, rhythm dark-band,
 * buttons arrow, mark dots, palette white-mint-tiles, type host, lit ground sand.
 */

/**
 * <head> dan pembuka <body>.
 * $o: judul, deskripsi, momen (array id momen yang dimuat), diagram (bool), css (CSS halaman).
 */
function kepala(array $o): void
{
    $judul = $o['judul'];
    $deskripsi = $o['deskripsi'];
    $momen = $o['momen'] ?? [];
    ?>
<!doctype html>
<html lang="id" data-type="software" data-radius="soft" data-buttons="arrow" data-motion>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($judul) ?></title>
<meta name="description" content="<?= e($deskripsi) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($judul) ?>">
<meta property="og:description" content="<?= e($deskripsi) ?>">
<meta property="og:image" content="https://cek-hatespeech.vercel.app/share.jpg">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<meta name="theme-color" content="#a8101a">
<link rel="canonical" href="https://cek-hatespeech.vercel.app<?= e($o['kanonik'] ?? '/') ?>">
<meta name="site-kind" content="software">
<meta name="page-type" content="software">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Host+Grotesk:wght@400..700&family=JetBrains+Mono:wght@400..500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/system.css">
<link rel="stylesheet" href="/assets/ui/cards.css">
<link rel="stylesheet" href="/assets/motion/motion.css">
<link rel="stylesheet" href="/assets/backgrounds/backgrounds.css">
<link rel="stylesheet" href="/assets/layout/layout.css">
<link rel="stylesheet" href="/assets/icons/icon.css">
<style>
:root {
  /* merah putih: merah kampus untuk tindakan dan pita, putih untuk dasar */
  --p-bg: #ffffff; --p-surface: #f7f3f2; --p-ink: #1d1a1a; --p-muted: #575151;
  --p-accent: #fbe5e5; --p-accent-ink: #8c0d15; --p-action: #b0121c; --p-action-ink: #ffffff;
  --p-inv-bg: #a8101a; --p-inv-surface: #b8202a; --p-inv-ink: #ffffff; --p-inv-muted: #f6d2d4;
  --p-inv-accent: #ffffff; --p-inv-accent-ink: #a8101a; --p-inv-action: #ffffff; --p-inv-action-ink: #a8101a;
  --font-display: "Host Grotesk", system-ui, sans-serif;
  --font-text: "Host Grotesk", system-ui, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
  --display-weight: 500;
  --display-tracking: -0.035em;
  --display-tracking-xl: -0.04em;
}
</style>
<link rel="stylesheet" href="/assets/ui/base.css">
<style>
/* nama penutup selebar wadah; teks pendamping cukup kontras (7:1) di dasar terang */
footer.closing .close__word { font-size: min(12.6vw, 186px); line-height: 1.1; padding-block: .04em; white-space: nowrap; overflow: visible; }
footer.closing > .wrap > * { min-width: 0; }
html .hero .hero__lead { color: #4a4545 !important; }
/* halaman dokumen (dasar hukum, tentang): tinggi mengikuti isi, tidak dikunci satu layar */
main > section.doc-sec { height: auto; min-height: 0; }
.doc-sec { padding: var(--padt) 0 var(--padb); }
.doc-sec.ground-surface { background: var(--g-alt); }
html main section:not(.ground-inverse) :is(p.muted, .t-lead) { color: #4a4545; }
.mark[data-mark] { background: var(--p-action); }
.ground-inverse .mark[data-mark] { background: #fff; }
</style>
<?php if (!empty($o['diagram'])): ?>
<link rel="stylesheet" href="/assets/diagrams/diagram.css">
<?php endif; ?>
<script>document.documentElement.classList.add('m-enter');setTimeout(()=>document.documentElement.classList.add('m-enter-done'),700)</script>
<script src="/assets/backgrounds/backgrounds.js"></script>
<?php if ($momen): ?>
<script>document.documentElement.classList.add('mo-js');setTimeout(()=>document.documentElement.classList.add('mo-safe'),2500)</script>
<link rel="modulepreload" href="/assets/moments/moments.js">
<link rel="modulepreload" href="/assets/moments/lib.js">
<?php foreach ($momen as $id): ?>
<link rel="modulepreload" href="/assets/moments/<?= e($id) ?>/moment.js">
<?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($o['css'])): ?>
<style>
<?= $o['css'] ?>
</style>
<?php endif; ?>
</head>
<body>
<?php
}

/** Bar navigasi tetap (nav-centre): Beranda, Tentang, Lapor. $aktif: 'beranda' | 'tentang' | 'lapor' | '' */
function navigasi(string $aktif = ''): void
{
    $cur = fn(string $h) => $aktif === $h ? ' aria-current="page"' : '';
    ?>
<header class="nav nav--centre">
  <div class="wrap nav__bar">
    <a class="skip-link" href="#top">Lewati ke isi</a>
    <a class="nav__logo" href="/"><span class="mark" data-mark="dots" aria-hidden="true"></span>Cek Hatespeech</a>
    <nav class="nav__links" aria-label="Utama">
      <div class="nav__item"><a class="nav__link" href="/"<?= $cur('beranda') ?>>Beranda</a></div>
      <div class="nav__item"><a class="nav__link" href="/tentang"<?= $cur('tentang') ?>>Tentang</a></div>
      <div class="nav__item"><a class="nav__link" href="/lapor"<?= $cur('lapor') ?>>Lapor</a></div>
    </nav>
    <div class="nav__actions">
      <a class="btn btn--primary" href="/lapor">Buat laporan</a>
    </div>
    <button class="nav__toggle" type="button" aria-expanded="false" aria-label="Menu">
      <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path d="M2 5h14M2 13h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
    </button>
  </div>
</header>
<?php
}

/** Penutup (close-word) + footer gelap dalam satu layar terakhir, lalu skrip kit. */
function penutup(array $o = []): void
{
    ?>
<footer class="footer closing footer--dark ground-inverse" id="contact" aria-labelledby="close-h">
  <div class="wrap">
    <div class="closing__cta close--word">
      <div class="section-head--split">
        <div><h2 id="close-h">Buat laporan perundungan</h2><p class="t-lead">PDF siap diserahkan ke <?= e(KAMPUS['tujuan']) ?>. Nama dan NIM tidak dikirim ke server.</p></div>
        <a class="btn btn--primary" href="/lapor">Buat laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a>
      </div>
      <p class="close__word" aria-hidden="true">Cek Hatespeech</p>
    </div>
    <div class="closing__links">
      <div class="grid">
        <div class="span-4 stack-4">
          <a class="nav__logo" href="/"><span class="mark" data-mark="dots" aria-hidden="true"></span>Cek Hatespeech</a>
          <p class="footer__lede">Alat bantu melapor perundungan untuk mahasiswa Telkom University Purwokerto. Tugas akhir Teknik Informatika, bukan layanan resmi kampus.</p>
        </div>
        <div class="span-2 start-7 footer__col"><h3>Aplikasi</h3><ul><li><a href="/">Beranda</a></li><li><a href="/tentang">Tentang</a></li><li><a href="/lapor">Buat laporan</a></li></ul></div>
        <div class="span-2 footer__col"><h3>Panduan</h3><ul><li><a href="/#tanda">Tanda perundungan</a></li><li><a href="/#serahkan">Ke mana melapor</a></li><li><a href="/dasar-hukum">Dasar hukum</a></li></ul></div>
        <div class="span-2 footer__col"><h3>Sumber</h3><ul><li><a href="https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1" rel="noopener">Model di Hugging Face</a></li><li><a href="https://github.com/ArvanMurbiyanto10/cek-hatespeech" rel="noopener">Kode di GitHub</a></li></ul></div>
      </div>
      <div class="footer__legal">
        <span>&copy; <?= e(PENGEMBANG['tahun']) ?> <?= e(PENGEMBANG['nama']) ?></span>
        <div class="socials"></div>
        <p class="credits">Foto via rawpixel, StockSnap dan Wikimedia Commons. <a href="/kredit">Kredit foto</a></p>
      </div>
    </div>
  </div>
</footer>

<script type="module" src="/assets/system.js"></script>
<script type="module" src="/assets/motion/motion.js"></script>
<script type="module" src="/assets/layout/layout.js"></script>
<?php if (!empty($o['diagram'])): ?>
<script type="module" src="/assets/diagrams/mount.js"></script>
<?php endif; ?>
<?php if (!empty($o['js'])): ?>
<script>
<?= $o['js'] ?>
</script>
<?php endif; ?>
</body>
</html>
<?php
}
