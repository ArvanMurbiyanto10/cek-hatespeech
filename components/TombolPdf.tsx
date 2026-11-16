'use client';
import { useState } from 'react';
import type { LaporanLengkap } from '@/lib/types';
import { Panah } from './Panah';

export function TombolPdf({ laporan, kelas = 'btn btn--primary' }: { laporan: LaporanLengkap; kelas?: string }) {
  const [sibuk, setSibuk] = useState(false);
  async function unduh() {
    setSibuk(true);
    try {
      const { unduhPdfLaporan } = await import('@/lib/pdf');
      await unduhPdfLaporan(laporan);
    } catch {
      alert('PDF gagal dibuat. Coba muat ulang halaman.');
    } finally {
      setSibuk(false);
    }
  }
  return (
    <button type="button" className={kelas} onClick={unduh} disabled={sibuk}>
      {sibuk ? 'Membuat PDF…' : 'Unduh PDF laporan'}<Panah />
    </button>
  );
}
