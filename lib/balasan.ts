/**
 * Membaca balasan JSON dari API di browser. Bila server error dan tidak mengirim JSON
 * (misalnya MySQL mati), kembalikan pesan yang bisa dipahami, bukan "Unexpected end of JSON input".
 */
export async function bacaBalasan<T extends { ok: boolean; galat?: string }>(r: Response): Promise<T> {
  const teks = await r.text();
  try {
    return JSON.parse(teks) as T;
  } catch {
    return {
      ok: false,
      galat: `Server sedang bermasalah (kode ${r.status}). Coba lagi sebentar; bila masih gagal, cek terminal "npm run dev" (biasanya MySQL belum menyala).`,
    } as T;
  }
}
