<?php
/*
 * Halaman Lapor: formulir laporan dugaan perundungan untuk mahasiswa Telkom University Purwokerto.
 * Privasi: data diri, kronologi dan tangkapan layar tidak pernah dikirim ke server.
 * Hanya teks bukti yang dikirim ke /periksa untuk dinilai model AI. PDF dibuat di browser (jsPDF).
 */

$konfig = [
    'tujuan'        => KAMPUS['tujuan'],
    'tujuanLengkap' => KAMPUS['tujuan_lengkap'],
    'kampus'        => KAMPUS['nama'],
    'maks'          => MAKS_KARAKTER,
];

$tempat = ['Grup WhatsApp', 'Chat pribadi (WhatsApp, Line, Telegram)', 'Instagram', 'TikTok', 'X (Twitter)', 'Discord', 'Di kampus secara langsung', 'Lainnya'];
$bentuk = ['Hinaan atau ejekan', 'Ujaran kebencian (suku, agama, ras)', 'Ancaman', 'Pengucilan', 'Foto atau data pribadi disebar', 'Kekerasan fisik', 'Lainnya'];

$css = <<<'CSS'
.lapor { padding-top: calc(var(--nav) + var(--s-8)); }
.lapor__head { max-width: 760px; margin-bottom: var(--s-7); }
.lapor__head h1 { font-size: clamp(34px, 4vw, 56px); }
.lapor__head p { margin-top: var(--s-4); font-size: 18px; line-height: 1.6; color: #4a4545; }
.lapor__grid { display: grid; grid-template-columns: minmax(0, 8fr) minmax(0, 4fr); gap: var(--gutter); align-items: start; }

/* bagian formulir */
.bag { background: #fff; border: 1px solid #ece6e5; border-radius: 18px; padding: 28px; margin: 0 0 var(--s-5); }
.bag legend { float: left; width: 100%; padding: 0; margin: 0 0 6px; display: flex; align-items: center; gap: 12px; font: 500 22px/1.25 var(--font-display); letter-spacing: -0.01em; color: var(--ink); }
.bag legend span { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; flex: none; background: var(--p-action); color: #fff; font: 600 15px/1 var(--font-text); }
.bag__info { clear: both; margin: 0 0 20px 46px; font-size: 15px; line-height: 1.55; color: #4a4545; }
.isian { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 20px; }
.isian .penuh { grid-column: 1 / -1; }
.f { display: grid; gap: 6px; align-content: start; }
.f > label, .f > .f__label { font: 500 15px/1.35 var(--font-text); color: var(--ink); }
.f > label em, .f__label em { font-style: normal; color: #a8101a; }
.f small { font-size: 13px; color: #5c5656; line-height: 1.45; }
.f input[type=text], .f input[type=date], .f input[type=number], .f select, .f textarea {
  width: 100%; min-height: 48px; padding: 11px 14px; border: 1px solid #d9d1d0; border-radius: 10px; background: #fff;
  font: 400 16px/1.45 var(--font-text); color: var(--ink); }
.f textarea { resize: vertical; min-height: 130px; }
.f :is(input, select, textarea):focus-visible { outline: 2px solid var(--p-action); outline-offset: 1px; border-color: var(--p-action); }
.f :is(input, select, textarea).salah { border-color: #a8101a; background: #fff8f8; }
.pilih { display: flex; flex-wrap: wrap; gap: 10px; }
.pilih label { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 8px 14px; border: 1px solid #d9d1d0; border-radius: 10px; font-size: 15px; cursor: pointer; background: #fff; }
.pilih input { accent-color: #a8101a; width: 18px; height: 18px; margin: 0; }
.pilih label:has(input:checked) { border-color: #a8101a; background: #fdf3f3; }
.hitung { justify-self: end; font-size: 13px; color: #5c5656; }

/* hasil pemeriksaan AI */
#tombol-ai { min-height: 46px; padding: 0 18px; border: 1.5px solid var(--p-action); border-radius: 10px; color: var(--p-action); background: #fff; text-decoration: none; }
#tombol-ai:hover { background: #fdf3f3; }
#tombol-ai:disabled { opacity: .6; cursor: progress; }
.periksa-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 4px; }
.ai { margin-top: 16px; border-radius: 14px; border: 1px solid #ece6e5; overflow: hidden; }
.ai[hidden] { display: none; }
.ai__vonis { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 14px; align-items: start; padding: 16px 18px; background: #f4f4f4; }
.ai__ikon { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; color: #fff; font: 600 18px/1 var(--font-text); background: #232323; }
.ai--hate .ai__vonis { background: #fbe9e6; } .ai--hate .ai__ikon { background: #a3271b; }
.ai--tinjau .ai__vonis { background: #fdf2e0; } .ai--tinjau .ai__ikon { background: #8a5300; }
.ai--abusive .ai__vonis { background: #ebf0f6; } .ai--abusive .ai__ikon { background: #2e4462; }
.ai--aman .ai__vonis { background: #e4f6ea; } .ai--aman .ai__ikon { background: #17603b; }
.ai__judul { margin: 0; font: 500 19px/1.3 var(--font-text); }
.ai__sub { margin: 4px 0 0; font-size: 15px; line-height: 1.5; color: #2c2727; }
.ai__isi { padding: 16px 18px; display: grid; gap: 12px; }
.skor div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; font-size: 15px; }
.skor div + div { margin-top: 10px; }
.skor b { font-variant-numeric: tabular-nums; }
.skor i { grid-column: 1 / -1; height: 7px; border-radius: 9px; background: #ececec; overflow: hidden; }
.skor i::after { content: ""; display: block; height: 100%; width: var(--v); background: #1d1a1a; border-radius: inherit; }
.ai__teks { margin: 0; padding: 12px 14px; border-radius: 10px; background: #f8f5f4; font-size: 15px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
.ai__teks mark { padding: 1px 3px; border-radius: 4px; font-weight: 500; }
.hl-hate { background: #fbe6e3; color: #8f2016; box-shadow: inset 0 -2px 0 #a3271b; }
.hl-threat { background: #a3271b; color: #fff; }
.hl-abuse { background: #fdf0dc; color: #6e4100; box-shadow: inset 0 -2px 0 #8a5300; }
.hl-target { background: #e9eef5; color: #233a58; }
.ai__catatan { margin: 0; font-size: 13px; color: #5c5656; }
.pesan { margin: 14px 0 0; padding: 12px 14px; border-radius: 10px; font-size: 15px; line-height: 1.5; }
.pesan--galat { background: #fbe9e6; color: #8f2016; }
.pesan--basi { background: #fdf2e0; color: #6e4100; }
.pesan[hidden] { display: none; }

/* tangkapan layar */
.unggah { display: grid; place-items: center; gap: 4px; min-height: 120px; padding: 18px; border: 1.5px dashed #d2c6c4; border-radius: 12px; background: #fcfaf9; text-align: center; cursor: pointer; font-size: 15px; }
.unggah:hover, .unggah:focus-within { border-color: #a8101a; background: #fdf6f6; }
.unggah b { color: #a8101a; }
.unggah input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.thumbs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
.thumbs:empty { display: none; }
.thumb { position: relative; aspect-ratio: 3 / 4; border-radius: 10px; overflow: hidden; border: 1px solid #ece6e5; background: #f4f0ef; }
.thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb button { position: absolute; top: 6px; right: 6px; min-height: 32px; padding: 0 10px; border: 0; border-radius: 99px; background: rgb(29 26 26 / .82); color: #fff; font: 500 13px/1 var(--font-text); cursor: pointer; }

/* pernyataan */
.setuju { display: flex; gap: 12px; align-items: flex-start; font-size: 16px; line-height: 1.55; cursor: pointer; }
.setuju input { accent-color: #a8101a; width: 20px; height: 20px; margin-top: 2px; flex: none; }

/* panel samping */
.ringkas { position: sticky; top: calc(var(--nav) + 20px); background: #fff; border: 1px solid #ece6e5; border-radius: 18px; overflow: hidden; }
.ringkas__head { padding: 18px 22px; background: #a8101a; color: #fff; }
.ringkas__head b { display: block; font: 500 18px/1.3 var(--font-text); }
.ringkas__head small { font-size: 14px; color: #f6d2d4; }
.ringkas ol { list-style: none; margin: 0; padding: 10px 22px; }
.ringkas li { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 11px 0; border-bottom: 1px solid #f0eaea; font-size: 15px; }
.ringkas li:last-child { border-bottom: 0; }
.ringkas li span:last-child { font-size: 13px; padding: 3px 9px; border-radius: 99px; background: #f4f0ef; color: #5c5656; white-space: nowrap; }
.ringkas li.ok span:last-child { background: #e4f6ea; color: #17603b; }
.ringkas__aksi { padding: 6px 22px 22px; display: grid; gap: 10px; }
.ringkas__aksi .btn { width: 100%; justify-content: center; }
.ringkas__aksi p { margin: 0; font-size: 13px; line-height: 1.5; color: #5c5656; }
.salah-list { margin: 0 22px 14px; padding: 12px 14px 12px 30px; border-radius: 10px; background: #fbe9e6; color: #8f2016; font-size: 14px; line-height: 1.5; }
.salah-list[hidden] { display: none; }

@media (max-width: 1023px) { .lapor__grid { grid-template-columns: minmax(0, 1fr); } .ringkas { position: static; } }
@media (max-width: 639px) { .bag { padding: 20px 16px; } .bag__info { margin-left: 0; } .isian { grid-template-columns: minmax(0, 1fr); } .thumbs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
CSS;

$js = <<<'JS'
(function () {
  var K = JSON.parse(document.getElementById('konfig').textContent);
  var form = document.getElementById('form-lapor');
  var $ = function (id) { return document.getElementById(id); };
  var hasilAI = null, teksDiperiksa = '', gambar = [], kotor = false;

  /* ---------- isian bantu ---------- */
  $('tanggal').max = new Date().toISOString().slice(0, 10);
  $('tempat').addEventListener('change', function () { $('f-tempat-lain').hidden = this.value !== 'Lainnya'; perbarui(); });
  function penghitung(id, out) { var t = $(id), o = $(out); function u() { o.textContent = t.value.length.toLocaleString('id-ID') + ' / ' + (+t.maxLength).toLocaleString('id-ID'); } t.addEventListener('input', u); u(); }
  penghitung('kronologi', 'hitung-kronologi'); penghitung('bukti', 'hitung-bukti');
  form.addEventListener('input', function () { kotor = true; perbarui(); });
  form.addEventListener('change', perbarui);
  window.addEventListener('beforeunload', function (e) { if (kotor) { e.preventDefault(); e.returnValue = ''; } });

  /* ---------- pemeriksaan AI (hanya teks bukti yang dikirim) ---------- */
  var tombolAI = $('tombol-ai');
  function tampilGalat(t) { var p = $('ai-galat'); p.textContent = t; p.hidden = !t; }
  $('bukti').addEventListener('input', function () { $('ai-basi').hidden = !(hasilAI && this.value.trim() !== teksDiperiksa); });

  function periksa() {
    var teks = $('bukti').value.trim();
    if (!teks) { tampilGalat('Tempel dulu teks bukti yang ingin diperiksa.'); $('bukti').focus(); return Promise.resolve(false); }
    tombolAI.disabled = true; tombolAI.firstChild.nodeValue = 'Memeriksa… ';
    tampilGalat('');
    return fetch('/periksa', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ teks: teks }) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.ok) { tampilGalat(d.galat || 'Pemeriksaan gagal.'); return false; }
        hasilAI = d.hasil; teksDiperiksa = teks; tampilHasil(d.hasil); $('ai-basi').hidden = true; perbarui(); return true;
      })
      .catch(function () { tampilGalat('Tidak bisa menghubungi server. Periksa koneksi internet, lalu coba lagi.'); return false; })
      .finally(function () { tombolAI.disabled = false; tombolAI.firstChild.nodeValue = hasilAI ? 'Periksa ulang ' : 'Periksa dengan AI '; });
  }
  tombolAI.addEventListener('click', periksa);

  function tampilHasil(h) {
    var ikon = { hate: '!', tinjau: '?', abusive: '!', aman: '✓' };
    var box = $('ai'); box.className = 'ai ai--' + h.label; box.hidden = false;
    $('ai-ikon').textContent = ikon[h.label] || '?';
    $('ai-judul').textContent = h.judul;
    $('ai-sub').textContent = h.penjelasan || '';
    $('ai-hs').textContent = h.hsPersen; $('ai-hs-bar').style.setProperty('--v', (h.hs * 100).toFixed(2) + '%');
    $('ai-ab').textContent = h.abPersen; $('ai-ab-bar').style.setProperty('--v', (h.ab * 100).toFixed(2) + '%');
    $('ai-teks').innerHTML = h.html; // HTML sudah di-escape di server
  }

  /* ---------- tangkapan layar: diperkecil di browser, tidak diunggah ---------- */
  $('gambar').addEventListener('change', function () {
    var files = Array.prototype.slice.call(this.files || []);
    this.value = '';
    files.forEach(function (f) {
      if (gambar.length >= 3) { alert('Maksimal 3 tangkapan layar.'); return; }
      if (!/^image\/(png|jpe?g|webp)$/.test(f.type)) { alert(f.name + ' bukan gambar PNG, JPG atau WebP.'); return; }
      var rd = new FileReader();
      rd.onload = function () {
        var img = new Image();
        img.onload = function () {
          var s = Math.min(1, 1400 / Math.max(img.width, img.height));
          var c = document.createElement('canvas'); c.width = Math.round(img.width * s); c.height = Math.round(img.height * s);
          var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(img, 0, 0, c.width, c.height);
          if (gambar.length < 3) { gambar.push({ data: c.toDataURL('image/jpeg', 0.85), w: c.width, h: c.height, nama: f.name }); gambarUlang(); perbarui(); }
        };
        img.src = rd.result;
      };
      rd.readAsDataURL(f);
    });
  });
  function gambarUlang() {
    var w = $('thumbs'); w.innerHTML = '';
    gambar.forEach(function (g, i) {
      var d = document.createElement('div'); d.className = 'thumb';
      var im = document.createElement('img'); im.src = g.data; im.alt = 'Tangkapan layar ' + (i + 1);
      var b = document.createElement('button'); b.type = 'button'; b.textContent = 'Hapus';
      b.addEventListener('click', function () { gambar.splice(i, 1); gambarUlang(); perbarui(); });
      d.appendChild(im); d.appendChild(b); w.appendChild(d);
    });
    $('gambar-info').textContent = gambar.length ? gambar.length + ' dari 3 terlampir' : 'PNG, JPG atau WebP, maksimal 3';
  }

  /* ---------- ringkasan dan validasi ---------- */
  function nilai(id) { return ($(id).value || '').trim(); }
  function bentukDipilih() { return Array.prototype.map.call(form.querySelectorAll('input[name=bentuk]:checked'), function (c) { return c.value; }); }
  function cekBagian() {
    return {
      diri: !!(nilai('nama') && /^\d{8,12}$/.test(nilai('nim')) && nilai('prodi') && nilai('kontak') && form.querySelector('input[name=peran]:checked')),
      kejadian: !!(nilai('tanggal') && nilai('tempat') && (nilai('tempat') !== 'Lainnya' || nilai('tempat-lain')) && bentukDipilih().length && nilai('kronologi')),
      bukti: !!(nilai('bukti') || gambar.length),
      ai: !!(hasilAI && nilai('bukti') === teksDiperiksa),
      setuju: $('setuju').checked
    };
  }
  function perbarui() {
    var c = cekBagian();
    ['diri', 'kejadian', 'bukti', 'ai', 'setuju'].forEach(function (k) {
      var li = $('r-' + k); li.classList.toggle('ok', c[k]);
      li.lastElementChild.textContent = c[k] ? 'Lengkap' : (k === 'ai' ? 'Belum diperiksa' : 'Belum');
    });
  }
  function daftarSalah() {
    var s = [], tandai = function (id, pesan) { $(id).classList.add('salah'); s.push([id, pesan]); };
    form.querySelectorAll('.salah').forEach(function (x) { x.classList.remove('salah'); });
    if (!nilai('nama')) tandai('nama', 'Isi nama lengkap.');
    if (!/^\d{8,12}$/.test(nilai('nim'))) tandai('nim', 'NIM berisi 8 sampai 12 angka.');
    if (!nilai('prodi')) tandai('prodi', 'Isi program studi.');
    if (!nilai('kontak')) tandai('kontak', 'Isi email atau nomor WhatsApp.');
    if (!form.querySelector('input[name=peran]:checked')) s.push(['peran-korban', 'Pilih kamu melapor sebagai korban atau saksi.']);
    if (!nilai('tanggal')) tandai('tanggal', 'Isi tanggal kejadian.');
    if (!nilai('tempat')) tandai('tempat', 'Pilih tempat atau aplikasi kejadian.');
    if (nilai('tempat') === 'Lainnya' && !nilai('tempat-lain')) tandai('tempat-lain', 'Tulis tempat kejadiannya.');
    if (!bentukDipilih().length) s.push(['bentuk-0', 'Pilih minimal satu bentuk perundungan.']);
    if (!nilai('kronologi')) tandai('kronologi', 'Ceritakan kronologi singkatnya.');
    if (!nilai('bukti') && !gambar.length) tandai('bukti', 'Tempel teks bukti atau lampirkan tangkapan layar.');
    if (!$('setuju').checked) s.push(['setuju', 'Centang pernyataan kebenaran laporan.']);
    return s;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var s = daftarSalah(), box = $('salah-list');
    if (s.length) {
      box.innerHTML = ''; s.forEach(function (x) { var li = document.createElement('li'); li.textContent = x[1]; box.appendChild(li); });
      box.hidden = false; $(s[0][0]).focus(); return;
    }
    box.hidden = true;
    var lanjut = Promise.resolve(true);
    if (nilai('bukti') && !(hasilAI && nilai('bukti') === teksDiperiksa)) {
      lanjut = periksa().then(function (ok) {
        return ok || confirm('Pemeriksaan AI belum berhasil (server model mungkin sedang mati). Buat PDF tanpa hasil AI?');
      });
    }
    lanjut.then(function (ok) { if (ok) buatPdf(); });
  });

  /* ---------- PDF laporan ---------- */
  function aman(s) {
    return String(s == null || s === '' ? '-' : s)
      .replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '')
      .replace(/[‘’]/g, "'").replace(/[“”]/g, '"').replace(/[–—]/g, '-').replace(/…/g, '...')
      .replace(/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/g, '?');
  }
  function buatPdf() {
    if (!window.jspdf) { alert('Pembuat PDF belum termuat. Periksa koneksi internet, lalu coba lagi.'); return; }
    var doc = new window.jspdf.jsPDF({ unit: 'mm', format: 'a4' });
    var L = 20, W = 170, y = 0, kini = new Date();
    var p2 = function (x) { return String(x).padStart(2, '0'); };
    var nomor = 'LP-' + kini.getFullYear() + p2(kini.getMonth() + 1) + p2(kini.getDate()) + '-' + p2(kini.getHours()) + p2(kini.getMinutes()) + '-' + Math.floor(1000 + Math.random() * 9000);
    var tglPanjang = function (d) { return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); };
    var tglKejadian = nilai('tanggal') ? tglPanjang(new Date(nilai('tanggal') + 'T00:00:00')) : '-';
    var MERAH = [168, 16, 26], TINTA = [29, 26, 26], ABU = [100, 94, 94];

    function warna(c) { doc.setTextColor(c[0], c[1], c[2]); }
    function cukup(h) { if (y + h > 272) { doc.addPage(); y = 20; } }
    function judul(t) { cukup(14); y += 4; doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); warna(MERAH); doc.text(aman(t).toUpperCase(), L, y); y += 2.5; doc.setDrawColor(236, 214, 215); doc.line(L, y, L + W, y); y += 6; }
    function baris(label, isi) {
      doc.setFontSize(10.5); var v = doc.splitTextToSize(aman(isi), W - 52); cukup(v.length * 5 + 2);
      doc.setFont('helvetica', 'normal'); warna(ABU); doc.text(aman(label), L, y);
      warna(TINTA); doc.text(v, L + 52, y); y += v.length * 5 + 1.6;
    }
    function paragraf(t, ukuran, c) {
      var u = ukuran || 10.5, lh = u * 0.45 + 1.4;
      doc.setFont('helvetica', 'normal'); doc.setFontSize(u); warna(c || TINTA);
      var b = doc.splitTextToSize(aman(t), W);
      if (b.length * lh < 45) cukup(b.length * lh + 2); // paragraf pendek tidak dipotong antarhalaman
      b.forEach(function (x) { cukup(6); doc.text(x, L, y); y += lh; });
    }
    function kutipan(t) {
      doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5);
      var b = doc.splitTextToSize(aman(t), W - 10);
      for (var i = 0; i < b.length; i += 40) {
        var pot = b.slice(i, i + 40), h = pot.length * 5 + 6; cukup(h);
        doc.setFillColor(249, 245, 244); doc.rect(L, y - 4, W, h, 'F'); doc.setFillColor(MERAH[0], MERAH[1], MERAH[2]); doc.rect(L, y - 4, 1.2, h, 'F');
        warna(TINTA); doc.text(pot, L + 5, y + 0.5); y += h + 2;
      }
    }

    // kop
    doc.setFillColor(MERAH[0], MERAH[1], MERAH[2]); doc.rect(0, 0, 210, 7, 'F');
    y = 22; doc.setFont('helvetica', 'bold'); doc.setFontSize(18); warna(TINTA); doc.text('LAPORAN DUGAAN PERUNDUNGAN', L, y);
    y += 7; doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(ABU);
    doc.text(aman('Kepada: ' + K.tujuan), L, y); y += 5;
    doc.text(aman('Nomor: ' + nomor + '     Dibuat: ' + tglPanjang(kini) + ', pukul ' + p2(kini.getHours()) + '.' + p2(kini.getMinutes())), L, y); y += 4;

    judul('1. Data pelapor');
    baris('Nama lengkap', nilai('nama'));
    baris('NIM', nilai('nim'));
    baris('Program studi', nilai('prodi') + (nilai('angkatan') ? ', angkatan ' + nilai('angkatan') : ''));
    baris('Kontak', nilai('kontak'));
    baris('Melapor sebagai', (form.querySelector('input[name=peran]:checked') || {}).value);

    judul('2. Kejadian');
    baris('Tanggal kejadian', tglKejadian);
    baris('Tempat atau aplikasi', nilai('tempat') === 'Lainnya' ? nilai('tempat-lain') : nilai('tempat'));
    baris('Frekuensi', nilai('frekuensi'));
    baris('Bentuk', bentukDipilih().join(', '));
    baris('Terduga pelaku', nilai('pelaku') || 'Tidak disebutkan');
    y += 1; doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); warna(TINTA); cukup(8); doc.text('Kronologi', L, y); y += 5.5;
    paragraf(nilai('kronologi'));

    judul('3. Bukti');
    if (nilai('bukti')) {
      doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); warna(TINTA); cukup(8); doc.text('Teks pesan atau komentar', L, y); y += 5.5;
      kutipan(nilai('bukti'));
      var h = hasilAI && nilai('bukti') === teksDiperiksa ? hasilAI : null;
      if (h) {
        var wr = { hate: [[251, 233, 230], [163, 39, 27]], tinjau: [[253, 242, 224], [125, 74, 0]], abusive: [[235, 240, 246], [46, 68, 98]], aman: [[228, 246, 234], [23, 96, 59]] }[h.label];
        cukup(40); y += 2;
        doc.setFillColor(wr[0][0], wr[0][1], wr[0][2]); doc.roundedRect(L, y, W, 12, 2, 2, 'F');
        doc.setFont('helvetica', 'bold'); doc.setFontSize(12); warna(wr[1]); doc.text(aman('Hasil pemeriksaan AI: ' + h.judul), L + 5, y + 7.8); y += 19;
        [['Skor ujaran kebencian (HATESPEECH)', h.hsPersen, h.hs], ['Skor bahasa kasar (ABUSIVE)', h.abPersen, h.ab]].forEach(function (r) {
          doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(TINTA); doc.text(r[0], L, y);
          doc.setFont('helvetica', 'bold'); doc.text(aman(r[1]), L + W, y, { align: 'right' }); y += 2.6;
          doc.setFillColor(236, 236, 236); doc.roundedRect(L, y, W, 2.4, 1.2, 1.2, 'F');
          if (r[2] > 0.004) { doc.setFillColor(TINTA[0], TINTA[1], TINTA[2]); doc.roundedRect(L, y, Math.max(2.4, W * r[2]), 2.4, 1.2, 1.2, 'F'); }
          y += 8;
        });
        if (h.temuan && h.temuan.length) baris('Kata yang disorot', h.temuan.map(function (t) { return t.kata + ' (' + t.tipe + ')'; }).join(', '));
        if (h.target && h.target !== '-') baris('Sasaran (kamus)', h.target);
        baris('Model', h.model);
      } else {
        paragraf('Pemeriksaan AI tidak tersedia saat laporan dibuat.', 9.5, ABU);
      }
    }
    if (gambar.length) { y += 1; baris('Tangkapan layar', gambar.length + ' gambar, lihat halaman Lampiran'); }

    cukup(80); // pernyataan dan tanda tangan selalu di halaman yang sama
    judul('4. Pernyataan');
    paragraf('Saya menyatakan bahwa informasi dalam laporan ini saya tulis dengan sebenar-benarnya dan saya bersedia dihubungi untuk keterangan lebih lanjut.');
    cukup(42); y += 6;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(TINTA);
    var tx = L + W - 62;
    doc.text(aman('Purwokerto, ' + tglPanjang(kini)), tx, y); y += 5; doc.text('Pelapor,', tx, y); y += 24;
    doc.text(aman('( ' + nilai('nama') + ' )'), tx, y); y += 5; warna(ABU); doc.text(aman('NIM ' + nilai('nim')), tx, y); y += 10;

    judul('Langkah berikutnya');
    paragraf('Serahkan dokumen ini ke ' + K.tujuan + ' bersama bukti aslinya (percakapan, tautan atau tangkapan layar). Jangan hapus percakapan asli. Dalam keadaan darurat, hubungi 112.', 10);

    // lampiran: tangkapan layar di halaman tersendiri, dua kolom
    if (gambar.length) {
      doc.addPage(); y = 20; judul('Lampiran: tangkapan layar (' + gambar.length + ')');
      var lebar = 82, x = L, tinggiBaris = 0;
      gambar.forEach(function (g, i) {
        var hgt = Math.min(lebar * g.h / g.w, 118), wid = hgt * g.w / g.h;
        if (i % 2 === 0) { if (i) y += tinggiBaris + 12; tinggiBaris = 0; cukup(hgt + 10); x = L; }
        doc.addImage(g.data, 'JPEG', x, y, wid, hgt); doc.setDrawColor(220, 214, 213); doc.rect(x, y, wid, hgt);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); warna(ABU); doc.text('Gambar ' + (i + 1), x, y + hgt + 5);
        tinggiBaris = Math.max(tinggiBaris, hgt); x += lebar + 6;
      });
    }

    // kaki halaman
    var n = doc.getNumberOfPages();
    for (var i = 1; i <= n; i++) {
      doc.setPage(i); doc.setDrawColor(230, 225, 224); doc.line(L, 283, L + W, 283);
      doc.setFont('helvetica', 'normal'); doc.setFontSize(7.8); warna(ABU);
      doc.text(aman('Dibuat pelapor dengan Cek Hatespeech (cek-hatespeech.vercel.app), aplikasi tugas akhir, bukan dokumen resmi ' + K.kampus + '.'), L, 288);
      doc.text(aman(nomor + '  |  Hal. ' + i + ' dari ' + n), L + W, 292, { align: 'right' });
    }
    doc.save(nomor + '.pdf');
    kotor = false;
    $('selesai').hidden = false; $('selesai').scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  perbarui();
})();
JS;

kepala([
    'judul'     => 'Buat laporan perundungan | Cek Hatespeech',
    'deskripsi' => 'Isi formulir laporan dugaan perundungan, periksa bukti dengan AI, lalu unduh PDF untuk diserahkan ke Satgas PPKPT Telkom University Purwokerto.',
    'css'       => $css,
    'kanonik'   => '/lapor',
]);
navigasi('lapor');
?>
<main id="top">
  <section class="doc-sec lapor" aria-labelledby="lapor-h">
    <div class="wrap">
      <div class="lapor__head">
        <h1 id="lapor-h">Buat laporan perundungan</h1>
        <p>Isi empat bagian di bawah, lalu unduh PDF untuk diserahkan ke <?= e(KAMPUS['tujuan']) ?>. Data dirimu hanya diolah di browser ini dan tidak dikirim ke server.</p>
      </div>

      <div class="lapor__grid">
        <form id="form-lapor" novalidate autocomplete="off">

          <fieldset class="bag">
            <legend><span>1</span>Data pelapor</legend>
            <p class="bag__info">Dipakai Satgas untuk menghubungimu. Tidak ada yang disimpan di web ini.</p>
            <div class="isian">
              <div class="f"><label for="nama">Nama lengkap <em>*</em></label><input type="text" id="nama" autocomplete="name" maxlength="80"></div>
              <div class="f"><label for="nim">NIM <em>*</em></label><input type="text" id="nim" inputmode="numeric" maxlength="12" placeholder="contoh: 2311102074"></div>
              <div class="f"><label for="prodi">Program studi <em>*</em></label><input type="text" id="prodi" maxlength="80" placeholder="contoh: S1 Teknik Informatika"></div>
              <div class="f"><label for="angkatan">Angkatan</label><input type="number" id="angkatan" min="2015" max="2030" placeholder="contoh: 2023"></div>
              <div class="f penuh"><label for="kontak">Kontak yang bisa dihubungi <em>*</em></label><input type="text" id="kontak" maxlength="100" placeholder="Email kampus atau nomor WhatsApp"></div>
              <div class="f penuh"><span class="f__label">Kamu melapor sebagai <em>*</em></span>
                <div class="pilih"><label><input type="radio" name="peran" id="peran-korban" value="Korban"> Korban</label><label><input type="radio" name="peran" value="Saksi"> Saksi atau teman korban</label></div>
              </div>
            </div>
          </fieldset>

          <fieldset class="bag">
            <legend><span>2</span>Kejadian</legend>
            <p class="bag__info">Tulis seingatmu. Detail kecil seperti jam atau nama grup membantu Satgas menindaklanjuti.</p>
            <div class="isian">
              <div class="f"><label for="tanggal">Tanggal kejadian <em>*</em></label><input type="date" id="tanggal"><small>Kalau berulang, isi tanggal kejadian terakhir.</small></div>
              <div class="f"><label for="frekuensi">Sudah berapa kali?</label><select id="frekuensi"><option>Sekali</option><option>2 sampai 3 kali</option><option>Lebih dari 3 kali</option><option>Terus berlangsung</option></select></div>
              <div class="f"><label for="tempat">Tempat atau aplikasi <em>*</em></label><select id="tempat"><option value="">Pilih</option><?php foreach ($tempat as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select></div>
              <div class="f" id="f-tempat-lain" hidden><label for="tempat-lain">Tulis tempatnya <em>*</em></label><input type="text" id="tempat-lain" maxlength="80"></div>
              <div class="f penuh"><span class="f__label">Bentuk perundungan <em>*</em></span>
                <div class="pilih"><?php foreach ($bentuk as $i => $b): ?><label><input type="checkbox" name="bentuk" id="bentuk-<?= $i ?>" value="<?= e($b) ?>"> <?= e($b) ?></label><?php endforeach; ?></div>
              </div>
              <div class="f penuh"><label for="pelaku">Terduga pelaku</label><input type="text" id="pelaku" maxlength="120" placeholder="Nama atau nama akun, boleh dikosongkan"></div>
              <div class="f penuh"><label for="kronologi">Kronologi singkat <em>*</em></label><textarea id="kronologi" maxlength="1500" placeholder="Apa yang terjadi, siapa saja yang ada, dan apa dampaknya untukmu."></textarea><span class="hitung" id="hitung-kronologi"></span></div>
            </div>
          </fieldset>

          <fieldset class="bag">
            <legend><span>3</span>Bukti</legend>
            <p class="bag__info">Salin teks pesan atau komentar yang menyerangmu. Hanya teks ini yang dikirim ke server untuk dinilai AI, dan tidak disimpan.</p>
            <div class="f">
              <label for="bukti">Teks pesan atau komentar</label>
              <textarea id="bukti" maxlength="<?= MAKS_KARAKTER ?>" placeholder="Tempel di sini, misalnya: besok kalau dia masih nongol di lab, kita kerjain lagi"></textarea>
              <span class="hitung" id="hitung-bukti"></span>
            </div>
            <div class="periksa-row">
              <button class="btn btn--secondary" type="button" id="tombol-ai">Periksa dengan AI <svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></button>
            </div>
            <p class="pesan pesan--galat" id="ai-galat" role="alert" hidden></p>
            <p class="pesan pesan--basi" id="ai-basi" hidden>Teks bukti berubah setelah diperiksa. Tekan Periksa ulang agar hasil di PDF sesuai.</p>
            <div class="ai" id="ai" hidden aria-live="polite">
              <div class="ai__vonis"><span class="ai__ikon" id="ai-ikon" aria-hidden="true"></span><div><p class="ai__judul" id="ai-judul"></p><p class="ai__sub" id="ai-sub"></p></div></div>
              <div class="ai__isi">
                <div class="skor">
                  <div><span>Skor ujaran kebencian</span><b id="ai-hs"></b><i id="ai-hs-bar"></i></div>
                  <div><span>Skor bahasa kasar</span><b id="ai-ab"></b><i id="ai-ab-bar"></i></div>
                </div>
                <p class="ai__teks" id="ai-teks"></p>
                <p class="ai__catatan">Penilaian model AI, bukan putusan. Hasil apa pun tetap boleh kamu laporkan.</p>
              </div>
            </div>

            <div class="f" style="margin-top: var(--s-6)">
              <span class="f__label">Tangkapan layar</span>
              <label class="unggah"><input type="file" id="gambar" accept="image/png,image/jpeg,image/webp" multiple><b>Pilih gambar</b><span id="gambar-info">PNG, JPG atau WebP, maksimal 3</span><small>Gambar hanya dimasukkan ke PDF, tidak diunggah ke server.</small></label>
              <div class="thumbs" id="thumbs"></div>
            </div>
          </fieldset>

          <fieldset class="bag">
            <legend><span>4</span>Pernyataan</legend>
            <p class="bag__info">Laporan yang jujur membantu Satgas bertindak cepat.</p>
            <label class="setuju"><input type="checkbox" id="setuju"> Saya menyatakan informasi dalam laporan ini benar dan saya bersedia dihubungi untuk keterangan lebih lanjut.</label>
          </fieldset>
        </form>

        <aside class="ringkas" aria-label="Kelengkapan laporan">
          <div class="ringkas__head"><b>Kelengkapan laporan</b><small>Untuk <?= e(KAMPUS['tujuan']) ?></small></div>
          <ol>
            <li id="r-diri"><span>1. Data pelapor</span><span>Belum</span></li>
            <li id="r-kejadian"><span>2. Kejadian</span><span>Belum</span></li>
            <li id="r-bukti"><span>3. Bukti</span><span>Belum</span></li>
            <li id="r-ai"><span>Pemeriksaan AI</span><span>Belum diperiksa</span></li>
            <li id="r-setuju"><span>4. Pernyataan</span><span>Belum</span></li>
          </ol>
          <ul class="salah-list" id="salah-list" hidden role="alert"></ul>
          <div class="ringkas__aksi">
            <button class="btn btn--primary" type="submit" form="form-lapor">Unduh PDF laporan<svg class="m-arrow" viewBox="0 0 12 12" aria-hidden="true"><path class="m-arrow__stem" d="M1 6h8"/><path class="m-arrow__head" d="M4.5 2.5 8 6l-3.5 3.5"/></svg></button>
            <p>PDF dibuat di perangkatmu. Setelah diunduh, cetak atau kirim ke Satgas lewat kanal resmi kampus.</p>
            <p class="pesan" id="selesai" hidden style="background:#e4f6ea;color:#17603b;margin:0">PDF sudah diunduh. Langkah berikutnya: serahkan ke <?= e(KAMPUS['tujuan']) ?> bersama bukti aslinya.</p>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>
<script type="application/json" id="konfig"><?= json_encode($konfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
<?php
penutup(['js' => $js]);
