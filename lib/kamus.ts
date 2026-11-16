/**
 * LAPISAN BANTU berbasis kamus kata (porting dari versi PHP).
 * Tidak menentukan hasil akhir; dipakai untuk menyorot kata dan memperkirakan sasaran/kategori.
 * Hasil akhir (label dan skor) tetap berasal dari model XLM-RoBERTa.
 */

export type TipeKata = 'slur' | 'fisik' | 'ancaman' | 'kasar' | 'kelompok' | 'individu';
type Kategori = 'agama' | 'ras' | 'gender' | 'fisik' | 'lainnya';

interface InfoKata {
  tipe: TipeKata;
  kategori: Kategori;
}

export interface Temuan {
  kata: string;
  tipe: TipeKata;
}

export interface HasilKamus {
  label: 'hate' | 'abusive' | 'aman';
  target: string;
  kategori: string[];
  tingkat: string;
  temuan: Temuan[];
  /** Teks asli yang sudah di-escape, dengan <mark> pada kata yang disorot. Aman untuk innerHTML. */
  html: string;
}

const DATA: Record<TipeKata, Partial<Record<Kategori, string[]>>> = {
  slur: {
    agama: ['kafir', 'kapir', 'murtad', 'penista agama', 'antek yahudi', 'thogut', 'togut', 'laknatullah', 'agama sesat', 'aliran sesat', 'teroris', 'onta', 'kaum sumbu pendek'],
    ras: ['aseng', 'cokin', 'cina babi', 'china babi', 'babi cina', 'cina kafir', 'negro', 'nigga', 'keling', 'inlander', 'monyet papua', 'antek aseng', 'pribumi malas'],
    gender: ['banci', 'bencong', 'maho', 'lekong', 'lonte', 'pelacur', 'perek', 'jablay', 'sundal', 'pecun', 'bispak', 'cabe cabean', 'homo menjijikkan', 'lgbt sampah'],
    lainnya: ['cebong', 'kadrun', 'kadal gurun', 'antek asing', 'pengkhianat bangsa', 'buzzerp'],
  },
  fisik: {
    fisik: ['cacat', 'autis', 'budek', 'pincang', 'cebol', 'kerdil', 'burik', 'dekil', 'gendut', 'jelek', 'sipit', 'pesek', 'muka babi', 'muka monyet'],
  },
  ancaman: {
    lainnya: ['bunuh', 'bantai', 'basmi', 'usir', 'musnahkan', 'habisi', 'ganyang', 'penggal', 'bacok', 'perkosa', 'hancurkan', 'enyahkan', 'mampus', 'modar', 'mati aja', 'mati saja', 'mati lu', 'mati kau', 'bakar aja', 'bakar saja', 'bakar hidup hidup', 'gantung aja', 'gantung saja', 'tembak mati', 'minggat'],
  },
  kasar: {
    lainnya: ['anjing', 'anjir', 'anjrit', 'njing', 'bangsat', 'bajingan', 'keparat', 'kampret', 'goblok', 'goblog', 'tolol', 'bego', 'bodoh', 'dungu', 'idiot', 'gila', 'sinting', 'brengsek', 'tai', 'taik', 'sampah', 'babi', 'monyet', 'setan', 'iblis', 'kontol', 'memek', 'ngentot', 'entot', 'jancok', 'jancuk', 'cok', 'asu', 'celeng', 'kunyuk', 'sialan', 'laknat', 'kampungan', 'udik', 'pantek', 'pukimak', 'bacot', 'bangke', 'bangkai', 'kurang ajar', 'biadab', 'najis', 'busuk', 'bedebah', 'jahanam'],
  },
  kelompok: {
    ras: ['cina', 'china', 'tionghoa', 'tiongkok', 'papua', 'jawa', 'batak', 'sunda', 'madura', 'arab', 'pribumi', 'pendatang', 'etnis', 'suku', 'ras', 'bule'],
    agama: ['yahudi', 'kristen', 'kristiani', 'katolik', 'islam', 'muslim', 'hindu', 'buddha', 'budha', 'konghucu', 'ateis', 'atheis', 'syiah', 'ahmadiyah', 'umat', 'agama'],
    gender: ['lgbt', 'gay', 'lesbi', 'lesbian', 'homo', 'transgender', 'waria', 'perempuan', 'wanita', 'cewek', 'betina'],
    lainnya: ['kalian', 'mereka', 'kaum', 'golongan', 'komunis', 'pki', 'partai'],
  },
  individu: {
    lainnya: ['kamu', 'kau', 'engkau', 'lu', 'lo', 'loe', 'elu', 'elo', 'lw', 'dia', 'ente', 'situ', 'anda'],
  },
};

/** Kata yang tidak boleh dicocokkan lewat pemotongan imbuhan (idiom, dll.). */
const PENGECUALIAN = new Set(['membabi', 'pantai', 'cocok']);
const AKHIRAN = ['nya', 'lah', 'kah', 'kan', 'an', 'i', 'mu', 'ku'];
const AWALAN = ['meng', 'meny', 'mem', 'men', 'me', 'peng', 'pem', 'pen', 'pe', 'di', 'ter', 'ber', 'ke', 'se'];
const ALAY: Record<string, string> = { '1': 'i', '0': 'o', '3': 'e', '4': 'a', '5': 's', '7': 't', '@': 'a', $: 's' };

/** Huruf kecil, ubah angka "alay" ke huruf, rapatkan huruf berulang (anjiiing -> anjing). */
export function normalisasi(teks: string): string {
  let t = teks.toLowerCase().replace(/[1034557@$]/g, (c) => ALAY[c] ?? c);
  t = t.replace(/(\p{L})\1+/gu, '$1');
  return t.replace(/[^\p{L}\s]+/gu, '').trim();
}

let kamusCache: Map<string, InfoKata> | null = null;

/** Urutan prioritas jika satu kata muncul di beberapa tipe. */
function kamus(): Map<string, InfoKata> {
  if (kamusCache) return kamusCache;
  const prioritas: TipeKata[] = ['slur', 'ancaman', 'kasar', 'fisik', 'kelompok', 'individu'];
  const m = new Map<string, InfoKata>();
  for (const tipe of prioritas) {
    for (const [kategori, daftar] of Object.entries(DATA[tipe]) as [Kategori, string[]][]) {
      for (const frasa of daftar) {
        const kunci = normalisasi(frasa);
        if (!m.has(kunci)) m.set(kunci, { tipe, kategori });
      }
    }
  }
  kamusCache = m;
  return m;
}

/** Coba potong imbuhan sederhana (dibunuh -> bunuh, pembantaian -> bantai). */
function cariAkar(kata: string, k: Map<string, InfoKata>): string | null {
  if (PENGECUALIAN.has(kata)) return null;
  const kandidat = [kata];
  for (const a of AKHIRAN) {
    if (kata.length > a.length + 2 && kata.endsWith(a)) kandidat.push(kata.slice(0, -a.length));
  }
  for (const c of [...kandidat]) {
    for (const p of AWALAN) {
      if (c.length > p.length + 2 && c.startsWith(p)) kandidat.push(c.slice(p.length));
    }
  }
  return kandidat.find((c) => k.has(c)) ?? null;
}

function escapeHtml(s: string): string {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

const KELAS: Record<TipeKata, string> = {
  slur: 'hl-hate', fisik: 'hl-hate', ancaman: 'hl-threat', kasar: 'hl-abuse', kelompok: 'hl-target', individu: 'hl-target',
};
const NAMA_KATEGORI: Record<Kategori, string> = {
  agama: 'Agama', ras: 'Ras / Etnis', fisik: 'Fisik', gender: 'Gender / Orientasi', lainnya: 'Lainnya',
};

export function deteksiKamus(teks: string): HasilKamus {
  const k = kamus();
  // Pisahkan teks jadi potongan: indeks ganjil = kata, genap = pemisah.
  const bagian = teks.split(/([\p{L}\p{N}@$]+)/u);
  const kata: { idx: number; norm: string }[] = [];
  for (let i = 1; i < bagian.length; i += 2) {
    const n = normalisasi(bagian[i]);
    if (n !== '') kata.push({ idx: i, norm: n });
  }

  const tanda = new Map<number, InfoKata>();
  let temuan: Temuan[] = [];
  const jumlah: Record<TipeKata, number> = { slur: 0, fisik: 0, ancaman: 0, kasar: 0, kelompok: 0, individu: 0 };
  const kategoriHina = new Set<Kategori>();
  const kategoriSasaran = new Set<Kategori>();

  for (let i = 0; i < kata.length; ) {
    let cocok: InfoKata | null = null;
    let panjang = 0;
    for (let len = 3; len >= 1; len--) {
      if (i + len > kata.length) continue;
      const frasa = kata.slice(i, i + len).map((x) => x.norm).join(' ');
      const info = k.get(frasa);
      if (info) { cocok = info; panjang = len; break; }
      if (len === 1) {
        const akar = cariAkar(frasa, k);
        if (akar) { cocok = k.get(akar)!; panjang = 1; }
      }
    }
    if (!cocok) { i++; continue; }

    const asli: string[] = [];
    for (let j = i; j < i + panjang; j++) {
      tanda.set(kata[j].idx, cocok);
      asli.push(bagian[kata[j].idx]);
    }
    jumlah[cocok.tipe]++;
    if (cocok.tipe === 'slur' || cocok.tipe === 'fisik') kategoriHina.add(cocok.kategori);
    else if (cocok.tipe === 'kelompok') kategoriSasaran.add(cocok.kategori);
    temuan.push({ kata: asli.join(' '), tipe: cocok.tipe });
    i += panjang;
  }

  // ---- Aturan klasifikasi ----
  const adaSasaran = jumlah.kelompok > 0 || jumlah.individu > 0;
  const adaNegatif = jumlah.slur + jumlah.kasar + jumlah.ancaman > 0;
  const fisikAktif = jumlah.fisik > 0 && (adaSasaran || adaNegatif) ? jumlah.fisik : 0;
  const negatif = jumlah.slur + jumlah.kasar + jumlah.ancaman + fisikAktif;

  let label: HasilKamus['label'];
  if (jumlah.slur > 0 || fisikAktif > 0 || (jumlah.kelompok > 0 && (jumlah.kasar > 0 || jumlah.ancaman > 0))) label = 'hate';
  else if (jumlah.kasar > 0 || jumlah.ancaman > 0) label = 'abusive';
  else label = 'aman';

  let tingkat = '-';
  let target = '-';
  let kategori: string[] = [];
  if (label === 'hate') {
    if (jumlah.ancaman > 0 || negatif >= 3) tingkat = 'Kuat';
    else if (negatif >= 2 || jumlah.kelompok > 0) tingkat = 'Sedang';
    else tingkat = 'Lemah';
    const hinaKelompok = [...kategoriHina].some((x) => x === 'agama' || x === 'ras' || x === 'lainnya');
    target = jumlah.kelompok > 0 || (hinaKelompok && jumlah.individu === 0) ? 'Kelompok' : 'Individu';
    const semua = new Set<Kategori>([...kategoriHina, ...kategoriSasaran]);
    if (semua.size > 1) semua.delete('lainnya');
    kategori = [...semua].map((x) => NAMA_KATEGORI[x]);
    if (!kategori.length) kategori = ['Lainnya'];
  } else if (label === 'abusive') {
    target = jumlah.individu > 0 ? 'Individu' : '-';
  }

  // ---- Susun teks dengan sorotan ----
  const html = bagian
    .map((potong, i) => {
      const aman = escapeHtml(potong);
      const info = tanda.get(i);
      const tampil = info && label !== 'aman' && !(info.tipe === 'fisik' && fisikAktif === 0);
      return tampil ? `<mark class="${KELAS[info.tipe]}">${aman}</mark>` : aman;
    })
    .join('');

  temuan = temuan.filter((t) => label !== 'aman' && (t.tipe !== 'fisik' || fisikAktif > 0));
  return { label, target, kategori, tingkat, temuan, html };
}

export const NAMA_TIPE: Record<TipeKata, string> = {
  slur: 'Ujaran kebencian', fisik: 'Hinaan fisik', ancaman: 'Ancaman', kasar: 'Kata kasar', kelompok: 'Sasaran kelompok', individu: 'Sasaran individu',
};
