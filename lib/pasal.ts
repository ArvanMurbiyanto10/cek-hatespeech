import { PASAL, type KodePasal, type Pasal } from './kampus';

/** Bagian hasil AI yang dibutuhkan untuk memilih pasal (dipakai di browser dan server). */
export interface RingkasAI {
  label: string;
  temuan: { kode: string }[];
}

export interface PasalTerpilih {
  kode: KodePasal;
  pasal: Pasal;
  alasan: string[];
}

/**
 * Memilih pasal yang MUNGKIN terkait dari hasil AI, kata yang disorot kamus, dan bentuk perundungan yang dicentang.
 * Permendikbudristek 55/2024 selalu disertakan. Penentuan pasal tetap wewenang Satgas dan aparat penegak hukum.
 */
export function pilihPasal(ai: RingkasAI | null, bentuk: readonly string[]): PasalTerpilih[] {
  const kode = ai ? ai.temuan.map((t) => t.kode) : [];
  const punya = (k: string) => kode.includes(k);
  const ada = (b: string) => bentuk.includes(b);

  const a28: string[] = [];
  const a29: string[] = [];
  const a27: string[] = [];
  if (ai?.label === 'hate') a28.push('AI: indikasi kuat ujaran kebencian');
  if (ai?.label === 'tinjau' && (punya('kelompok') || punya('slur'))) a28.push('AI: perlu ditinjau, menyasar kelompok');
  if (ada('Ujaran kebencian (suku, agama, ras)')) a28.push('Pelapor memilih: ujaran kebencian (suku, agama, ras)');
  if (punya('ancaman')) a29.push('Kamus menyorot kata ancaman');
  if (ada('Ancaman')) a29.push('Pelapor memilih: ancaman');
  if (ai?.label === 'abusive') a27.push('AI: bahasa kasar');
  if (punya('individu') || punya('fisik')) a27.push('Kamus menyorot hinaan ke perorangan');
  if (ada('Hinaan atau ejekan')) a27.push('Pelapor memilih: hinaan atau ejekan');

  const hasil: PasalTerpilih[] = [];
  if (a28.length) hasil.push({ kode: '28-2', pasal: PASAL['28-2'], alasan: a28 });
  if (a29.length) hasil.push({ kode: '29', pasal: PASAL['29'], alasan: a29 });
  if (a27.length) hasil.push({ kode: '27a', pasal: PASAL['27a'], alasan: a27 });
  hasil.push({ kode: 'kampus', pasal: PASAL.kampus, alasan: ['Berlaku untuk setiap laporan perundungan di kampus'] });
  return hasil;
}

/** Prioritas awal laporan untuk dasbor Satgas. */
export function hitungPrioritas(ai: RingkasAI | null, bentuk: readonly string[]): 'tinggi' | 'sedang' | 'normal' {
  const ancaman = bentuk.includes('Ancaman') || bentuk.includes('Kekerasan fisik') || !!ai?.temuan.some((t) => t.kode === 'ancaman');
  if (ancaman || ai?.label === 'hate') return 'tinggi';
  if (ai?.label === 'tinjau' || ai?.label === 'abusive' || bentuk.includes('Ujaran kebencian (suku, agama, ras)')) return 'sedang';
  return 'normal';
}
