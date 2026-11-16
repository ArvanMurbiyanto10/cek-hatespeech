import { penggunaSaatIni } from '@/lib/auth';
import { satu } from '@/lib/db';

/** Mengirim gambar lampiran hanya kepada pelapornya sendiri atau petugas Satgas. */
export async function GET(_: Request, { params }: { params: Promise<{ id: string }> }) {
  const user = await penggunaSaatIni();
  if (!user) return new Response('Masuk dulu.', { status: 401 });
  const id = Number((await params).id);
  if (!Number.isInteger(id) || id < 1) return new Response('Tidak ditemukan.', { status: 404 });

  const g = await satu<{ mime: string; data: Buffer; pelapor_id: number }>(
    'SELECT a.mime, a.data, l.pelapor_id FROM lampiran a JOIN laporan l ON l.id = a.laporan_id WHERE a.id = ?',
    [id],
  );
  if (!g || (user.role !== 'satgas' && g.pelapor_id !== user.id)) return new Response('Tidak ditemukan.', { status: 404 });

  return new Response(new Uint8Array(g.data), {
    headers: {
      'Content-Type': g.mime,
      'Cache-Control': 'private, max-age=3600',
      'X-Content-Type-Options': 'nosniff',
      'Content-Disposition': 'inline',
    },
  });
}
