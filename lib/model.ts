import 'server-only';
import { deteksiKamus, NAMA_TIPE, type TipeKata } from './kamus';

/**
 * Pemanggilan model AI (FastAPI "RuangAman API" yang menjalankan nahiar/hatespeech-abusive-xlm-roberta-v1).
 * Label, skor dan tingkat berasal dari MODEL; kamus hanya menyorot kata dan memperkirakan sasaran.
 */

export const NAMA_MODEL = 'nahiar/hatespeech-abusive-xlm-roberta-v1';

export type LabelAI = 'hate' | 'tinjau' | 'abusive' | 'aman';

export const JUDUL_LABEL: Record<LabelAI, string> = {
  hate: 'Indikasi kuat ujaran kebencian',
  tinjau: 'Perlu ditinjau manusia',
  abusive: 'Bahasa kasar',
  aman: 'Tidak terindikasi',
};

export interface HasilAI {
  label: LabelAI;
  judul: string;
  penjelasan: string;
  hs: number;
  ab: number;
  target: string;
  kategori: string;
  tingkat: string;
  temuan: { kata: string; tipe: string; kode: TipeKata }[];
  html: string;
  model: string;
}

interface ResponsModel {
  skor: { HATESPEECH: number; ABUSIVE: number };
  status: 'INDIKASI_KUAT' | 'PERLU_DITINJAU' | 'BAHASA_KASAR' | 'TIDAK_TERINDIKASI';
  penjelasan?: string;
}

export class GalatModel extends Error {}

async function panggilModel(teks: string): Promise<ResponsModel> {
  const url = process.env.API_URL;
  if (!url) throw new GalatModel('API_URL belum diatur. Isi alamat server model di Environment Variables.');

  let res: Response;
  try {
    res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ teks }),
      signal: AbortSignal.timeout(25_000), // di bawah batas waktu fungsi Vercel
      cache: 'no-store',
    });
  } catch (e) {
    const timeout = e instanceof DOMException && e.name === 'TimeoutError';
    throw new GalatModel(
      timeout
        ? 'Server model sedang dinyalakan atau sibuk. Tunggu sekitar 1 menit, lalu coba lagi.'
        : 'Model AI tidak dapat dihubungi. Pastikan server model di Colab aktif dan API_URL sudah benar.',
    );
  }
  if ([502, 503, 504].includes(res.status)) {
    throw new GalatModel('Server model sedang dinyalakan atau sibuk. Tunggu sekitar 1 menit, lalu coba lagi.');
  }
  if (!res.ok) throw new GalatModel(`Model AI tidak dapat dihubungi (kode ${res.status}).`);
  const data = (await res.json()) as Partial<ResponsModel>;
  if (typeof data.skor?.HATESPEECH !== 'number' || typeof data.skor?.ABUSIVE !== 'number' || !data.status) {
    throw new GalatModel('Format respons model tidak dikenali.');
  }
  return data as ResponsModel;
}

const PETA: Record<ResponsModel['status'], [LabelAI, string]> = {
  INDIKASI_KUAT: ['hate', 'Kuat'],
  PERLU_DITINJAU: ['tinjau', 'Perlu ditinjau'],
  BAHASA_KASAR: ['abusive', '-'],
  TIDAK_TERINDIKASI: ['aman', '-'],
};

export async function deteksiHatespeech(teks: string): Promise<HasilAI> {
  const model = await panggilModel(teks);
  const bantu = deteksiKamus(teks);
  const [label, tingkat] = PETA[model.status] ?? ['aman', '-'];
  return {
    label,
    judul: JUDUL_LABEL[label],
    penjelasan: model.penjelasan ?? '',
    hs: model.skor.HATESPEECH,
    ab: model.skor.ABUSIVE,
    target: bantu.target,
    kategori: bantu.kategori.length ? bantu.kategori.join(', ') : '-',
    tingkat,
    temuan: bantu.temuan.map((t) => ({ kata: t.kata, tipe: NAMA_TIPE[t.tipe], kode: t.tipe })),
    html: bantu.html,
    model: NAMA_MODEL,
  };
}
