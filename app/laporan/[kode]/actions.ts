'use server';
import { revalidatePath } from 'next/cache';
import { supabaseServer } from '@/lib/supabase/server';
import { DAFTAR_STATUS, type StatusLaporan } from '@/lib/kampus';

export interface HasilAksi { ok: boolean; pesan: string }

/** Satgas mengubah status laporan dan menulis catatan. RLS menolak bila pengguna bukan Satgas. */
export async function ubahStatus(_: HasilAksi | null, form: FormData): Promise<HasilAksi> {
  const id = String(form.get('id') ?? '');
  const kode = String(form.get('kode') ?? '');
  const status = String(form.get('status') ?? '') as StatusLaporan;
  const prioritas = String(form.get('prioritas') ?? '');
  const catatan = String(form.get('catatan') ?? '').trim().slice(0, 1000);
  if (!DAFTAR_STATUS.includes(status)) return { ok: false, pesan: 'Status tidak dikenal.' };
  if (!['tinggi', 'sedang', 'normal'].includes(prioritas)) return { ok: false, pesan: 'Prioritas tidak dikenal.' };
  if (status === 'ditolak' && !catatan) return { ok: false, pesan: 'Tulis alasan penolakan di catatan agar pelapor mengerti.' };

  const sb = await supabaseServer();
  const { data: auth } = await sb.auth.getUser();
  if (!auth.user) return { ok: false, pesan: 'Sesi berakhir. Masuk lagi.' };

  const { data: diubah, error } = await sb
    .from('laporan')
    .update({ status, prioritas, diubah: new Date().toISOString() })
    .eq('id', id)
    .select('id');
  if (error || !diubah?.length) return { ok: false, pesan: 'Status gagal diubah. Pastikan akunmu akun Satgas.' };

  const { error: e2 } = await sb.from('riwayat_status').insert({ laporan_id: id, status, catatan, oleh: auth.user.id });
  if (e2) return { ok: false, pesan: `Status diubah, tetapi catatan gagal disimpan: ${e2.message}` };

  revalidatePath(`/laporan/${kode}`);
  revalidatePath('/satgas');
  return { ok: true, pesan: 'Status laporan diperbarui. Pelapor dapat melihatnya di halaman Laporan saya.' };
}
