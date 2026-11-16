import { dbSiap } from '@/lib/db';

/** Pemberitahuan untuk pengembang bila database atau kunci sesi belum diatur. */
export function BelumSiap() {
  if (dbSiap && process.env.SESSION_SECRET) return null;
  return (
    <p className="belum-siap">
      Database belum terhubung. Isi <code>DATABASE_URL</code> dan <code>SESSION_SECRET</code> di <code>.env.local</code> (lokal) atau
      Environment Variables Vercel, lalu jalankan <code>database/schema.sql</code> di MySQL.
    </p>
  );
}
