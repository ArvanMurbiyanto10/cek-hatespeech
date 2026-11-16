import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  // Gambar tangkapan layar diunggah langsung ke Supabase Storage dari browser,
  // jadi batas body Route Handler cukup untuk teks laporan.
  poweredByHeader: false,
};

export default nextConfig;
