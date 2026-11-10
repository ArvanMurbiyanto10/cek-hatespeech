<?php
/* Halaman kredit foto: sumber dan lisensi setiap gambar di Cek Hatespeech. */
$kredit = [
    ['Perempuan memakai media sosial (hero.jpg)', 'rawpixel, CC0', 'https://www.rawpixel.com/image/5921379/photo-image-public-domain-student-woman'],
    ['Penggunaan media digital (tentang.jpg)', 'Rawpixel.com via Wikimedia Commons, CC0', 'https://commons.wikimedia.org/w/index.php?curid=78993089'],
    ['People Woman (hate-speech.jpg)', 'Siddharth Bhogra via StockSnap, CC0', 'https://stocksnap.io/photo/people-woman-NXWB8SXF45'],
    ['Palu hakim di ruang sidang (hukum.jpg)', 'Joe Gratz via Wikimedia Commons, CC0', 'https://commons.wikimedia.org/w/index.php?curid=91844335'],
    ['Logo Telkom University', 'Hilfans via Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Logo_Telkom_University_potrait.png'],
];
kepala([
    'judul'     => 'Kredit foto | Cek Hatespeech',
    'deskripsi' => 'Sumber dan lisensi foto yang dipakai di Cek Hatespeech.',
    'css'       => '.doc-kredit { min-height: 70svh; padding: calc(var(--nav) + var(--s-9)) 0 var(--s-9); } .doc-kredit h1 { font-size: clamp(38px, 4.4vw, 64px); max-width: 18ch; } .doc-kredit .rows { margin-top: var(--s-7); max-width: 880px; } .doc-kredit .rows a { text-decoration: underline; text-underline-offset: 3px; }',
    'kanonik'   => '/kredit',
]);
navigasi();
?>
<main id="top">
  <section class="doc-kredit" aria-labelledby="kredit-h">
    <div class="wrap">
      <h1 id="kredit-h">Kredit foto Cek Hatespeech</h1>
      <ul class="rows">
        <?php foreach ($kredit as [$isi, $sumber, $url]): ?>
          <li><span class="rows__name"><?= e($isi) ?></span><span class="rows__value"><a href="<?= e($url) ?>" rel="noopener"><?= e($sumber) ?></a></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</main>
<?php
penutup();
