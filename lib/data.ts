import 'server-only';
import { supabaseServer } from './supabase/server';
import type { LaporanLengkap } from './types';

/**
 * Mengambil satu laporan lengkap berdasarkan kodenya. RLS memastikan mahasiswa hanya mendapat laporannya sendiri
 * dan Satgas mendapat semua. Gambar lampiran diberi tautan sementara (1 jam) karena bucket-nya privat.
 */
export async function ambilLaporanLengkap(kode: string): Promise<LaporanLengkap | null> {
  const sb = await supabaseServer();
  const { data } = await sb
    .from('laporan')
    .select('*, pelapor:profiles(nama, nim, prodi, angkatan, kontak, email), bukti(*), lampiran(*), laporan_pasal(*), riwayat_status(*)')
    .eq('kode', kode)
    .maybeSingle();
  if (!data) return null;

  const lap = data as unknown as LaporanLengkap;
  lap.riwayat_status.sort((a, b) => a.dibuat.localeCompare(b.dibuat));
  lap.lampiran.sort((a, b) => a.path.localeCompare(b.path));
  if (lap.lampiran.length) {
    const { data: url } = await sb.storage.from('lampiran').createSignedUrls(lap.lampiran.map((l) => l.path), 3600);
    lap.lampiran = lap.lampiran.map((l, i) => ({ ...l, url: url?.[i]?.signedUrl ?? null }));
  }
  return lap;
}
