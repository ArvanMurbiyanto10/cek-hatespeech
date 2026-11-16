'use server';
import { revalidatePath } from 'next/cache';
import { penggunaSaatIni } from '@/lib/auth';
import { transaksi } from '@/lib/db';
import { DAFTAR_STATUS, type StatusLaporan } from '@/lib/kampus';
import type { ResultSetHeader } from 'mysql2/promise';

export interface HasilAksi { ok: boolean; pesan: string }

/** Satgas mengubah status laporan dan menulis catatan; status dan riwayat disimpan dalam satu transaksi. */
export async function ubahStatus(_: HasilAksi | null, form: FormData): Promise<HasilAksi> {
  const user = await penggunaSaatIni();
  if (!user) return { ok: false, pesan: 'Sesi berakhir. Masuk lagi.' };
  if (user.role !== 'satgas') return { ok: false, pesan: 'Hanya petugas Satgas yang dapat mengubah status.' };

  const id = Number(form.get('id'));
  const kode = String(form.get('kode') ?? '');
  const status = String(form.get('status') ?? '') as StatusLaporan;
  const prioritas = String(form.get('prioritas') ?? '');
  const catatan = String(form.get('catatan') ?? '').trim().slice(0, 1000);
  if (!Number.isInteger(id)) return { ok: false, pesan: 'Laporan tidak dikenal.' };
  if (!DAFTAR_STATUS.includes(status)) return { ok: false, pesan: 'Status tidak dikenal.' };
  if (!['tinggi', 'sedang', 'normal'].includes(prioritas)) return { ok: false, pesan: 'Prioritas tidak dikenal.' };
  if (status === 'ditolak' && !catatan) return { ok: false, pesan: 'Tulis alasan penolakan di catatan agar pelapor mengerti.' };

  try {
    const ada = await transaksi(async (c) => {
      const [r] = await c.query<ResultSetHeader>('UPDATE laporan SET status = ?, prioritas = ? WHERE id = ?', [status, prioritas, id]);
      if (!r.affectedRows) return false;
      await c.query('INSERT INTO riwayat_status (laporan_id, status, catatan, oleh) VALUES (?, ?, ?, ?)', [id, status, catatan, user.id]);
      return true;
    });
    if (!ada) return { ok: false, pesan: 'Laporan tidak ditemukan.' };
  } catch (e) {
    console.error('Gagal mengubah status', e);
    return { ok: false, pesan: 'Status gagal disimpan. Coba lagi.' };
  }

  revalidatePath(`/laporan/${kode}`);
  revalidatePath('/satgas');
  return { ok: true, pesan: 'Status laporan diperbarui. Pelapor dapat melihatnya di halaman Laporan saya.' };
}
