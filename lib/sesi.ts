import { jwtVerify, SignJWT } from 'jose';

/**
 * Sesi login berupa JWT bertanda tangan (HS256) di cookie httpOnly.
 * Berkas ini tidak memakai modul Node sehingga bisa dipakai juga di middleware (Edge runtime).
 */

export const NAMA_COOKIE = 'sesi';
export const UMUR_SESI = 60 * 60 * 24 * 7; // 7 hari

export interface IsiSesi {
  uid: number;
  role: 'mahasiswa' | 'satgas';
}

function kunci(): Uint8Array {
  const rahasia = process.env.SESSION_SECRET;
  if (!rahasia || rahasia.length < 32) throw new Error('SESSION_SECRET belum diatur (minimal 32 karakter).');
  return new TextEncoder().encode(rahasia);
}

export async function buatToken(isi: IsiSesi): Promise<string> {
  return new SignJWT({ role: isi.role })
    .setProtectedHeader({ alg: 'HS256' })
    .setSubject(String(isi.uid))
    .setIssuedAt()
    .setExpirationTime(`${UMUR_SESI}s`)
    .sign(kunci());
}

export async function bacaToken(token: string | undefined): Promise<IsiSesi | null> {
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, kunci(), { algorithms: ['HS256'] });
    const uid = Number(payload.sub);
    const role = payload.role;
    if (!Number.isInteger(uid) || (role !== 'mahasiswa' && role !== 'satgas')) return null;
    return { uid, role };
  } catch {
    return null;
  }
}
