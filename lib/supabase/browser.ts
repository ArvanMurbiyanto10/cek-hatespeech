'use client';
import { createBrowserClient } from '@supabase/ssr';
import { SUPABASE_ANON_KEY, SUPABASE_URL } from './konfigurasi';

let klien: ReturnType<typeof createBrowserClient> | null = null;

/** Klien Supabase untuk komponen di browser (login, unggah lampiran). */
export function supabaseBrowser() {
  klien ??= createBrowserClient(SUPABASE_URL, SUPABASE_ANON_KEY);
  return klien;
}
