import { NextResponse } from 'next/server';
import { deteksiHatespeech, GalatModel } from '@/lib/model';
import { MAKS_KARAKTER } from '@/lib/kampus';
import { penggunaSaatIni } from '@/lib/auth';

/** Pratinjau pemeriksaan bukti oleh AI di formulir laporan. Hanya untuk pengguna yang sudah masuk. */
export async function POST(req: Request) {
  const user = await penggunaSaatIni();
  if (!user) return NextResponse.json({ ok: false, galat: 'Masuk dulu untuk memeriksa bukti.' }, { status: 401 });

  const body = (await req.json().catch(() => ({}))) as { teks?: unknown };
  const teks = typeof body.teks === 'string' ? body.teks.trim() : '';
  if (!teks) return NextResponse.json({ ok: false, galat: 'Tempel dulu teks bukti yang ingin diperiksa.' });
  if (teks.length > MAKS_KARAKTER) {
    return NextResponse.json({ ok: false, galat: `Teks bukti terlalu panjang (maksimal ${MAKS_KARAKTER.toLocaleString('id-ID')} karakter).` });
  }
  try {
    return NextResponse.json({ ok: true, hasil: await deteksiHatespeech(teks) });
  } catch (e) {
    const pesan = e instanceof GalatModel ? e.message : 'Pemeriksaan gagal. Coba lagi sebentar lagi.';
    return NextResponse.json({ ok: false, galat: pesan });
  }
}
