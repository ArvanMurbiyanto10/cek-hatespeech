import 'server-only';
import { cookies } from 'next/headers';
import { createServerClient } from '@supabase/ssr';
import { SUPABASE_ANON_KEY, SUPABASE_URL, supabaseSiap } from './konfigurasi';
import type { Profil } from '@/lib/types';

/** Klien Supabase untuk Server Component, Server Action dan Route Handler (memakai sesi dari cookie). */
export async function supabaseServer() {
  const toko = await cookies();
  return createServerClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    cookies: {
      getAll: () => toko.getAll(),
      setAll: (daftar) => {
        try {
          daftar.forEach(({ name, value, options }) => toko.set(name, value, options));
        } catch {
          // Dipanggil dari Server Component: cookie diperbarui oleh middleware.
        }
      },
    },
  });
}

/** Pengguna yang sedang masuk beserta profilnya, atau null. */
export async function penggunaSaatIni(): Promise<{ id: string; email: string; profil: Profil | null } | null> {
  if (!supabaseSiap) return null;
  const sb = await supabaseServer();
  const { data } = await sb.auth.getUser();
  if (!data.user) return null;
  const { data: profil } = await sb.from('profiles').select('*').eq('id', data.user.id).maybeSingle<Profil>();
  return { id: data.user.id, email: data.user.email ?? '', profil };
}
