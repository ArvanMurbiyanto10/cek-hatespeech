import { supabaseSiap } from '@/lib/supabase/konfigurasi';

/** Pemberitahuan untuk pengembang bila Supabase belum dihubungkan (env belum diisi). */
export function BelumSiap() {
  if (supabaseSiap) return null;
  return (
    <p className="belum-siap">
      Database belum terhubung. Isi <code>NEXT_PUBLIC_SUPABASE_URL</code> dan <code>NEXT_PUBLIC_SUPABASE_ANON_KEY</code> di{' '}
      <code>.env.local</code> (lokal) atau Environment Variables Vercel, lalu jalankan <code>supabase/schema.sql</code>.
    </p>
  );
}
