#!/usr/bin/env node
/**
 * Membuat (atau memperbarui) akun petugas Satgas PPKPT.
 * Akun Satgas sengaja tidak bisa dibuat dari halaman Daftar.
 *
 *   npm run buat-satgas -- --nama "Nama Petugas" --email satgas@contoh.ac.id --sandi "kata-sandi-kuat"
 *
 * Membaca DATABASE_URL (dan DB_SSL/DB_CA bila ada) dari environment atau .env.local.
 */
import { readFileSync, existsSync } from 'node:fs';
import mysql from 'mysql2/promise';
import bcrypt from 'bcryptjs';

if (existsSync('.env.local')) {
  for (const baris of readFileSync('.env.local', 'utf8').split(/\r?\n/)) {
    const m = baris.match(/^\s*([A-Z_]+)\s*=\s*(.*)\s*$/);
    if (m && !process.env[m[1]]) process.env[m[1]] = m[2].replace(/^["']|["']$/g, '');
  }
}

const arg = (nama) => {
  const i = process.argv.indexOf(`--${nama}`);
  return i > -1 ? process.argv[i + 1] : undefined;
};
const nama = arg('nama');
const email = arg('email')?.trim().toLowerCase();
const sandi = arg('sandi');

if (!nama || !email || !sandi) {
  console.error('Pemakaian: npm run buat-satgas -- --nama "Nama Petugas" --email satgas@contoh.ac.id --sandi "kata-sandi-kuat"');
  process.exit(1);
}
if (sandi.length < 10) {
  console.error('Kata sandi Satgas minimal 10 karakter.');
  process.exit(1);
}
if (!process.env.DATABASE_URL) {
  console.error('DATABASE_URL belum diatur (isi di .env.local).');
  process.exit(1);
}

const ssl = process.env.DB_SSL === 'true' ? { minVersion: 'TLSv1.2', rejectUnauthorized: true, ...(process.env.DB_CA ? { ca: process.env.DB_CA } : {}) } : undefined;
const db = await mysql.createConnection({ uri: process.env.DATABASE_URL, ssl });
const hash = await bcrypt.hash(sandi, 12);
await db.query(
  `INSERT INTO users (role, nama, email, password_hash) VALUES ('satgas', ?, ?, ?)
   ON DUPLICATE KEY UPDATE role = 'satgas', nama = VALUES(nama), password_hash = VALUES(password_hash)`,
  [nama, email, hash],
);
await db.end();
console.log(`Akun Satgas siap: ${email}`);
