import 'server-only';
import mysql, { type Pool, type PoolConnection, type ResultSetHeader, type RowDataPacket } from 'mysql2/promise';

/**
 * Koneksi MySQL (pool). Semua query memakai parameter (?) sehingga aman dari SQL injection.
 * DATABASE_URL contoh: mysql://root:@127.0.0.1:3306/cek_hatespeech
 * DB_SSL=true untuk layanan cloud yang mewajibkan TLS (TiDB Cloud, Aiven, dll.); DB_CA berisi sertifikat CA bila perlu.
 */

export const dbSiap = Boolean(process.env.DATABASE_URL);

const global_ = globalThis as unknown as { __poolCekHatespeech?: Pool };

function buatPool(): Pool {
  const ssl = process.env.DB_SSL === 'true' ? { minVersion: 'TLSv1.2', rejectUnauthorized: true, ...(process.env.DB_CA ? { ca: process.env.DB_CA } : {}) } : undefined;
  const p = mysql.createPool({
    uri: process.env.DATABASE_URL,
    ssl,
    connectionLimit: 5,
    waitForConnections: true,
    timezone: 'Z',
    dateStrings: ['DATE'],
    decimalNumbers: true,
    charset: 'utf8mb4',
  });
  // Simpan dan baca waktu dalam UTC; saat ditampilkan, waktu diformat ke WIB (Asia/Jakarta).
  p.pool.on('connection', (c) => c.query("SET time_zone = '+00:00'"));
  return p;
}

/** Satu pool per proses (dipakai ulang antarpermintaan dan saat hot reload). */
export function pool(): Pool {
  if (!dbSiap) throw new Error('DATABASE_URL belum diatur.');
  global_.__poolCekHatespeech ??= buatPool();
  return global_.__poolCekHatespeech;
}

export async function pilih<T>(sql: string, params: unknown[] = []): Promise<T[]> {
  const [baris] = await pool().query<RowDataPacket[]>(sql, params);
  return baris as T[];
}

export async function satu<T>(sql: string, params: unknown[] = []): Promise<T | null> {
  return (await pilih<T>(sql, params))[0] ?? null;
}

export async function jalankan(sql: string, params: unknown[] = []): Promise<ResultSetHeader> {
  const [hasil] = await pool().query<ResultSetHeader>(sql, params);
  return hasil;
}

/** Menjalankan beberapa query dalam satu transaksi: semua berhasil, atau semua dibatalkan. */
export async function transaksi<T>(fn: (c: PoolConnection) => Promise<T>): Promise<T> {
  const c = await pool().getConnection();
  try {
    await c.beginTransaction();
    const hasil = await fn(c);
    await c.commit();
    return hasil;
  } catch (e) {
    await c.rollback();
    throw e;
  } finally {
    c.release();
  }
}

/** MySQL mengembalikan JSON sebagai objek, MariaDB sebagai teks; samakan keduanya. */
export function bacaJson<T>(v: unknown, cadangan: T): T {
  if (v == null) return cadangan;
  if (typeof v === 'string') {
    try { return JSON.parse(v) as T; } catch { return cadangan; }
  }
  return v as T;
}
