'use client';
import type { jsPDF as JsPDF } from 'jspdf';
import { KAMPUS, PASAL, STATUS, type KodePasal } from './kampus';
import type { LaporanLengkap } from './types';

/**
 * Membuat PDF "Laporan Dugaan Perundungan" dari data laporan yang tersimpan.
 * Dijalankan di browser; gambar lampiran diambil lewat tautan sementara Supabase Storage.
 */

const MERAH: [number, number, number] = [168, 16, 26];
const TINTA: [number, number, number] = [29, 26, 26];
const ABU: [number, number, number] = [100, 94, 94];
const JUDUL_AI: Record<string, string> = {
  hate: 'Indikasi kuat ujaran kebencian', tinjau: 'Perlu ditinjau manusia', abusive: 'Bahasa kasar', aman: 'Tidak terindikasi',
};
const WARNA_AI: Record<string, [[number, number, number], [number, number, number]]> = {
  hate: [[251, 233, 230], [163, 39, 27]], tinjau: [[253, 242, 224], [125, 74, 0]],
  abusive: [[235, 240, 246], [46, 68, 98]], aman: [[228, 246, 234], [23, 96, 59]],
};

/** Huruf standar PDF hanya memuat Latin-1: emoji dibuang, karakter lain di luar Latin-1 diganti tanda tanya. */
function aman(s: unknown): string {
  return String(s == null || s === '' ? '-' : s)
    .replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '')
    .replace(/[‘’]/g, "'").replace(/[“”]/g, '"').replace(/[–—]/g, '-').replace(/…/g, '...')
    .replace(/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/g, '?');
}
const tglPanjang = (d: Date) => d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const persen = (x: number) => (x * 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';

async function keDataUrl(url: string): Promise<{ data: string; w: number; h: number } | null> {
  try {
    const blob = await (await fetch(url)).blob();
    const data = await new Promise<string>((ok) => { const r = new FileReader(); r.onload = () => ok(String(r.result)); r.readAsDataURL(blob); });
    const img = await new Promise<HTMLImageElement>((ok, gagal) => { const i = new Image(); i.onload = () => ok(i); i.onerror = gagal; i.src = data; });
    return { data, w: img.width, h: img.height };
  } catch {
    return null;
  }
}

export async function unduhPdfLaporan(lap: LaporanLengkap): Promise<void> {
  const { jsPDF } = await import('jspdf');
  const doc: JsPDF = new jsPDF({ unit: 'mm', format: 'a4' });
  const L = 20, W = 170;
  let y = 0;
  const p = lap.pelapor;
  const dibuat = new Date(lap.dibuat);

  const warna = (c: [number, number, number]) => doc.setTextColor(c[0], c[1], c[2]);
  const cukup = (h: number) => { if (y + h > 272) { doc.addPage(); y = 20; } };
  const judul = (t: string) => {
    cukup(14); y += 4;
    doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); warna(MERAH); doc.text(aman(t).toUpperCase(), L, y);
    y += 2.5; doc.setDrawColor(236, 214, 215); doc.line(L, y, L + W, y); y += 6;
  };
  const baris = (label: string, isi: unknown) => {
    doc.setFontSize(10.5);
    const v = doc.splitTextToSize(aman(isi), W - 52) as string[];
    cukup(v.length * 5 + 2);
    doc.setFont('helvetica', 'normal'); warna(ABU); doc.text(aman(label), L, y);
    warna(TINTA); doc.text(v, L + 52, y); y += v.length * 5 + 1.6;
  };
  const paragraf = (t: string, ukuran = 10.5, c = TINTA) => {
    const lh = ukuran * 0.45 + 1.4;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(ukuran); warna(c);
    const b = doc.splitTextToSize(aman(t), W) as string[];
    if (b.length * lh < 45) cukup(b.length * lh + 2);
    b.forEach((x) => { cukup(6); doc.text(x, L, y); y += lh; });
  };
  const subjudul = (t: string) => { doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5); warna(TINTA); cukup(8); doc.text(aman(t), L, y); y += 5.5; };
  const kutipan = (t: string) => {
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5);
    const b = doc.splitTextToSize(aman(t), W - 10) as string[];
    for (let i = 0; i < b.length; i += 40) {
      const pot = b.slice(i, i + 40), h = pot.length * 5 + 6;
      cukup(h);
      doc.setFillColor(249, 245, 244); doc.rect(L, y - 4, W, h, 'F');
      doc.setFillColor(...MERAH); doc.rect(L, y - 4, 1.2, h, 'F');
      warna(TINTA); doc.text(pot, L + 5, y + 0.5); y += h + 2;
    }
  };

  // ---- kop ----
  doc.setFillColor(...MERAH); doc.rect(0, 0, 210, 7, 'F');
  y = 22; doc.setFont('helvetica', 'bold'); doc.setFontSize(18); warna(TINTA); doc.text('LAPORAN DUGAAN PERUNDUNGAN', L, y);
  y += 7; doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(ABU);
  doc.text(aman(`Kepada: ${KAMPUS.tujuan}`), L, y); y += 5;
  doc.text(aman(`Nomor: ${lap.kode}     Dikirim: ${tglPanjang(dibuat)}     Status: ${STATUS[lap.status].label}`), L, y); y += 4;

  judul('1. Data pelapor');
  baris('Nama lengkap', p?.nama);
  baris('NIM', p?.nim);
  baris('Program studi', (p?.prodi ?? '-') + (p?.angkatan ? `, angkatan ${p.angkatan}` : ''));
  baris('Kontak', [p?.kontak, p?.email].filter(Boolean).join(' / '));
  baris('Melapor sebagai', lap.peran === 'korban' ? 'Korban' : 'Saksi atau teman korban');

  judul('2. Kejadian');
  baris('Tanggal kejadian', tglPanjang(new Date(lap.tanggal_kejadian + 'T00:00:00')));
  baris('Tempat atau aplikasi', lap.tempat);
  baris('Frekuensi', lap.frekuensi);
  baris('Bentuk', lap.bentuk.join(', '));
  baris('Terduga pelaku', lap.terduga_pelaku || 'Tidak disebutkan');
  y += 1; subjudul('Kronologi'); paragraf(lap.kronologi);

  judul('3. Bukti');
  for (const b of lap.bukti) {
    subjudul('Teks pesan atau komentar');
    kutipan(b.isi_teks);
    if (b.ai_label) {
      const [bg, fg] = WARNA_AI[b.ai_label];
      cukup(40); y += 2;
      doc.setFillColor(...bg); doc.roundedRect(L, y, W, 12, 2, 2, 'F');
      doc.setFont('helvetica', 'bold'); doc.setFontSize(12); warna(fg); doc.text(aman(`Hasil pemeriksaan AI: ${JUDUL_AI[b.ai_label]}`), L + 5, y + 7.8); y += 19;
      for (const [nama, nilai] of [['Skor ujaran kebencian (HATESPEECH)', Number(b.skor_hatespeech ?? 0)], ['Skor bahasa kasar (ABUSIVE)', Number(b.skor_abusive ?? 0)]] as const) {
        doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(TINTA); doc.text(nama, L, y);
        doc.setFont('helvetica', 'bold'); doc.text(persen(nilai), L + W, y, { align: 'right' }); y += 2.6;
        doc.setFillColor(236, 236, 236); doc.roundedRect(L, y, W, 2.4, 1.2, 1.2, 'F');
        if (nilai > 0.004) { doc.setFillColor(...TINTA); doc.roundedRect(L, y, Math.max(2.4, W * nilai), 2.4, 1.2, 1.2, 'F'); }
        y += 8;
      }
      if (b.temuan.length) baris('Kata yang disorot', b.temuan.map((t) => `${t.kata} (${t.tipe})`).join(', '));
      baris('Model', b.model);
    } else {
      paragraf('Pemeriksaan AI tidak tersedia saat laporan dikirim.', 9.5, ABU);
    }
  }
  if (lap.lampiran.length) { y += 1; baris('Tangkapan layar', `${lap.lampiran.length} gambar, lihat halaman Lampiran`); }

  cukup(60);
  judul('4. Dasar hukum yang mungkin terkait');
  for (const lp of lap.laporan_pasal) {
    const ps = PASAL[lp.pasal_kode as KodePasal];
    if (!ps) continue;
    doc.setFontSize(10);
    const isi = doc.splitTextToSize(aman(`${ps.judul}. ${ps.isi}`), W - 6) as string[];
    cukup(isi.length * 5 + 26);
    const y0 = y - 4;
    doc.setFont('helvetica', 'bold'); doc.setFontSize(11); warna(lp.pasal_kode === 'kampus' ? TINTA : [140, 13, 21]); doc.text(aman(ps.nama), L + 5, y); y += 5.5;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10); warna(TINTA); doc.text(isi, L + 5, y); y += isi.length * 4.7 + 1;
    doc.setFont('helvetica', 'bold');
    const an = doc.splitTextToSize(aman((lp.pasal_kode === 'kampus' ? 'Sanksi: ' : 'Ancaman pidana: ') + ps.ancaman), W - 6) as string[];
    doc.text(an, L + 5, y); y += an.length * 4.7 + 1;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(9); warna(ABU);
    const al = doc.splitTextToSize(aman(`Dasar pemilihan: ${lp.alasan}${ps.catatan ? '. ' + ps.catatan : ''}`), W - 6) as string[];
    doc.text(al, L + 5, y); y += al.length * 4.2 + 2;
    // warna isian diatur tepat sebelum garis: di PDF, warna teks memakai warna isian yang sama
    if (lp.pasal_kode === 'kampus') doc.setFillColor(150, 142, 142); else doc.setFillColor(...MERAH);
    doc.rect(L, y0, 1.2, y - y0 - 1, 'F');
    y += 3;
  }
  paragraf('Daftar ini dipilih otomatis oleh aplikasi dari hasil AI dan isian pelapor. Penentuan pasal tetap wewenang Satgas dan aparat penegak hukum.', 9, ABU);

  judul('5. Riwayat penanganan');
  for (const r of lap.riwayat_status) {
    baris(tglPanjang(new Date(r.dibuat)), `${STATUS[r.status as keyof typeof STATUS]?.label ?? r.status}${r.catatan ? ': ' + r.catatan : ''}`);
  }

  cukup(80);
  judul('6. Pernyataan');
  paragraf('Saya menyatakan bahwa informasi dalam laporan ini saya tulis dengan sebenar-benarnya dan saya bersedia dihubungi untuk keterangan lebih lanjut.');
  y += 6;
  doc.setFont('helvetica', 'normal'); doc.setFontSize(10.5); warna(TINTA);
  const tx = L + W - 62;
  doc.text(aman(`Purwokerto, ${tglPanjang(dibuat)}`), tx, y); y += 5; doc.text('Pelapor,', tx, y); y += 24;
  doc.text(aman(`( ${p?.nama ?? ''} )`), tx, y); y += 5; warna(ABU); doc.text(aman(`NIM ${p?.nim ?? '-'}`), tx, y); y += 10;

  // ---- lampiran ----
  if (lap.lampiran.length) {
    doc.addPage(); y = 20;
    judul(`Lampiran: tangkapan layar (${lap.lampiran.length})`);
    const lebar = 82;
    let x = L, tinggiBaris = 0;
    for (const [i, l] of lap.lampiran.entries()) {
      const g = l.url ? await keDataUrl(l.url) : null;
      if (i % 2 === 0) { if (i) y += tinggiBaris + 12; tinggiBaris = 0; x = L; }
      if (!g) { doc.setFontSize(9); warna(ABU); doc.text(`Gambar ${i + 1} tidak dapat dimuat.`, x, y + 5); x += lebar + 6; continue; }
      const hgt = Math.min((lebar * g.h) / g.w, 118), wid = (hgt * g.w) / g.h;
      if (i % 2 === 0) cukup(hgt + 10);
      doc.addImage(g.data, 'JPEG', x, y, wid, hgt); doc.setDrawColor(220, 214, 213); doc.rect(x, y, wid, hgt);
      doc.setFont('helvetica', 'normal'); doc.setFontSize(9); warna(ABU); doc.text(`Gambar ${i + 1}`, x, y + hgt + 5);
      tinggiBaris = Math.max(tinggiBaris, hgt); x += lebar + 6;
    }
  }

  // ---- kaki halaman ----
  const n = doc.getNumberOfPages();
  for (let i = 1; i <= n; i++) {
    doc.setPage(i); doc.setDrawColor(230, 225, 224); doc.line(L, 283, L + W, 283);
    doc.setFont('helvetica', 'normal'); doc.setFontSize(7.8); warna(ABU);
    doc.text(aman(`Dicetak dari Cek Hatespeech (cek-hatespeech.vercel.app), aplikasi tugas akhir, bukan dokumen resmi ${KAMPUS.nama}.`), L, 288);
    doc.text(aman(`${lap.kode}  |  Hal. ${i} dari ${n}`), L + W, 292, { align: 'right' });
  }
  doc.save(`${lap.kode}.pdf`);
}
