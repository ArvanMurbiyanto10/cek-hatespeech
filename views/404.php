<?php
/* Halaman 404: alamat yang tidak dikenal router. */
kepala([
    'judul'     => 'Halaman tidak ditemukan | Cek Hatespeech',
    'deskripsi' => 'Alamat yang Anda buka tidak ada di Cek Hatespeech.',
    'css'       => '.doc-404 { min-height: 70svh; padding: calc(var(--nav) + var(--s-9)) 0 var(--s-9); } .doc-404 h1 { font-size: clamp(38px, 4.4vw, 64px); max-width: 16ch; } .doc-404 p { margin-top: var(--s-5); font-size: 18px; max-width: 52ch; }',
    'kanonik'   => '/',
]);
navigasi();
?>
<main id="top">
  <section class="doc-404" aria-labelledby="h404">
    <div class="wrap">
      <h1 id="h404">Halaman ini tidak ada di Cek Hatespeech</h1>
      <p>Alamatnya mungkin salah ketik atau sudah dipindah. Cek teks dari halaman utama, atau buka dasar hukum dan halaman tentang lewat menu di atas.</p>
      <p class="btn-row" style="margin-top: var(--s-6)"><a class="btn btn--primary" href="/#cek">Cek teks<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></a></p>
    </div>
  </section>
</main>
<?php
penutup();
