<?php
/*
 * Halaman Cek teks: satu alur sederhana.
 * 1. Tempel teks  2. Tekan Cek teks  3. Baca putusan lalu Unduh PDF (dibuat di browser dengan jsPDF).
 */

$contoh = [
    'Netral'    => 'Terima kasih banyak atas bantuannya, semoga hari kalian menyenangkan!',
    'Kasar'     => 'Anjing, macet parah lagi pagi ini',
    'Kebencian' => 'Semua pendatang dari suku itu pencuri, jangan kasih tempat',
];

// Data untuk PDF, disusun di server agar isinya sama persis dengan yang tampil di layar.
$dataPdf = null;
if ($hasil !== null) {
    $dataPdf = [
        'teks'       => $teks,
        'judul'      => $infoLabel[$hasil['label']]['judul'],
        'label'      => $hasil['label'],
        'penjelasan' => $hasil['penjelasan'],
        'hs'         => persen($hasil['hs']),
        'ab'         => persen($hasil['ab']),
        'hsAngka'    => $hasil['hs'],
        'abAngka'    => $hasil['ab'],
        'target'     => $hasil['target'],
        'kategori'   => $hasil['kategori'] ? implode(', ', $hasil['kategori']) : '-',
        'tingkat'    => $hasil['tingkat'],
        'temuan'     => array_map(fn($t) => $t['kata'] . ' (' . ($namaTipe[$t['tipe']] ?? $t['tipe']) . ')', $hasil['temuan']),
        'model'      => 'nahiar/hatespeech-abusive-xlm-roberta-v1',
    ];
}

$css = <<<'CSS'
.cek-page { padding-top: calc(var(--nav) + var(--s-8)); }
.cek-page h1 { font-size: clamp(36px, 4.2vw, 60px); max-width: 18ch; }
.cek-page .t-lead { margin-top: var(--s-4); max-width: 56ch; }
.cek-alur { display: flex; flex-wrap: wrap; gap: var(--s-3) var(--s-6); margin: var(--s-6) 0 0; padding: 0; list-style: none; counter-reset: alur; }
.cek-alur li { display: flex; align-items: center; gap: 10px; font: 500 15px/1.3 var(--font-text); color: var(--ink); counter-increment: alur; }
.cek-alur li::before { content: counter(alur); width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; background: var(--p-accent); color: var(--p-accent-ink); font-size: 13px; }
.cek-aksi { margin-top: 4px; }
.hw__sp { flex: 1; }
/* ---- jendela aplikasi (dipindah dari beranda) ---- */
.hw__dots { display: flex; gap: 6px; } .hw__dots i { width: 10px; height: 10px; border-radius: 50%; background: #dcdfe2; }
.st { display: inline-flex; align-items: center; gap: 6px; height: 22px; padding: 0 8px; border-radius: 99px; font: 500 12px/1 var(--font-text); white-space: nowrap; background: #efefef; color: #3c3f43; }
.st::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.st--kuat { background: #fbe6e3; color: #a3271b; } .st--tinjau { background: #fdf0dc; color: #7d4a00; }
.st--kasar { background: #e9eef5; color: #2e4462; } .st--aman { background: #e1f5e8; color: #17603b; }
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
  .cek-page .app > .app__pane, .cek-page .cek-out { overflow: visible; min-height: auto; }
}
@media (max-width: 639px) {
  .cek-panduan li { grid-template-columns: minmax(0, 1fr); gap: 2px; }
  .cek-fakta { grid-template-columns: minmax(0, 1fr); }
  .cek-form, .cek-out { padding: 16px; }
}

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

/* Unduh PDF: laporan A4 dibuat di browser (jsPDF), teks tidak disimpan di server. */
(function () {
  var tombol = document.getElementById('tombol-pdf'), el = document.getElementById('data-pdf');
  if (!tombol || !el) return;
  var d = JSON.parse(el.textContent);
  // Huruf standar PDF hanya memuat Latin-1: emoji dibuang, karakter lain di luar Latin-1 diganti tanda tanya.
  function aman(s) {
    return String(s == null || s === '' ? '-' : s)
      .replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '')
      .replace(/[‘’]/g, "'").replace(/[“”]/g, '"').replace(/[–—]/g, '-')
      .replace(/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/g, '?')
      .replace(/ +$/gm, '');
  }
  var warna = { hate: [163, 39, 27], tinjau: [138, 83, 0], abusive: [46, 68, 98], aman: [23, 96, 59] };
  var latar = { hate: [251, 233, 230], tinjau: [253, 242, 224], abusive: [235, 240, 246], aman: [228, 246, 234] };

  tombol.addEventListener('click', function () {
    if (!window.jspdf) { alert('Pembuat PDF belum termuat. Periksa koneksi internet, lalu coba lagi.'); return; }
    var doc = new window.jspdf.jsPDF({ unit: 'mm', format: 'a4' });
    var L = 20, W = 170, y = 22, kini = new Date();
    var tanggal = kini.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + ', pukul ' + kini.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    function tulis(s, ukuran, gaya, rgb, jarak) {
      doc.setFont('helvetica', gaya || 'normal'); doc.setFontSize(ukuran); doc.setTextColor.apply(doc, rgb || [35, 35, 35]);
      doc.splitTextToSize(aman(s), W).forEach(function (b) {
        if (y > 280) { doc.addPage(); y = 20; }
        doc.text(b, L, y); y += ukuran * 0.42 + (jarak || 1.6);
      });
    }
    function garis() { doc.setDrawColor(225, 225, 225); doc.line(L, y, L + W, y); y += 7; }

    tulis('Laporan Cek Hatespeech', 20, 'bold');
    tulis('Dibuat ' + tanggal + '  |  cek-hatespeech.vercel.app', 9.5, 'normal', [104, 104, 104]);
    y += 3; garis();

    // kotak putusan berwarna sesuai status
    var c = warna[d.label] || warna.aman, bg = latar[d.label] || latar.aman;
    var isi = d.penjelasan ? doc.splitTextToSize(aman(d.penjelasan), W - 12) : [];
    var tinggi = 14 + isi.length * 5;
    doc.setFillColor.apply(doc, bg); doc.roundedRect(L, y, W, tinggi, 3, 3, 'F');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(14); doc.setTextColor.apply(doc, c); doc.text(aman(d.judul), L + 6, y + 9);
    if (isi.length) { doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); doc.setTextColor(35, 35, 35); doc.text(isi, L + 6, y + 15.5); }
    y += tinggi + 9;

    function skor(nama, nilai, angka) {
      doc.setFont('helvetica', 'normal'); doc.setFontSize(11); doc.setTextColor(35, 35, 35); doc.text(nama, L, y);
      doc.setFont('helvetica', 'bold'); doc.text(aman(nilai), L + W, y, { align: 'right' });
      y += 3; doc.setFillColor(236, 236, 236); doc.roundedRect(L, y, W, 2.6, 1.3, 1.3, 'F');
      doc.setFillColor(35, 35, 35); if (angka > 0.004) doc.roundedRect(L, y, Math.max(2.6, W * angka), 2.6, 1.3, 1.3, 'F');
      y += 10;
    }
    tulis('Skor model', 12, 'bold'); y += 2;
    skor('Ujaran kebencian (HATESPEECH)', d.hs, d.hsAngka);
    skor('Bahasa kasar (ABUSIVE)', d.ab, d.abAngka);

    tulis('Rincian', 12, 'bold'); y += 1;
    [['Sasaran (kamus)', d.target], ['Kategori (kamus)', d.kategori], ['Tingkat', d.tingkat], ['Model', d.model]].forEach(function (r) {
      doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); doc.setTextColor(104, 104, 104); doc.text(r[0], L, y);
      var v = doc.splitTextToSize(aman(r[1]), W - 55); doc.setTextColor(35, 35, 35); doc.text(v, L + 55, y); y += 2 + v.length * 5;
    });
    y += 4;

    tulis('Teks yang dicek', 12, 'bold'); y += 1;
    tulis(d.teks, 11, 'normal', [35, 35, 35], 2);
    y += 3;
    if (d.temuan && d.temuan.length) { tulis('Kata yang disorot kamus', 12, 'bold'); y += 1; tulis(d.temuan.join(', '), 10.5); y += 3; }

    garis();
    tulis('Catatan: hasil ini dibuat oleh model AI dan kamus bantu, bukan putusan hukum. Hanya aparat penegak hukum dan pengadilan yang menilai apakah sebuah ujaran memenuhi unsur pasal.', 9, 'normal', [104, 104, 104]);

    var p = function (x) { return String(x).padStart(2, '0'); };
    doc.save('cek-hatespeech-' + kini.getFullYear() + p(kini.getMonth() + 1) + p(kini.getDate()) + '-' + p(kini.getHours()) + p(kini.getMinutes()) + '.pdf');
  });
})();
JS;

kepala([
    'judul'     => 'Cek teks | Cek Hatespeech',
    'deskripsi' => 'Tempel teks berbahasa Indonesia, cek skor ujaran kebencian dan bahasa kasar, lalu unduh hasilnya sebagai PDF.',
    'css'       => $css,
    'kanonik'   => '/cek',
]);
navigasi('cek');
?>
<main id="top">
  <section class="doc-sec cek-page" aria-labelledby="cek-h">
    <div class="wrap">
      <h1 id="cek-h">Cek ujaran kebencian di teks Anda</h1>
      <p class="t-lead">Model XLM-RoBERTa menilai skor ujaran kebencian dan bahasa kasar secara terpisah. Hasilnya bisa disimpan sebagai PDF.</p>
      <ol class="cek-alur">
        <li>Tempel teks</li>
        <li>Tekan Cek teks</li>
        <li>Unduh PDF hasilnya</li>
      </ol>
      <div class="ui ui-app app">
        <div class="app__pane app__pane--list">
          <div class="cek-bar"><span class="hw__dots"><i></i><i></i><i></i></span><b>Teks</b><span class="hw__sp"></span><span>Maks. 2.000 karakter</span></div>
          <form class="cek-form" id="form-cek" method="post" action="/cek#hasil-cek" autocomplete="off">
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
              <div class="btn-row cek-aksi">
                <button class="btn btn--primary" type="button" id="tombol-pdf">Unduh PDF<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></button>
                <a class="btn btn--secondary" href="/cek">Cek teks lain</a>
              </div>
            <?php elseif ($galat === ''): ?>
              <p class="cek-label">Status yang bisa muncul</p>
              <ul class="cek-panduan">
                <li><em class="st st--kuat">Indikasi kuat</em><span>Skor ujaran kebencian 0,8 atau lebih.</span></li>
                <li><em class="st st--tinjau">Perlu ditinjau</em><span>Skor ujaran kebencian 0,5 sampai di bawah 0,8.</span></li>
                <li><em class="st st--kasar">Bahasa kasar</em><span>Skor bahasa kasar 0,8 atau lebih, kebencian di bawah 0,5.</span></li>
                <li><em class="st st--aman">Tidak terindikasi</em><span>Kedua skor di bawah ambang batas.</span></li>
              </ul>
              <p class="cek-note">Putusan muncul di panel ini dalam beberapa detik setelah Anda menekan Cek teks, lalu bisa diunduh sebagai PDF.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php if ($dataPdf !== null): ?>
<script type="application/json" id="data-pdf"><?= json_encode($dataPdf, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
<?php endif; ?>
<?php
penutup(['js' => $js]);
