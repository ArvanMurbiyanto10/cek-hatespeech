/** Supabase dianggap siap bila URL dan anon key sudah diisi di Environment Variables. */
export const SUPABASE_URL = process.env.NEXT_PUBLIC_SUPABASE_URL ?? '';
export const SUPABASE_ANON_KEY = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY ?? '';
export const supabaseSiap = Boolean(SUPABASE_URL && SUPABASE_ANON_KEY);
