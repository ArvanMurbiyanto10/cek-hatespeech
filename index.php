<?php
declare(strict_types=1);

/* =========================================================================
 *  CEK HATESPEECH
 *  Penilaian utama: model AI nahiar/hatespeech-abusive-xlm-roberta-v1
 *  yang dijalankan sebagai API Python (FastAPI di Google Colab).
 *  Kamus kata hanya dipakai sebagai lapisan bantu untuk menyorot kata
 *  dan memperkirakan sasaran/kategori, BUKAN untuk menentukan hasil.
 * ========================================================================= */

const MAKS_KARAKTER = 2000;

// Ganti dengan link dari Colab (langkah 5), tambahkan /predict di belakangnya.
// Link berubah setiap kali Colab dijalankan ulang.
const API_URL = 'https://standings-level-rover-throw.trycloudflare.com/predict';

// Identitas tugas akhir, tampil di bagian "Pengembang". GANTI sesuai data Anda.
const PENGEMBANG = [
    'judul'      => 'Rancang Bangun Aplikasi Web Deteksi Ujaran Kebencian Berbahasa Indonesia Menggunakan Model XLM-RoBERTa',
    'nama'       => 'Arvan Murbiyanto',
    'nim'        => '2311102074',
    'prodi'      => 'Teknik Informatika',
    'kampus'     => 'Telkom University Purwokerto',
    'pembimbing' => '', // isi nama dosen pembimbing; jika kosong, baris ini disembunyikan
    'tahun'      => '2026',
];

/**
 * Kamus kata. Format: 'kata atau frasa' => [tipe, kategori]
 *  tipe:
 *   - slur     : istilah merendahkan kelompok, langsung dianggap hate speech
 *   - fisik    : hinaan fisik, dianggap hate speech jika ada sasaran/kata kasar
 *   - ancaman  : ajakan kekerasan / ancaman
 *   - kasar    : kata kasar / makian umum (abusive)
 *   - kelompok : penanda sasaran kelompok (suku, agama, ras, gender, dll.)
 *   - individu : penanda sasaran individu (kata ganti orang)
 */
function kamus(): array
{
    static $kamus = null;
    if ($kamus !== null) {
        return $kamus;
    }

    $data = [
        'slur' => [
            'agama'   => ['kafir', 'kapir', 'murtad', 'penista agama', 'antek yahudi', 'thogut', 'togut',
                          'laknatullah', 'agama sesat', 'aliran sesat', 'teroris', 'onta', 'kaum sumbu pendek'],
            'ras'     => ['aseng', 'cokin', 'cina babi', 'china babi', 'babi cina', 'cina kafir', 'negro', 'nigga',
                          'keling', 'inlander', 'monyet papua', 'antek aseng', 'pribumi malas'],
            'gender'  => ['banci', 'bencong', 'maho', 'lekong', 'lonte', 'pelacur', 'perek', 'jablay', 'sundal',
                          'pecun', 'bispak', 'cabe cabean', 'homo menjijikkan', 'lgbt sampah'],
            'lainnya' => ['cebong', 'kadrun', 'kadal gurun', 'antek asing', 'pengkhianat bangsa', 'buzzerp'],
        ],
        'fisik' => [
            'fisik'   => ['cacat', 'autis', 'budek', 'pincang', 'cebol', 'kerdil', 'burik', 'dekil', 'gendut',
                          'jelek', 'sipit', 'pesek', 'muka babi', 'muka monyet'],
        ],
        'ancaman' => [
            'lainnya' => ['bunuh', 'bantai', 'basmi', 'usir', 'musnahkan', 'habisi', 'ganyang', 'penggal',
                          'bacok', 'perkosa', 'hancurkan', 'enyahkan', 'mampus', 'modar', 'mati aja', 'mati saja',
                          'mati lu', 'mati kau', 'bakar aja', 'bakar saja', 'bakar hidup hidup', 'gantung aja',
                          'gantung saja', 'tembak mati', 'minggat'],
        ],
        'kasar' => [
            'lainnya' => ['anjing', 'anjir', 'anjrit', 'njing', 'bangsat', 'bajingan', 'keparat', 'kampret',
                          'goblok', 'goblog', 'tolol', 'bego', 'bodoh', 'dungu', 'idiot', 'gila', 'sinting',
                          'brengsek', 'tai', 'taik', 'sampah', 'babi', 'monyet', 'setan', 'iblis', 'kontol',
                          'memek', 'ngentot', 'entot', 'jancok', 'jancuk', 'cok', 'asu', 'celeng', 'kunyuk',
                          'sialan', 'laknat', 'kampungan', 'udik', 'pantek', 'pukimak', 'bacot', 'bangke',
                          'bangkai', 'kurang ajar', 'biadab', 'najis', 'busuk', 'bedebah', 'jahanam'],
        ],
        'kelompok' => [
            'ras'     => ['cina', 'china', 'tionghoa', 'tiongkok', 'papua', 'jawa', 'batak', 'sunda', 'madura',
                          'arab', 'pribumi', 'pendatang', 'etnis', 'suku', 'ras', 'bule'],
            'agama'   => ['yahudi', 'kristen', 'kristiani', 'katolik', 'islam', 'muslim', 'hindu', 'buddha',
                          'budha', 'konghucu', 'ateis', 'atheis', 'syiah', 'ahmadiyah', 'umat', 'agama'],
            'gender'  => ['lgbt', 'gay', 'lesbi', 'lesbian', 'homo', 'transgender', 'waria', 'perempuan',
                          'wanita', 'cewek', 'betina'],
            'lainnya' => ['kalian', 'mereka', 'kaum', 'golongan', 'komunis', 'pki', 'partai'],
        ],
        'individu' => [
            'lainnya' => ['kamu', 'kau', 'engkau', 'lu', 'lo', 'loe', 'elu', 'elo', 'lw', 'dia', 'ente',
                          'situ', 'anda'],
        ],
    ];

    // Urutan prioritas jika satu kata muncul di beberapa tipe.
    $prioritas = ['slur', 'ancaman', 'kasar', 'fisik', 'kelompok', 'individu'];
    $kamus = [];
    foreach ($prioritas as $tipe) {
        foreach ($data[$tipe] as $kategori => $daftar) {
            foreach ($daftar as $frasa) {
                $kunci = normalisasi($frasa);
                if (!isset($kamus[$kunci])) {
                    $kamus[$kunci] = ['tipe' => $tipe, 'kategori' => $kategori];
                }
            }
        }
    }
    return $kamus;
}

/** Kata yang tidak boleh dicocokkan lewat pemotongan imbuhan (idiom, dll.). */
const PENGECUALIAN = ['membabi', 'pantai', 'cocok'];

/** Huruf kecil, ubah angka "alay" ke huruf, rapatkan huruf berulang (anjiiing -> anjing). */
function normalisasi(string $teks): string
{
    $teks = function_exists('mb_strtolower') ? mb_strtolower($teks, 'UTF-8') : strtolower($teks);
    $teks = strtr($teks, ['1' => 'i', '0' => 'o', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's']);
    $teks = preg_replace('/(\p{L})\1+/u', '$1', $teks) ?? $teks;
    return trim(preg_replace('/[^\p{L}\s]+/u', '', $teks) ?? $teks);
}

/** Coba potong imbuhan sederhana (dibunuh -> bunuh, pembantaian -> bantai). */
function cariAkar(string $kata, array $kamus): ?string
{
    if (in_array($kata, PENGECUALIAN, true)) {
        return null;
    }
    $akhiran = ['nya', 'lah', 'kah', 'kan', 'an', 'i', 'mu', 'ku'];
    $awalan  = ['meng', 'meny', 'mem', 'men', 'me', 'peng', 'pem', 'pen', 'pe', 'di', 'ter', 'ber', 'ke', 'se'];

    $kandidat = [$kata];
    foreach ($akhiran as $a) {
        if (strlen($kata) > strlen($a) + 2 && str_ends_with($kata, $a)) {
            $kandidat[] = substr($kata, 0, -strlen($a));
        }
    }
    foreach ($kandidat as $k) {
        foreach ($awalan as $p) {
            if (strlen($k) > strlen($p) + 2 && str_starts_with($k, $p)) {
                $kandidat[] = substr($k, strlen($p));
            }
        }
    }
    foreach ($kandidat as $k) {
        if (isset($kamus[$k])) {
            return $k;
        }
    }
    return null;
}

/**
 * LAPISAN BANTU berbasis kamus (tidak menentukan hasil akhir).
 * Mengembalikan: label, skor, target, kategori[], tingkat, temuan[], html.
 * Yang dipakai halaman hanya target, kategori, temuan, dan html.
 */
function deteksiKamus(string $teks): array
{
    $kamus = kamus();

    // Pisahkan teks jadi potongan: indeks ganjil = kata, genap = pemisah.
    $bagian = preg_split('/([\p{L}\p{N}@$]+)/u', $teks, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$teks];
    $kata = [];
    for ($i = 1; $i < count($bagian); $i += 2) {
        $n = normalisasi($bagian[$i]);
        if ($n !== '') {
            $kata[] = ['idx' => $i, 'norm' => $n];
        }
    }

    $tanda = [];   // idx bagian => info kamus
    $temuan = [];
    $jumlah = ['slur' => 0, 'fisik' => 0, 'ancaman' => 0, 'kasar' => 0, 'kelompok' => 0, 'individu' => 0];
    $kategoriHina = [];
    $kategoriSasaran = [];

    $total = count($kata);
    for ($i = 0; $i < $total;) {
        $cocok = null;
        $panjang = 0;
        for ($len = 3; $len >= 1; $len--) {
            if ($i + $len > $total) {
                continue;
            }
            $frasa = implode(' ', array_map(fn($k) => $k['norm'], array_slice($kata, $i, $len)));
            if (isset($kamus[$frasa])) {
                $cocok = $kamus[$frasa];
                $panjang = $len;
                break;
            }
            if ($len === 1 && ($akar = cariAkar($frasa, $kamus)) !== null) {
                $cocok = $kamus[$akar];
                $panjang = 1;
            }
        }

        if ($cocok === null) {
            $i++;
            continue;
        }

        $asli = [];
        for ($j = $i; $j < $i + $panjang; $j++) {
            $tanda[$kata[$j]['idx']] = $cocok;
            $asli[] = $bagian[$kata[$j]['idx']];
        }
        $jumlah[$cocok['tipe']]++;
        if (in_array($cocok['tipe'], ['slur', 'fisik'], true)) {
            $kategoriHina[$cocok['kategori']] = true;
        } elseif ($cocok['tipe'] === 'kelompok') {
            $kategoriSasaran[$cocok['kategori']] = true;
        }
        $temuan[] = ['kata' => implode(' ', $asli), 'tipe' => $cocok['tipe']];
        $i += $panjang;
    }

    // ---- Aturan klasifikasi ----
    $adaSasaran = $jumlah['kelompok'] > 0 || $jumlah['individu'] > 0;
    $adaNegatif = $jumlah['slur'] + $jumlah['kasar'] + $jumlah['ancaman'] > 0;
    $fisikAktif = ($jumlah['fisik'] > 0 && ($adaSasaran || $adaNegatif)) ? $jumlah['fisik'] : 0;
    $negatif = $jumlah['slur'] + $jumlah['kasar'] + $jumlah['ancaman'] + $fisikAktif;

    if ($jumlah['slur'] > 0 || $fisikAktif > 0 || ($jumlah['kelompok'] > 0 && ($jumlah['kasar'] > 0 || $jumlah['ancaman'] > 0))) {
        $label = 'hate';
    } elseif ($jumlah['kasar'] > 0 || $jumlah['ancaman'] > 0) {
        $label = 'abusive';
    } else {
        $label = 'aman';
    }

    $tingkat = '-';
    $target = '-';
    $kategori = [];
    if ($label === 'hate') {
        if ($jumlah['ancaman'] > 0 || $negatif >= 3) {
            $tingkat = 'Kuat';
            $skor = min(98, 90 + 2 * $negatif);
        } elseif ($negatif >= 2 || $jumlah['kelompok'] > 0) {
            $tingkat = 'Sedang';
            $skor = 81;
        } else {
            $tingkat = 'Lemah';
            $skor = 68;
        }
        $hinaKelompok = array_intersect(array_keys($kategoriHina), ['agama', 'ras', 'lainnya']);
        $target = ($jumlah['kelompok'] > 0 || ($hinaKelompok && $jumlah['individu'] === 0)) ? 'Kelompok' : 'Individu';
        $nama = ['agama' => 'Agama', 'ras' => 'Ras / Etnis', 'fisik' => 'Fisik', 'gender' => 'Gender / Orientasi', 'lainnya' => 'Lainnya'];
        foreach (array_keys($kategoriHina + $kategoriSasaran) as $k) {
            $kategori[$k] = $nama[$k];
        }
        if (count($kategori) > 1) {
            unset($kategori['lainnya']);
        }
        $kategori = array_values($kategori) ?: ['Lainnya'];
    } elseif ($label === 'abusive') {
        $skor = min(85, 45 + 10 * $negatif);
        $target = $jumlah['individu'] > 0 ? 'Individu' : '-';
    } else {
        $skor = 4;
    }

    // ---- Susun teks dengan sorotan ----
    $kelasTipe = ['slur' => 'hl-hate', 'fisik' => 'hl-hate', 'ancaman' => 'hl-threat', 'kasar' => 'hl-abuse',
                  'kelompok' => 'hl-target', 'individu' => 'hl-target'];
    $html = '';
    foreach ($bagian as $i => $potong) {
        $aman = htmlspecialchars($potong, ENT_QUOTES, 'UTF-8');
        $info = $tanda[$i] ?? null;
        $tampil = $info !== null && $label !== 'aman'
            && !($info['tipe'] === 'fisik' && $fisikAktif === 0);
        $html .= $tampil ? '<mark class="' . $kelasTipe[$info['tipe']] . '">' . $aman . '</mark>' : $aman;
    }

    // Hanya tampilkan temuan yang relevan.
    $temuan = array_values(array_filter($temuan, function ($t) use ($label, $fisikAktif) {
        if ($label === 'aman') return false;
        if ($t['tipe'] === 'fisik') return $fisikAktif > 0;
        return true;
    }));

    return compact('label', 'skor', 'target', 'kategori', 'tingkat', 'temuan', 'html');
}

/* ======================= Pemanggilan model AI ======================= */

/** Kirim teks ke API model (FastAPI di Colab) dan kembalikan respons JSON-nya. */
function panggilModel(string $teks): array
{
    $ch = curl_init(API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(['teks' => $teks]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $respon = curl_exec($ch);
    $kode   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $galat  = curl_error($ch);
    curl_close($ch);

    if ($respon === false || $kode !== 200) {
        throw new RuntimeException($galat !== '' ? $galat : 'Server model membalas kode ' . $kode);
    }
    $data = json_decode((string)$respon, true);
    if (!isset($data['skor']['HATESPEECH'], $data['skor']['ABUSIVE'], $data['status'])) {
        throw new RuntimeException('Format respons model tidak dikenali.');
    }
    return $data;
}

/**
 * Analisis utama. Label, skor, dan tingkat berasal dari MODEL AI.
 * Sasaran, kategori, dan sorotan kata berasal dari kamus (perkiraan bantu).
 */
function deteksiHatespeech(string $teks): array
{
    $model = panggilModel($teks);
    $bantu = deteksiKamus($teks);

    $peta = [
        'INDIKASI_KUAT'     => ['hate', 'Kuat'],
        'PERLU_DITINJAU'    => ['tinjau', 'Perlu ditinjau'],
        'BAHASA_KASAR'      => ['abusive', '-'],
        'TIDAK_TERINDIKASI' => ['aman', '-'],
    ];
    [$label, $tingkat] = $peta[$model['status']] ?? ['aman', '-'];

    return [
        'label'      => $label,
        'skor'       => (int)round($model['skor']['HATESPEECH'] * 100),
        'skorKasar'  => (int)round($model['skor']['ABUSIVE'] * 100),
        'tingkat'    => $tingkat,
        'penjelasan' => (string)($model['penjelasan'] ?? ''),
        'target'     => $bantu['target'],
        'kategori'   => $bantu['kategori'],
        'temuan'     => $bantu['temuan'],
        'html'       => $bantu['html'],
    ];
}

/* ============================ Proses form ============================ */
$teks = '';
$hasil = null;
$galat = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $teks = trim((string)($_POST['teks'] ?? ''));
    $panjangTeks = function_exists('mb_strlen') ? mb_strlen($teks, 'UTF-8') : strlen($teks);
    if ($teks === '') {
        $galat = 'Teks masih kosong. Tulis atau tempel kalimat yang ingin dicek.';
    } elseif ($panjangTeks > MAKS_KARAKTER) {
        $galat = 'Teks terlalu panjang (maksimal ' . MAKS_KARAKTER . ' karakter).';
    } else {
        try {
            $hasil = deteksiHatespeech($teks);
        } catch (RuntimeException $ex) {
            $galat = 'Model AI tidak dapat dihubungi. Pastikan Colab masih berjalan dan API_URL sudah benar. ('
                   . $ex->getMessage() . ')';
        }
    }
}

$infoLabel = [
    'hate'    => ['judul' => 'Terindikasi Hate Speech', 'sub' => 'Teks mengandung ujaran kebencian.', 'ikon' => '!'],
    'tinjau'  => ['judul' => 'Perlu Ditinjau', 'sub' => 'Model belum yakin. Teks ini sebaiknya diperiksa manusia.', 'ikon' => '?'],
    'abusive' => ['judul' => 'Ujaran Kasar (Abusive)', 'sub' => 'Teks mengandung kata kasar, tetapi tidak menyasar kelompok tertentu.', 'ikon' => '!'],
    'aman'    => ['judul' => 'Bukan Hate Speech', 'sub' => 'Tidak ditemukan indikasi ujaran kebencian.', 'ikon' => '✓'],
];
$namaTipe = ['slur' => 'Ujaran kebencian', 'fisik' => 'Hinaan fisik', 'ancaman' => 'Ancaman / hasutan',
             'kasar' => 'Kata kasar', 'kelompok' => 'Sasaran kelompok', 'individu' => 'Sasaran individu'];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Slot foto. Taruh file di assets/img/<nama>.jpg (atau .jpeg/.png/.webp)
 * maka foto otomatis tampil; jika belum ada, tampil kotak placeholder.
 */
/** Cek apakah file foto untuk slot tertentu sudah ada di assets/img. */
function adaFoto(string $nama): bool
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file(__DIR__ . "/assets/img/{$nama}.{$ext}")) {
            return true;
        }
    }
    return false;
}

/** Inisial nama, mis. "Arvan Murbiyanto" -> "AM". */
function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim(preg_replace('/[^\p{L}\s]/u', '', $nama))) ?: [];
    $huruf = array_map(fn($k) => mb_strtoupper(mb_substr($k, 0, 1)), array_slice(array_filter($kata), 0, 2));
    return implode('', $huruf) ?: '?';
}

function foto(string $nama, string $alt, string $saran, string $rasio = '4/3', string $kelas = ''): string
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $path = __DIR__ . "/assets/img/{$nama}.{$ext}";
        if (is_file($path)) {
            return '<figure class="foto ' . $kelas . '" style="aspect-ratio:' . $rasio . '">'
                 . '<img src="assets/img/' . $nama . '.' . $ext . '?v=' . filemtime($path) . '" alt="' . e($alt) . '" loading="lazy">'
                 . '</figure>';
        }
    }
    return '<figure class="foto foto-kosong ' . $kelas . '" style="aspect-ratio:' . $rasio . '" role="img" aria-label="Slot foto: ' . e($saran) . '">'
         . '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>'
         . '<figcaption><strong>' . e($saran) . '</strong><span>assets/img/' . $nama . '.jpg &middot; rasio ' . str_replace('/', ':', $rasio) . '</span></figcaption>'
         . '</figure>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Hatespeech</title>
    <meta name="description" content="Cek apakah sebuah kalimat mengandung ujaran kebencian (hate speech) atau bahasa kasar.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --bg: #ffffff;
            --bg-soft: #f6f6f3;
            --text: #1d1d1f;
            --muted: #5f6368;
            --line: #e4e4df;
            --line-strong: #cfcfc8;
            --accent: #1f4e79;
            --accent-hover: #173d60;
            --accent-soft: #e9f0f7;
            --hate: #b42318;
            --hate-soft: #fef3f2;
            --abuse: #b54708;
            --abuse-soft: #fffaeb;
            --safe: #067647;
            --safe-soft: #ecfdf3;
            --target: #175cd3;
            --target-soft: #eff8ff;
            --radius: 8px;
            --serif: 'Source Serif 4', Georgia, 'Times New Roman', serif;
            --sans: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        [id] { scroll-margin-top: 72px; }
        body { margin: 0; font-family: var(--sans); font-size: 16px; line-height: 1.65; color: var(--text); background: var(--bg); -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; display: block; }
        a { color: var(--accent); }
        h1, h2, h3, h4 { margin: 0; line-height: 1.25; }
        .container { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
        @media (max-width: 600px) { .container { padding: 0 16px; } }

        /* ---------- Navigasi ---------- */
        .nav { position: sticky; top: 0; z-index: 50; background: rgba(255, 255, 255, .95); backdrop-filter: saturate(180%) blur(8px); -webkit-backdrop-filter: saturate(180%) blur(8px); border-bottom: 1px solid var(--line); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; height: 64px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; color: var(--text); text-decoration: none; font-weight: 700; font-size: 16px; letter-spacing: -.01em; }
        .brand svg { color: var(--accent); }
        .nav-links { display: flex; gap: 2px; list-style: none; margin: 0; padding: 0; }
        .nav-links a { display: block; padding: 6px 10px; color: var(--muted); text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 6px; }
        .nav-links a:hover { color: var(--text); background: var(--bg-soft); }
        .nav-links a.aktif { color: var(--accent); background: var(--accent-soft); }
        .nav-toggle { display: none; width: 40px; height: 40px; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--line-strong); border-radius: 6px; color: var(--text); cursor: pointer; }
        @media (max-width: 1020px) {
            .nav-toggle { display: inline-flex; }
            .nav-links { display: none; position: absolute; top: 100%; left: 0; right: 0; flex-direction: column; background: #fff; border-bottom: 1px solid var(--line); padding: 8px 16px 16px; }
            .nav.buka .nav-links { display: flex; }
            .nav-links a { padding: 10px 8px; }
        }

        /* ---------- Tombol ---------- */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 20px; border-radius: 6px; border: 1px solid transparent; font: inherit; font-size: 15px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-ghost { background: #fff; color: var(--text); border-color: var(--line-strong); }
        .btn-ghost:hover { border-color: var(--text); }

        /* ---------- Hero ---------- */
        .hero { padding: 64px 0 72px; border-bottom: 1px solid var(--line); }
        .hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 56px; align-items: center; }
        .kicker { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; font-size: 14px; color: var(--muted); }
        .kicker::before { content: ''; width: 24px; height: 2px; background: var(--accent); }
        .hero h1 { font-family: var(--serif); font-size: clamp(40px, 6vw, 60px); font-weight: 700; letter-spacing: -.02em; }
        .hero-lead { font-size: 18px; color: var(--muted); margin: 16px 0 28px; max-width: 520px; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .fakta { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--line); }
        .fakta div { padding-right: 16px; }
        .fakta strong { display: block; font-family: var(--serif); font-size: 26px; font-weight: 700; }
        .fakta span { font-size: 13.5px; color: var(--muted); }
        @media (max-width: 860px) { .hero { padding: 40px 0 48px; } .hero-grid { grid-template-columns: 1fr; gap: 36px; } }

        /* ---------- Slot foto ---------- */
        .foto { margin: 0; width: 100%; border-radius: var(--radius); overflow: hidden; background: var(--bg-soft); }
        .foto img { width: 100%; height: 100%; object-fit: cover; }
        .foto-kosong { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 20px; text-align: center; border: 1.5px dashed var(--line-strong); color: var(--muted); }
        .foto-kosong svg { opacity: .6; }
        .foto-kosong figcaption strong { display: block; font-size: 14px; font-weight: 600; color: var(--text); }
        .foto-kosong figcaption span { font-size: 12px; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }
        .foto-logo { width: 88px; flex: none; }
        .foto-logo.foto-kosong { padding: 6px; gap: 4px; }
        .foto-logo.foto-kosong svg { width: 22px; height: 22px; }
        .foto-logo figcaption strong { font-size: 11px; font-weight: 500; }
        .foto-logo figcaption span { display: none; }
        .foto-profil { max-width: 240px; }
        .foto-inisial { display: grid; place-items: center; background: var(--accent); color: #fff; font-family: var(--serif); font-size: 72px; font-weight: 700; letter-spacing: .02em; }
        .foto-logo:not(.foto-kosong) { background: #fff; }
        .foto-logo img { object-fit: contain; padding: 6px; }
        .foto-layar { border: 1px solid var(--line); background: #fff; }
        .foto-layar img { object-fit: contain; }
        .keterangan code { font-size: 12.5px; background: #fff; border: 1px solid var(--line); border-radius: 4px; padding: 1px 5px; }

        /* ---------- Bagian ---------- */
        .sec { padding: 80px 0; }
        .sec.soft { background: var(--bg-soft); }
        .sec-head { max-width: 720px; margin-bottom: 40px; }
        .sec-head h2 { font-family: var(--serif); font-size: clamp(28px, 4vw, 38px); font-weight: 700; letter-spacing: -.015em; }
        .sec-head p { margin: 12px 0 0; font-size: 17px; color: var(--muted); }
        .sub { font-family: var(--serif); font-size: 22px; font-weight: 700; margin: 56px 0 18px; }
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }
        .prose p { margin: 0 0 14px; color: var(--muted); }
        .prose p:last-child { margin-bottom: 0; }
        .prose strong { color: var(--text); }
        .grid { display: grid; gap: 20px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 960px) { .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 860px) { .sec { padding: 56px 0; } .split { grid-template-columns: 1fr; gap: 28px; } }
        @media (max-width: 600px) { .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; } }
        .card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; }
        .card h3 { font-size: 17px; font-weight: 600; margin-bottom: 8px; }
        .card p { margin: 0; color: var(--muted); font-size: 15px; }
        .card ul, .card ol { margin: 0; padding-left: 20px; color: var(--muted); font-size: 15px; }
        .card li + li { margin-top: 6px; }
        .card li strong { color: var(--text); }
        .card.k-hate { border-left: 3px solid var(--hate); }
        .card.k-abuse { border-left: 3px solid var(--abuse); }
        .card.k-safe { border-left: 3px solid var(--safe); }
        .tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag { font-size: 13.5px; padding: 4px 10px; border: 1px solid var(--line-strong); border-radius: 4px; background: #fff; color: var(--text); }
        .contoh { margin-top: 14px; padding: 10px 12px; border-radius: 6px; background: var(--bg-soft); font-size: 14px; color: var(--text); }
        .contoh span { display: block; font-size: 12px; color: var(--muted); margin-bottom: 2px; }
        .keterangan { margin: 14px 0 0; font-size: 13.5px; color: var(--muted); }

        /* ---------- Cara kerja ---------- */
        .step-no { font-family: var(--serif); font-size: 30px; font-weight: 700; color: var(--accent); line-height: 1; margin-bottom: 14px; }
        .alur { display: flex; align-items: stretch; gap: 10px; }
        .alur-node { flex: 1; padding: 16px 14px; background: #fff; border: 1px solid var(--line); border-radius: var(--radius); text-align: center; }
        .alur-node strong { display: block; font-size: 15px; }
        .alur-node small { display: block; margin-top: 4px; font-size: 12.5px; line-height: 1.45; color: var(--muted); }
        .alur-panah { align-self: center; color: var(--line-strong); font-size: 22px; }
        @media (max-width: 760px) { .alur { flex-direction: column; } .alur-panah { transform: rotate(90deg); } }
        .status { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; font-weight: 600; line-height: 1.4; }
        .status .dot { margin-top: 6px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; flex: none; display: inline-block; }

        /* ---------- Dasar hukum ---------- */
        .grup-hukum { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 48px 0 16px; padding-bottom: 10px; border-bottom: 1px solid var(--line); }
        .grup-hukum h3 { font-family: var(--serif); font-size: 22px; font-weight: 700; }
        .grup-hukum span { font-size: 13.5px; color: var(--muted); }
        .pasal { display: flex; flex-direction: column; gap: 12px; background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; }
        .pasal h4 { font-family: var(--serif); font-size: 21px; font-weight: 700; }
        .pasal .pasal-topik { margin: -6px 0 0; font-size: 14px; font-weight: 600; color: var(--accent); }
        .pasal p { margin: 0; color: var(--muted); font-size: 15px; }
        .pasal h5 { margin: 4px 0 0; font-size: 13.5px; font-weight: 600; color: var(--text); }
        .pasal ol { margin: 0; padding-left: 20px; color: var(--muted); font-size: 14.5px; }
        .pasal ol li + li { margin-top: 3px; }
        .ancaman { margin-top: auto; padding: 12px 14px; border-radius: 6px; background: var(--hate-soft); color: var(--hate); font-size: 14.5px; }
        .ancaman span { display: block; font-size: 12px; font-weight: 600; text-transform: none; opacity: .85; }
        .ancaman strong { font-weight: 600; }
        .catatan-pasal { font-size: 13.5px !important; padding-left: 12px; border-left: 2px solid var(--line-strong); }
        .kaitan { font-size: 13.5px !important; color: var(--text) !important; }
        .kaitan b { color: var(--accent); }
        .tabel-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: var(--radius); background: #fff; }
        .tabel-ringkas { width: 100%; border-collapse: collapse; font-size: 14.5px; min-width: 640px; }
        .tabel-ringkas th, .tabel-ringkas td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: top; }
        .tabel-ringkas thead th { background: var(--bg-soft); font-size: 13px; font-weight: 600; color: var(--muted); }
        .tabel-ringkas tr:last-child td { border-bottom: 0; }
        .tabel-ringkas td:first-child { font-weight: 600; white-space: nowrap; }

        /* ---------- Form cek ---------- */
        .cek-wrap { max-width: 780px; }
        .cek-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 28px; }
        @media (max-width: 600px) { .cek-card { padding: 20px; } }
        .field-label { display: block; margin-bottom: 8px; font-weight: 600; }
        textarea { width: 100%; min-height: 170px; resize: vertical; padding: 14px 16px; border: 1px solid var(--line-strong); border-radius: 6px; background: #fff; color: var(--text); font: inherit; font-size: 16px; line-height: 1.6; transition: border-color .15s, box-shadow .15s; }
        textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        .meta-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 10px; font-size: 13px; color: var(--muted); }
        .examples { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .chip { padding: 3px 10px; border: 1px solid var(--line-strong); border-radius: 4px; background: #fff; color: var(--muted); font: inherit; font-size: 13px; cursor: pointer; }
        .chip:hover { color: var(--accent); border-color: var(--accent); }
        .actions { display: flex; gap: 10px; margin-top: 18px; }
        .actions .btn-primary { flex: 1; }
        .alert { margin-top: 16px; padding: 12px 14px; border-radius: 6px; background: var(--hate-soft); color: var(--hate); font-size: 14px; font-weight: 500; }
        .disclaimer { margin: 14px 0 0; font-size: 13px; color: var(--muted); }

        .result { margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--line); }
        .verdict { display: flex; align-items: flex-start; gap: 14px; padding: 18px 20px; border-radius: 6px; border-left: 4px solid; }
        .verdict.hate { background: var(--hate-soft); border-color: var(--hate); }
        .verdict.tinjau, .verdict.abusive { background: var(--abuse-soft); border-color: var(--abuse); }
        .verdict.aman { background: var(--safe-soft); border-color: var(--safe); }
        .verdict-icon { flex: none; width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; color: #fff; font-weight: 700; font-size: 17px; }
        .hate .verdict-icon { background: var(--hate); }
        .tinjau .verdict-icon, .abusive .verdict-icon { background: var(--abuse); }
        .aman .verdict-icon { background: var(--safe); }
        .verdict h3 { font-size: 19px; font-weight: 700; }
        .hate h3 { color: var(--hate); }
        .tinjau h3, .abusive h3 { color: var(--abuse); }
        .aman h3 { color: var(--safe); }
        .verdict p { margin: 2px 0 0; font-size: 14px; color: var(--muted); }
        .meter { margin-top: 18px; }
        .meter-head { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; font-weight: 500; }
        .meter-track { height: 8px; border-radius: 999px; background: var(--bg-soft); border: 1px solid var(--line); overflow: hidden; }
        .meter-fill { width: 0; height: 100%; border-radius: 999px; transition: width .8s cubic-bezier(.2, .8, .2, 1); }
        .hate-fill { background: var(--hate); }
        .tinjau-fill, .abusive-fill { background: var(--abuse); }
        .aman-fill { background: var(--safe); }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 18px; }
        .stat { padding: 12px 14px; border: 1px solid var(--line); border-radius: 6px; }
        .stat small { display: block; font-size: 12.5px; color: var(--muted); }
        .stat strong { font-size: 15px; font-weight: 600; }
        @media (max-width: 560px) { .stats { grid-template-columns: 1fr; } }
        .catatan { margin: 8px 0 0; font-size: 13px; color: var(--muted); }
        .section-title { margin: 22px 0 8px; font-size: 15px; font-weight: 600; }
        .highlighted { padding: 14px 16px; border: 1px solid var(--line); border-radius: 6px; background: var(--bg-soft); white-space: pre-wrap; word-wrap: break-word; }
        mark { padding: 1px 3px; border-radius: 3px; font-weight: 600; }
        .hl-hate { background: var(--hate-soft); color: var(--hate); box-shadow: inset 0 -2px 0 var(--hate); }
        .hl-threat { background: var(--hate); color: #fff; }
        .hl-abuse { background: var(--abuse-soft); color: var(--abuse); box-shadow: inset 0 -2px 0 var(--abuse); }
        .hl-target { background: var(--target-soft); color: var(--target); }
        .legend { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 10px; font-size: 12.5px; color: var(--muted); }
        .legend span { display: inline-flex; align-items: center; gap: 6px; }
        .legend .dot { border-radius: 2px; }
        .temuan { display: flex; flex-wrap: wrap; gap: 8px; }
        .temuan span { padding: 4px 10px; border: 1px solid var(--line); border-radius: 4px; font-size: 13px; }
        .spinner { display: none; width: 16px; height: 16px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: putar .7s linear infinite; }
        .loading .spinner { display: inline-block; }
        @keyframes putar { to { transform: rotate(360deg); } }

        /* ---------- Model ---------- */
        .spek { width: 100%; border-collapse: collapse; font-size: 15px; }
        .spek th, .spek td { padding: 11px 0; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
        .spek th { width: 38%; padding-right: 16px; font-weight: 500; color: var(--muted); }
        .spek tr:last-child th, .spek tr:last-child td { border-bottom: 0; }
        .spek a { word-break: break-all; }
        .metrik strong { display: block; font-family: var(--serif); font-size: 32px; font-weight: 700; line-height: 1.2; }
        .metrik span { font-size: 14px; color: var(--muted); }

        /* ---------- FAQ ---------- */
        .faq { max-width: 820px; border-top: 1px solid var(--line); }
        .faq details { border-bottom: 1px solid var(--line); }
        .faq summary { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 18px 0; font-weight: 600; cursor: pointer; list-style: none; }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary::after { content: '+'; flex: none; font-size: 22px; font-weight: 400; line-height: 1; color: var(--muted); }
        .faq details[open] summary::after { content: '\2212'; }
        .faq details p { margin: 0 0 18px; color: var(--muted); max-width: 720px; }

        /* ---------- Pengembang ---------- */
        .profil { display: grid; grid-template-columns: 240px 1fr; gap: 40px; align-items: start; }
        @media (max-width: 700px) { .profil { grid-template-columns: 1fr; gap: 24px; } }
        .judul-ta { font-family: var(--serif); font-size: 22px; font-weight: 700; line-height: 1.4; margin: 0 0 20px; }
        .kampus { display: flex; align-items: center; gap: 16px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); color: var(--muted); font-size: 14px; }
        .kampus strong { display: block; color: var(--text); font-size: 15px; }

        /* ---------- Footer ---------- */
        footer { padding: 40px 0 28px; border-top: 1px solid var(--line); background: var(--bg-soft); color: var(--muted); font-size: 14px; }
        .footer-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 28px; }
        @media (max-width: 700px) { .footer-grid { grid-template-columns: 1fr; } }
        .footer-links { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 20px; list-style: none; margin: 0; padding: 0; }
        .footer-links a { color: var(--muted); text-decoration: none; }
        .footer-links a:hover { color: var(--text); }
        .kredit { margin: 10px 0 0; font-size: 12px; }
        .kredit a { color: var(--muted); }
        .footer-bawah { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-top: 28px; padding-top: 18px; border-top: 1px solid var(--line); font-size: 13px; }
    </style>
</head>
<body>

<nav class="nav" id="nav">
    <div class="container nav-inner">
        <a href="#beranda" class="brand">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
            Cek Hatespeech
        </a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Buka menu" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <ul class="nav-links">
            <li><a href="#tentang">Tentang</a></li>
            <li><a href="#hate-speech">Hate Speech</a></li>
            <li><a href="#dasar-hukum">Dasar Hukum</a></li>
            <li><a href="#cara-kerja">Cara Kerja</a></li>
            <li><a href="#cek">Cek Teks</a></li>
            <li><a href="#model">Model</a></li>
            <li><a href="#faq">FAQ</a></li>
            <li><a href="#pengembang">Pengembang</a></li>
        </ul>
    </div>
</nav>

<!-- ============================== HERO ============================== -->
<header class="hero" id="beranda">
    <div class="container hero-grid">
        <div>
            <p class="kicker">Deteksi ujaran kebencian berbahasa Indonesia</p>
            <h1>Cek Hatespeech</h1>
            <p class="hero-lead">
                Periksa kalimat, komentar, atau postingan media sosial untuk mengetahui apakah
                mengandung ujaran kebencian atau bahasa kasar. Teks dianalisis oleh model AI XLM-RoBERTa.
            </p>
            <div class="hero-actions">
                <a href="#cek" class="btn btn-primary">Mulai Cek Teks</a>
                <a href="#tentang" class="btn btn-ghost">Pelajari Dulu</a>
            </div>
            <div class="fakta">
                <div><strong>2 label</strong><span>Hate speech &amp; bahasa kasar</span></div>
                <div><strong>10 bahasa</strong><span>Indonesia, daerah &amp; Inggris</span></div>
                <div><strong>&le; 2.000</strong><span>karakter per pengecekan</span></div>
            </div>
        </div>
        <?= foto('hero', 'Seseorang membaca komentar media sosial di ponsel', 'Orang membaca komentar media sosial di ponsel', '4/3') ?>
    </div>
</header>

<!-- ============================= TENTANG ============================= -->
<section class="sec" id="tentang">
    <div class="container">
        <div class="split">
            <div>
                <div class="sec-head" style="margin-bottom:24px">
                    <h2>Tentang Cek Hatespeech</h2>
                </div>
                <div class="prose">
                    <p>
                        <strong>Cek Hatespeech</strong> adalah aplikasi web untuk mendeteksi ujaran kebencian
                        (<em>hate speech</em>) dan bahasa kasar (<em>abusive language</em>) pada teks secara otomatis.
                        Penilaiannya dilakukan oleh model kecerdasan buatan berbasis arsitektur Transformer
                        (XLM-RoBERTa) yang mampu memahami bahasa Indonesia, termasuk bahasa gaul dan beberapa bahasa daerah.
                    </p>
                    <p>
                        Media sosial memudahkan siapa saja menyampaikan pendapat, tetapi juga menjadi tempat tersebarnya
                        ujaran yang menyerang seseorang atau kelompok berdasarkan suku, agama, ras, antargolongan, gender,
                        maupun kondisi fisik. Jumlah konten yang sangat banyak, ragam penulisan (singkatan, bahasa alay,
                        campuran bahasa daerah), serta terbatasnya tenaga moderator membuat pemeriksaan manual sulit
                        dilakukan. Aplikasi ini hadir sebagai alat bantu untuk mengenali ujaran kebencian secara cepat.
                    </p>
                </div>
            </div>
            <?= foto('tentang', 'Ilustrasi percakapan di media sosial', 'Ilustrasi percakapan / kolom komentar media sosial', '4/3') ?>
        </div>

        <h3 class="sub">Tujuan</h3>
        <div class="grid grid-4">
            <div class="card"><div class="step-no">1</div><p>Membangun aplikasi web yang mengklasifikasikan teks menjadi ujaran kebencian, bahasa kasar, atau aman.</p></div>
            <div class="card"><div class="step-no">2</div><p>Menerapkan model deep learning XLM-RoBERTa yang telah di-<em>fine-tune</em> untuk deteksi ujaran kebencian.</p></div>
            <div class="card"><div class="step-no">3</div><p>Menyajikan hasil deteksi yang mudah dipahami, lengkap dengan skor dan kata yang perlu diperhatikan.</p></div>
            <div class="card"><div class="step-no">4</div><p>Meningkatkan kesadaran masyarakat agar lebih bijak berkomunikasi di ruang digital.</p></div>
        </div>

        <h3 class="sub">Siapa yang dapat memanfaatkannya?</h3>
        <div class="grid grid-3">
            <div class="card"><h3>Masyarakat umum</h3><p>Memeriksa kalimat sebelum diunggah agar tidak tanpa sadar menyebarkan ujaran kebencian.</p></div>
            <div class="card"><h3>Moderator &amp; admin</h3><p>Membantu menyaring komentar di grup, forum, atau media sosial sebelum ditinjau lebih lanjut.</p></div>
            <div class="card"><h3>Peneliti &amp; akademisi</h3><p>Contoh penerapan <em>Natural Language Processing</em> (NLP) untuk masalah sosial di Indonesia.</p></div>
        </div>
    </div>
</section>

<!-- =========================== HATE SPEECH =========================== -->
<section class="sec soft" id="hate-speech">
    <div class="container">
        <div class="split">
            <?= foto('hate-speech', 'Ilustrasi perundungan di dunia maya', 'Ilustrasi perundungan daring (cyberbullying)', '4/3') ?>
            <div>
                <div class="sec-head" style="margin-bottom:24px">
                    <h2>Mengenal ujaran kebencian</h2>
                </div>
                <div class="prose">
                    <p>
                        Ujaran kebencian adalah tindakan komunikasi berupa provokasi, hasutan, atau hinaan kepada individu
                        atau kelompok berdasarkan identitas tertentu, seperti suku, agama, ras, gender, orientasi seksual,
                        maupun disabilitas.
                    </p>
                    <p>
                        Dampaknya tidak berhenti di layar. Ujaran kebencian dapat memicu diskriminasi, memperuncing
                        permusuhan antarkelompok, hingga mendorong terjadinya kekerasan di dunia nyata.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-2" style="margin-top:48px">
            <div class="card">
                <h3>Bentuk-bentuk ujaran kebencian</h3>
                <p style="margin-bottom:10px">Menurut Surat Edaran Kapolri No. SE/6/X/2015, ujaran kebencian dapat berupa:</p>
                <ul>
                    <li>Penghinaan</li>
                    <li>Pencemaran nama baik</li>
                    <li>Penistaan</li>
                    <li>Perbuatan tidak menyenangkan</li>
                    <li>Memprovokasi</li>
                    <li>Menghasut</li>
                    <li>Menyebarkan berita bohong</li>
                </ul>
            </div>
            <div class="card">
                <h3>Aspek yang biasa disasar</h3>
                <p style="margin-bottom:14px">Ujaran kebencian umumnya ditujukan kepada individu atau kelompok yang dibedakan berdasarkan:</p>
                <div class="tags">
                    <span class="tag">Suku</span><span class="tag">Agama</span><span class="tag">Aliran keagamaan</span>
                    <span class="tag">Keyakinan</span><span class="tag">Ras</span><span class="tag">Antargolongan</span>
                    <span class="tag">Warna kulit</span><span class="tag">Etnis</span><span class="tag">Gender</span>
                    <span class="tag">Kaum difabel</span><span class="tag">Orientasi seksual</span>
                </div>
            </div>
        </div>

        <h3 class="sub">Ujaran kebencian, bahasa kasar, dan kritik</h3>
        <div class="grid grid-3">
            <div class="card k-hate">
                <h3>Ujaran kebencian</h3>
                <p>Menyerang, merendahkan, atau menghasut kebencian terhadap seseorang atau kelompok <strong>karena identitasnya</strong>.</p>
                <div class="contoh"><span>Contoh</span>"Usir semua orang [kelompok tertentu] dari negeri ini!"</div>
            </div>
            <div class="card k-abuse">
                <h3>Bahasa kasar</h3>
                <p>Memakai kata kotor atau makian, tetapi <strong>tidak menyerang identitas</strong> kelompok tertentu.</p>
                <div class="contoh"><span>Contoh</span>"Dasar goblok, kerja begitu saja tidak becus!"</div>
            </div>
            <div class="card k-safe">
                <h3>Kritik / pendapat</h3>
                <p>Menyampaikan ketidaksetujuan terhadap <strong>gagasan, kebijakan, atau tindakan</strong> tanpa merendahkan identitas.</p>
                <div class="contoh"><span>Contoh</span>"Menurut saya kebijakan ini kurang tepat karena memberatkan rakyat kecil."</div>
            </div>
        </div>
    </div>
</section>

<!-- =========================== DASAR HUKUM =========================== -->
<section class="sec" id="dasar-hukum">
    <div class="container">
        <div class="split">
            <div>
                <div class="sec-head" style="margin-bottom:24px">
                    <h2>Dasar hukum &amp; penjelasan pasal</h2>
                </div>
                <div class="prose">
                    <p>
                        Di Indonesia, ujaran kebencian diatur dalam beberapa peraturan. Untuk ruang digital, aturan utamanya
                        adalah <strong>UU Informasi dan Transaksi Elektronik (UU ITE)</strong> yang terakhir diubah dengan
                        UU No. 1 Tahun 2024. Selain itu, <strong>KUHP baru (UU No. 1 Tahun 2023)</strong> yang berlaku sejak
                        2 Januari 2026 dan <strong>UU No. 40 Tahun 2008</strong> tentang Penghapusan Diskriminasi Ras dan Etnis
                        juga mengatur perbuatan serupa.
                    </p>
                    <p>
                        Berikut pasal-pasal yang paling sering dikaitkan dengan ujaran kebencian, lengkap dengan inti aturan,
                        unsur-unsurnya, dan ancaman pidananya.
                    </p>
                </div>
            </div>
            <?= foto('hukum', 'Buku undang-undang dan palu hakim', 'Buku undang-undang atau palu hakim', '4/3') ?>
        </div>

        <!-- UU ITE -->
        <div class="grup-hukum">
            <h3>UU ITE</h3>
            <span>UU No. 11 Tahun 2008 jo. UU No. 19 Tahun 2016 jo. UU No. 1 Tahun 2024</span>
        </div>
        <div class="grid grid-2">
            <article class="pasal">
                <h4>Pasal 28 ayat (2) jo. Pasal 45A ayat (2)</h4>
                <p class="pasal-topik">Menyebarkan kebencian berdasarkan identitas</p>
                <p>
                    Melarang setiap orang yang dengan sengaja dan tanpa hak menyebarkan informasi elektronik yang
                    menghasut, mengajak, atau memengaruhi orang lain sehingga menimbulkan rasa kebencian atau permusuhan
                    terhadap individu atau kelompok masyarakat berdasarkan identitasnya.
                </p>
                <h5>Unsur-unsur</h5>
                <ol>
                    <li>Setiap orang</li>
                    <li>Dengan sengaja dan tanpa hak</li>
                    <li>Mendistribusikan dan/atau mentransmisikan informasi atau dokumen elektronik</li>
                    <li>Bersifat menghasut, mengajak, atau memengaruhi orang lain</li>
                    <li>Menimbulkan rasa kebencian atau permusuhan</li>
                    <li>Terhadap individu/kelompok berdasarkan ras, kebangsaan, etnis, warna kulit, agama, kepercayaan, jenis kelamin, disabilitas mental, atau disabilitas fisik</li>
                </ol>
                <p class="catatan-pasal">
                    Putusan MK No. 105/PUU-XXII/2024 menegaskan pasal ini hanya berlaku untuk informasi yang secara
                    substantif memuat kebencian berdasarkan identitas, dilakukan dengan sengaja dan di muka umum, serta
                    menimbulkan risiko nyata terjadinya diskriminasi, permusuhan, atau kekerasan.
                </p>
                <p class="kaitan"><b>Kaitan dengan aplikasi:</b> paling relevan dengan hasil "Terindikasi Hate Speech".</p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</strong></div>
            </article>

            <article class="pasal">
                <h4>Pasal 27A jo. Pasal 45 ayat (4)</h4>
                <p class="pasal-topik">Menyerang kehormatan atau nama baik</p>
                <p>
                    Melarang setiap orang dengan sengaja menyerang kehormatan atau nama baik orang lain dengan cara
                    menuduhkan suatu hal, dengan maksud agar diketahui umum, melalui sistem elektronik. Pasal ini
                    menggantikan Pasal 27 ayat (3) yang lama.
                </p>
                <h5>Unsur-unsur</h5>
                <ol>
                    <li>Setiap orang dengan sengaja</li>
                    <li>Menyerang kehormatan atau nama baik orang lain</li>
                    <li>Dengan cara menuduhkan suatu hal</li>
                    <li>Dengan maksud agar diketahui umum</li>
                    <li>Dalam bentuk informasi/dokumen elektronik melalui sistem elektronik</li>
                </ol>
                <p class="catatan-pasal">
                    Merupakan <strong>delik aduan</strong>: hanya diproses jika korban sendiri yang melapor. Tidak dipidana
                    jika dilakukan untuk kepentingan umum atau karena terpaksa membela diri. Menurut Putusan MK
                    No. 105/PUU-XXII/2024, "orang lain" berarti perseorangan, bukan lembaga pemerintah, institusi,
                    korporasi, profesi, atau jabatan.
                </p>
                <p class="kaitan"><b>Kaitan dengan aplikasi:</b> hinaan yang menyasar individu.</p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 2 tahun dan/atau denda paling banyak Rp400 juta</strong></div>
            </article>

            <article class="pasal">
                <h4>Pasal 29 jo. Pasal 45B</h4>
                <p class="pasal-topik">Ancaman kekerasan atau menakut-nakuti</p>
                <p>
                    Melarang pengiriman informasi elektronik berisi ancaman kekerasan atau upaya menakut-nakuti yang
                    ditujukan secara pribadi kepada korban.
                </p>
                <p class="kaitan"><b>Kaitan dengan aplikasi:</b> kata ancaman seperti "bunuh" atau "bantai" disorot merah.</p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 4 tahun dan/atau denda paling banyak Rp750 juta</strong></div>
            </article>

            <article class="pasal">
                <h4>Pasal 28 ayat (3) jo. Pasal 45A ayat (3)</h4>
                <p class="pasal-topik">Berita bohong yang menimbulkan kerusuhan</p>
                <p>
                    Melarang penyebaran informasi elektronik yang diketahui memuat pemberitahuan bohong dan menimbulkan
                    kerusuhan di masyarakat. Hoaks sering menjadi sarana untuk menyulut kebencian terhadap kelompok tertentu.
                </p>
                <p class="kaitan"><b>Kaitan dengan aplikasi:</b> aplikasi tidak memeriksa kebenaran berita. Pasal ini dicantumkan sebagai konteks.</p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</strong></div>
            </article>
        </div>

        <!-- KUHP -->
        <div class="grup-hukum">
            <h3>KUHP baru</h3>
            <span>UU No. 1 Tahun 2023, berlaku sejak 2 Januari 2026</span>
        </div>
        <div class="grid grid-3">
            <article class="pasal">
                <h4>Pasal 242</h4>
                <p class="pasal-topik">Penghinaan terhadap golongan penduduk</p>
                <p>
                    Melarang pernyataan permusuhan atau penghinaan di muka umum terhadap satu atau beberapa golongan
                    penduduk Indonesia berdasarkan ras, kebangsaan, etnis, warna kulit, jenis kelamin, disabilitas mental,
                    atau disabilitas fisik.
                </p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</strong></div>
            </article>
            <article class="pasal">
                <h4>Pasal 243</h4>
                <p class="pasal-topik">Menyebarluaskan konten permusuhan</p>
                <p>
                    Melarang menyiarkan, mempertunjukkan, atau menempelkan tulisan/gambar, maupun memperdengarkan rekaman,
                    <strong>termasuk melalui sarana teknologi informasi</strong>, yang berisi permusuhan terhadap golongan
                    sebagaimana Pasal 242, dengan maksud agar diketahui umum.
                </p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 4 tahun atau denda paling banyak kategori IV (Rp200 juta)</strong></div>
            </article>
            <article class="pasal">
                <h4>Pasal 300</h4>
                <p class="pasal-topik">Permusuhan atas dasar agama atau kepercayaan</p>
                <p>
                    Melarang perbuatan bersifat permusuhan, pernyataan kebencian, atau hasutan untuk melakukan permusuhan,
                    kekerasan, atau diskriminasi di muka umum terhadap agama, kepercayaan, atau kelompok atas dasar agama
                    atau kepercayaan.
                </p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</strong></div>
            </article>
        </div>

        <!-- Lainnya -->
        <div class="grup-hukum">
            <h3>Peraturan lainnya</h3>
        </div>
        <div class="grid grid-2">
            <article class="pasal">
                <h4>UU No. 40 Tahun 2008, Pasal 4 huruf b jo. Pasal 16</h4>
                <p class="pasal-topik">Kebencian karena perbedaan ras dan etnis</p>
                <p>
                    Melarang tindakan yang menunjukkan kebencian atau rasa benci kepada orang karena perbedaan ras dan etnis,
                    antara lain dengan membuat tulisan atau gambar yang ditempatkan di tempat umum, maupun berpidato atau
                    melontarkan kata-kata tertentu di tempat umum yang dapat didengar orang lain.
                </p>
                <div class="ancaman"><span>Ancaman pidana</span><strong>Penjara paling lama 5 tahun dan/atau denda paling banyak Rp500 juta</strong></div>
            </article>
            <article class="pasal">
                <h4>Surat Edaran Kapolri No. SE/6/X/2015</h4>
                <p class="pasal-topik">Pedoman penanganan ujaran kebencian</p>
                <p>
                    Surat edaran ini <strong>bukan undang-undang</strong>, melainkan pedoman bagi kepolisian. Isinya
                    menjelaskan pengertian, bentuk-bentuk, dan aspek yang disasar ujaran kebencian, serta mengutamakan
                    langkah pencegahan dan mediasi antara pihak yang berselisih sebelum penegakan hukum.
                </p>
                <p class="kaitan"><b>Kaitan dengan aplikasi:</b> menjadi acuan definisi dan kategori ujaran kebencian di halaman ini.</p>
            </article>
        </div>

        <h3 class="sub">Ringkasan ancaman pidana</h3>
        <div class="tabel-wrap">
            <table class="tabel-ringkas">
                <thead>
                    <tr><th>Pasal</th><th>Perbuatan</th><th>Ancaman maksimal</th></tr>
                </thead>
                <tbody>
                    <tr><td>UU ITE Pasal 28 (2)</td><td>Menyebarkan kebencian berdasarkan identitas secara elektronik</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
                    <tr><td>UU ITE Pasal 28 (3)</td><td>Berita bohong yang menimbulkan kerusuhan</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
                    <tr><td>UU ITE Pasal 29</td><td>Ancaman kekerasan atau menakut-nakuti secara pribadi</td><td>4 tahun dan/atau Rp750 juta</td></tr>
                    <tr><td>UU ITE Pasal 27A</td><td>Menyerang kehormatan atau nama baik (delik aduan)</td><td>2 tahun dan/atau Rp400 juta</td></tr>
                    <tr><td>KUHP Pasal 242</td><td>Penghinaan terhadap golongan penduduk</td><td>3 tahun atau Rp200 juta</td></tr>
                    <tr><td>KUHP Pasal 243</td><td>Menyebarluaskan konten permusuhan terhadap golongan</td><td>4 tahun atau Rp200 juta</td></tr>
                    <tr><td>KUHP Pasal 300</td><td>Permusuhan atas dasar agama atau kepercayaan</td><td>3 tahun atau Rp200 juta</td></tr>
                    <tr><td>UU 40/2008 Pasal 16</td><td>Kebencian karena perbedaan ras dan etnis</td><td>5 tahun dan/atau Rp500 juta</td></tr>
                </tbody>
            </table>
        </div>
        <p class="keterangan">
            Penjelasan di atas adalah ringkasan untuk tujuan edukasi, bukan nasihat hukum. Untuk keperluan hukum,
            rujuk naskah resmi peraturan dan putusan pengadilan yang berlaku.
        </p>
    </div>
</section>

<!-- =========================== CARA KERJA =========================== -->
<section class="sec soft" id="cara-kerja">
    <div class="container">
        <div class="sec-head">
            <h2>Cara kerja aplikasi</h2>
            <p>Proses deteksi berlangsung dalam hitungan detik melalui empat tahap berikut.</p>
        </div>

        <div class="grid grid-4">
            <div class="card">
                <div class="step-no">01</div>
                <h3>Masukkan teks</h3>
                <p>Pengguna menulis atau menempelkan kalimat, komentar, atau postingan (maksimal <?= number_format(MAKS_KARAKTER, 0, ',', '.') ?> karakter).</p>
            </div>
            <div class="card">
                <div class="step-no">02</div>
                <h3>Validasi &amp; kirim</h3>
                <p>Server PHP memeriksa teks, lalu mengirimkannya dalam format JSON ke API model yang dibangun dengan FastAPI.</p>
            </div>
            <div class="card">
                <div class="step-no">03</div>
                <h3>Analisis model</h3>
                <p>Teks dipecah menjadi token, diproses lapisan Transformer XLM-RoBERTa, lalu menghasilkan skor <strong>hate speech</strong> dan <strong>bahasa kasar</strong>.</p>
            </div>
            <div class="card">
                <div class="step-no">04</div>
                <h3>Tampilkan hasil</h3>
                <p>Skor dibandingkan dengan ambang batas untuk menentukan status. Kamus kata membantu menyorot kata yang perlu diperhatikan.</p>
            </div>
        </div>

        <h3 class="sub">Arsitektur sistem</h3>
        <div class="alur" role="img" aria-label="Alur: Pengguna, Web PHP, API FastAPI, Model XLM-RoBERTa">
            <div class="alur-node"><strong>Pengguna</strong><small>Browser: HTML, CSS, JavaScript</small></div>
            <div class="alur-panah" aria-hidden="true">&rarr;</div>
            <div class="alur-node"><strong>Web PHP</strong><small>Validasi input, kamus bantu, tampilan hasil</small></div>
            <div class="alur-panah" aria-hidden="true">&rarr;</div>
            <div class="alur-node"><strong>API FastAPI</strong><small>Google Colab, diakses lewat Cloudflare Tunnel</small></div>
            <div class="alur-panah" aria-hidden="true">&rarr;</div>
            <div class="alur-node"><strong>Model XLM-RoBERTa</strong><small>Klasifikasi multilabel: HATESPEECH &amp; ABUSIVE</small></div>
        </div>
        <div style="margin-top:20px;max-width:720px">
            <?= foto('colab', 'Dokumentasi RuangAman API (FastAPI) dengan endpoint GET / dan POST /predict', 'Tangkapan layar API model di Google Colab', '16/9', 'foto-layar') ?>
            <p class="keterangan">Tangkapan layar dokumentasi API model (FastAPI) yang berjalan di Google Colab, dengan endpoint <code>POST /predict</code> yang dipanggil oleh aplikasi web.</p>
        </div>

        <h3 class="sub">Cara membaca hasil</h3>
        <div class="grid grid-4">
            <div class="card"><div class="status"><i class="dot" style="background:var(--hate)"></i>Terindikasi Hate Speech</div><p>Model cukup yakin teks mengandung ujaran kebencian terhadap individu atau kelompok.</p></div>
            <div class="card"><div class="status"><i class="dot" style="background:var(--abuse)"></i>Perlu Ditinjau</div><p>Skor berada di wilayah abu-abu. Model belum yakin, sebaiknya diperiksa oleh manusia.</p></div>
            <div class="card"><div class="status"><i class="dot" style="background:var(--abuse)"></i>Ujaran Kasar</div><p>Teks mengandung bahasa kasar atau makian, tetapi tidak terindikasi ujaran kebencian.</p></div>
            <div class="card"><div class="status"><i class="dot" style="background:var(--safe)"></i>Bukan Hate Speech</div><p>Tidak ditemukan indikasi ujaran kebencian maupun bahasa kasar yang berarti.</p></div>
        </div>
        <div class="card" style="margin-top:20px">
            <ul>
                <li><strong>Skor ujaran kebencian &amp; skor bahasa kasar</strong>: probabilitas (0–100%) dari model. Keduanya dihitung terpisah, sehingga jumlahnya tidak harus 100%.</li>
                <li><strong>Tingkat</strong>: seberapa kuat indikasi ujaran kebencian menurut model.</li>
                <li><strong>Sasaran &amp; kategori</strong>: perkiraan dari kamus kata (individu/kelompok; agama, ras, fisik, gender, dll.) sebagai informasi tambahan.</li>
                <li><strong>Sorotan kata</strong>: merah untuk kebencian/ancaman, oranye untuk kata kasar, biru untuk sasaran.</li>
            </ul>
        </div>
    </div>
</section>

<!-- ============================ CEK TEKS ============================ -->
<section class="sec" id="cek">
    <div class="container cek-wrap">
        <div class="sec-head" style="margin-bottom:28px">
            <h2>Cek teks Anda</h2>
            <p>Tulis atau tempel kalimat yang ingin diperiksa, lalu tekan <strong>Cek Sekarang</strong>. Hasil analisis akan muncul di bawah formulir.</p>
        </div>

        <div class="cek-card">
            <form method="post" action="#cek" id="form-cek" autocomplete="off">
                <label class="field-label" for="teks">Teks yang ingin dicek</label>
                <textarea id="teks" name="teks" maxlength="<?= MAKS_KARAKTER ?>"
                          placeholder="Tulis atau tempel komentar di sini..." required><?= e($teks) ?></textarea>
                <div class="meta-row">
                    <div class="examples">
                        <span>Contoh:</span>
                        <button type="button" class="chip" data-contoh="Terima kasih banyak atas bantuannya, semoga hari kalian menyenangkan!">Netral</button>
                        <button type="button" class="chip" data-contoh="Dasar goblok, kerja begitu saja tidak becus!">Kasar</button>
                        <button type="button" class="chip" data-contoh="Usir semua cina dari negeri ini, dasar kafir!">Kebencian</button>
                    </div>
                    <span id="hitung">0 / <?= MAKS_KARAKTER ?></span>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary" id="tombol-cek">
                        <span class="spinner" aria-hidden="true"></span>
                        Cek Sekarang
                    </button>
                    <button type="button" class="btn btn-ghost" id="tombol-hapus">Hapus</button>
                </div>
            </form>

            <?php if ($galat !== ''): ?>
                <div class="alert" role="alert"><?= e($galat) ?></div>
            <?php endif; ?>

            <?php if ($hasil !== null): $info = $infoLabel[$hasil['label']]; ?>
                <div class="result" id="hasil" aria-live="polite">
                    <div class="verdict <?= $hasil['label'] ?>">
                        <div class="verdict-icon" aria-hidden="true"><?= $info['ikon'] ?></div>
                        <div>
                            <h3><?= e($info['judul']) ?></h3>
                            <p><?= e($hasil['penjelasan'] !== '' ? $hasil['penjelasan'] : $info['sub']) ?></p>
                        </div>
                    </div>

                    <div class="meter">
                        <div class="meter-head">
                            <span>Skor ujaran kebencian</span>
                            <span><?= (int)$hasil['skor'] ?>%</span>
                        </div>
                        <div class="meter-track">
                            <div class="meter-fill <?= $hasil['label'] ?>-fill" data-width="<?= (int)$hasil['skor'] ?>"></div>
                        </div>
                    </div>

                    <div class="meter">
                        <div class="meter-head">
                            <span>Skor bahasa kasar</span>
                            <span><?= (int)$hasil['skorKasar'] ?>%</span>
                        </div>
                        <div class="meter-track">
                            <div class="meter-fill abusive-fill" data-width="<?= (int)$hasil['skorKasar'] ?>"></div>
                        </div>
                    </div>

                    <div class="stats">
                        <div class="stat"><small>Sasaran*</small><strong><?= e($hasil['target']) ?></strong></div>
                        <div class="stat"><small>Kategori*</small><strong><?= e($hasil['kategori'] ? implode(', ', $hasil['kategori']) : '-') ?></strong></div>
                        <div class="stat"><small>Tingkat</small><strong><?= e($hasil['tingkat']) ?></strong></div>
                    </div>
                    <p class="catatan">*Perkiraan dari kamus kata sebagai bantuan. Skor dan label utama berasal dari model AI.</p>

                    <div class="section-title">Teks yang dianalisis</div>
                    <div class="highlighted"><?= $hasil['html'] ?></div>
                    <?php if ($hasil['temuan']): ?>
                        <div class="legend">
                            <span><i class="dot" style="background:var(--hate)"></i>Ujaran kebencian / ancaman</span>
                            <span><i class="dot" style="background:var(--abuse)"></i>Kata kasar</span>
                            <span><i class="dot" style="background:var(--target)"></i>Sasaran</span>
                        </div>

                        <div class="section-title">Kata yang disorot kamus (<?= count($hasil['temuan']) ?>)</div>
                        <div class="temuan">
                            <?php foreach ($hasil['temuan'] as $t): ?>
                                <span><b><?= e($t['kata']) ?></b> &middot; <?= e($namaTipe[$t['tipe']]) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <p class="disclaimer">Penilaian oleh model AI. Hasil bisa saja keliru, gunakan sebagai bahan pertimbangan.</p>
    </div>
</section>

<!-- ============================== MODEL ============================== -->
<section class="sec soft" id="model">
    <div class="container">
        <div class="sec-head">
            <h2>Model &amp; teknologi</h2>
            <p>
                XLM-RoBERTa adalah model bahasa Transformer multibahasa yang dilatih pada teks dari sekitar 100 bahasa.
                Model ini memahami konteks kalimat secara utuh, bukan sekadar mencocokkan kata, sehingga cocok untuk teks
                media sosial Indonesia yang sering bercampur dengan bahasa daerah dan bahasa gaul.
            </p>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <h3>Spesifikasi model</h3>
                <table class="spek">
                    <tr><th>Nama model</th><td><a href="https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1" target="_blank" rel="noopener">nahiar/hatespeech-abusive-xlm-roberta-v1</a></td></tr>
                    <tr><th>Model dasar</th><td>XLM-RoBERTa base (FacebookAI)</td></tr>
                    <tr><th>Metode</th><td>Fine-tuning, learning rate 2e-5, batch size 16</td></tr>
                    <tr><th>Klasifikasi</th><td>Multilabel: HATESPEECH dan ABUSIVE</td></tr>
                    <tr><th>Bahasa</th><td>Indonesia, Melayu, Jawa, Sunda, Madura, Minangkabau, Banjar, Bugis, Aceh, dan Inggris</td></tr>
                    <tr><th>Lisensi</th><td>Apache-2.0</td></tr>
                </table>
            </div>
            <div>
                <div class="grid grid-2">
                    <div class="card metrik"><strong>92,49%</strong><span>Precision</span></div>
                    <div class="card metrik"><strong>93,00%</strong><span>Recall</span></div>
                    <div class="card metrik"><strong>92,74%</strong><span>F1-score (macro)</span></div>
                    <div class="card metrik"><strong>92,69%</strong><span>F1-score (weighted)</span></div>
                </div>
                <p class="keterangan">Metrik dilaporkan oleh pengembang model pada data validasi (sumber: model card Hugging Face), bukan hasil pengujian pada aplikasi ini.</p>
            </div>
        </div>

        <h3 class="sub">Teknologi yang digunakan</h3>
        <div class="tags">
            <span class="tag">PHP 8</span><span class="tag">HTML5</span><span class="tag">CSS3</span><span class="tag">JavaScript</span>
            <span class="tag">Python</span><span class="tag">FastAPI</span><span class="tag">Hugging Face Transformers</span>
            <span class="tag">XLM-RoBERTa</span><span class="tag">Google Colab</span><span class="tag">Cloudflare Tunnel</span>
        </div>
    </div>
</section>

<!-- =========================== KETERBATASAN =========================== -->
<section class="sec" id="keterbatasan">
    <div class="container">
        <div class="sec-head">
            <h2>Keterbatasan &amp; etika</h2>
            <p>Sebagai sistem otomatis, Cek Hatespeech memiliki keterbatasan. Gunakan hasilnya sebagai bahan pertimbangan, bukan keputusan akhir.</p>
        </div>
        <div class="grid grid-3">
            <div class="card"><h3>Konteks &amp; sarkasme</h3><p>Sindiran, ironi, atau kalimat yang justru mengecam kebencian kadang dinilai keliru oleh model.</p></div>
            <div class="card"><h3>Bukan alat bukti hukum</h3><p>Hasil deteksi tidak dapat dijadikan dasar penegakan hukum tanpa peninjauan oleh manusia dan ahli.</p></div>
            <div class="card"><h3>Bias data latih</h3><p>Kemampuan model bergantung pada data pelatihan, sehingga istilah baru atau sangat lokal mungkin belum dikenali.</p></div>
            <div class="card"><h3>Sasaran &amp; kategori</h3><p>Model hanya menilai hate speech dan bahasa kasar. Sasaran dan kategori merupakan perkiraan dari kamus kata.</p></div>
            <div class="card"><h3>Ketersediaan server</h3><p>Model berjalan di server terpisah. Jika server model tidak aktif, pengecekan untuk sementara tidak dapat dilakukan.</p></div>
            <div class="card"><h3>Privasi</h3><p>Aplikasi web tidak menyimpan teks ke basis data. Teks hanya dikirim ke server model untuk dianalisis. Hindari memasukkan data pribadi.</p></div>
        </div>
    </div>
</section>

<!-- =============================== FAQ =============================== -->
<section class="sec soft" id="faq">
    <div class="container">
        <div class="sec-head">
            <h2>Pertanyaan yang sering diajukan</h2>
        </div>
        <div class="faq">
            <details>
                <summary>Apakah hasil deteksinya pasti benar?</summary>
                <p>Tidak. Model memiliki tingkat ketepatan yang tinggi pada data uji, tetapi tetap bisa keliru, terutama pada kalimat bersifat sindiran atau yang sangat bergantung pada konteks. Gunakan hasilnya sebagai bahan pertimbangan.</p>
            </details>
            <details>
                <summary>Apa bedanya hate speech dengan bahasa kasar?</summary>
                <p>Hate speech menyerang seseorang atau kelompok karena identitasnya (suku, agama, ras, gender, dan sebagainya). Bahasa kasar hanya berisi makian atau kata kotor tanpa menyerang identitas. Kalimat yang kasar belum tentu hate speech, dan hate speech tidak selalu memakai kata kasar.</p>
            </details>
            <details>
                <summary>Mengapa ada status "Perlu Ditinjau"?</summary>
                <p>Status ini muncul ketika skor model berada di antara batas aman dan batas indikasi kuat. Pada kondisi tersebut model belum cukup yakin, sehingga keputusan sebaiknya diserahkan kepada manusia.</p>
            </details>
            <details>
                <summary>Mengapa skor hate speech dan bahasa kasar jika dijumlahkan bisa lebih dari 100%?</summary>
                <p>Model bersifat multilabel, artinya setiap label dinilai secara terpisah. Sebuah kalimat bisa sekaligus mengandung ujaran kebencian dan bahasa kasar, sehingga kedua skornya bisa sama-sama tinggi.</p>
            </details>
            <details>
                <summary>Jika hasilnya "Terindikasi Hate Speech", apakah berarti melanggar hukum?</summary>
                <p>Belum tentu. Suatu ujaran baru dapat dipidana jika memenuhi seluruh unsur pasal, misalnya dilakukan dengan sengaja, di muka umum, dan menimbulkan kebencian berdasarkan identitas. Penilaian tersebut hanya dapat dilakukan oleh aparat penegak hukum dan pengadilan.</p>
            </details>
            <details>
                <summary>Apakah teks yang saya masukkan disimpan?</summary>
                <p>Aplikasi web ini tidak menyimpan teks ke basis data. Teks hanya dikirim ke server model untuk dianalisis, lalu hasilnya ditampilkan kembali kepada Anda.</p>
            </details>
            <details>
                <summary>Bahasa apa saja yang didukung?</summary>
                <p>Model dirancang untuk bahasa Indonesia dan Melayu, beberapa bahasa daerah seperti Jawa, Sunda, Madura, Minangkabau, Banjar, Bugis, dan Aceh, serta bahasa Inggris. Hasil terbaik diperoleh pada teks berbahasa Indonesia.</p>
            </details>
            <details>
                <summary>Mengapa muncul pesan "Model AI tidak dapat dihubungi"?</summary>
                <p>Pesan ini berarti server model sedang tidak aktif atau alamatnya berubah. Coba beberapa saat lagi atau hubungi pengembang.</p>
            </details>
        </div>
    </div>
</section>

<!-- ============================ PENGEMBANG ============================ -->
<section class="sec" id="pengembang">
    <div class="container">
        <div class="sec-head">
            <h2>Tentang pengembang</h2>
            <p>Aplikasi ini dikembangkan sebagai tugas akhir.</p>
        </div>
        <div class="profil">
            <?php if (adaFoto('pengembang')): ?>
                <?= foto('pengembang', 'Foto ' . PENGEMBANG['nama'], 'Pas foto pengembang', '3/4', 'foto-profil') ?>
            <?php else: ?>
                <div class="foto foto-profil foto-inisial" style="aspect-ratio:3/4" role="img" aria-label="<?= e(PENGEMBANG['nama']) ?>"><?= e(inisial(PENGEMBANG['nama'])) ?></div>
            <?php endif; ?>
            <div>
                <p class="judul-ta"><?= e(PENGEMBANG['judul']) ?></p>
                <table class="spek">
                    <tr><th>Nama</th><td><?= e(PENGEMBANG['nama']) ?></td></tr>
                    <tr><th>NIM</th><td><?= e(PENGEMBANG['nim']) ?></td></tr>
                    <tr><th>Program Studi</th><td><?= e(PENGEMBANG['prodi']) ?></td></tr>
                    <?php if (PENGEMBANG['pembimbing'] !== ''): ?>
                        <tr><th>Dosen Pembimbing</th><td><?= e(PENGEMBANG['pembimbing']) ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Tahun</th><td><?= e(PENGEMBANG['tahun']) ?></td></tr>
                </table>
                <div class="kampus">
                    <?= foto('logo-kampus', 'Logo ' . PENGEMBANG['kampus'], 'Logo kampus', '1/1', 'foto-logo') ?>
                    <div><strong><?= e(PENGEMBANG['kampus']) ?></strong><?= e(PENGEMBANG['prodi']) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="#beranda" class="brand">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
                    Cek Hatespeech
                </a>
                <p style="margin:12px 0 0;max-width:420px">Aplikasi web pendeteksi ujaran kebencian dan bahasa kasar berbahasa Indonesia berbasis model XLM-RoBERTa. Mari bijak berkomunikasi di ruang digital.</p>
            </div>
            <ul class="footer-links">
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#hate-speech">Hate Speech</a></li>
                <li><a href="#dasar-hukum">Dasar Hukum</a></li>
                <li><a href="#cara-kerja">Cara Kerja</a></li>
                <li><a href="#cek">Cek Teks</a></li>
                <li><a href="#model">Model</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#pengembang">Pengembang</a></li>
            </ul>
        </div>
        <div class="footer-bawah">
            <span>&copy; <?= date('Y') ?> Cek Hatespeech &middot; <?= e(PENGEMBANG['nama']) ?></span>
            <span>Model: nahiar/hatespeech-abusive-xlm-roberta-v1</span>
        </div>
        <p class="kredit">
            Kredit foto (lisensi CC0 / domain publik):
            <a href="https://www.rawpixel.com/image/5921379/photo-image-public-domain-student-woman" target="_blank" rel="noopener">rawpixel</a>,
            <a href="https://commons.wikimedia.org/w/index.php?curid=78993089" target="_blank" rel="noopener">Rawpixel.com via Wikimedia Commons</a>,
            <a href="https://stocksnap.io/photo/people-woman-NXWB8SXF45" target="_blank" rel="noopener">Siddharth Bhogra via StockSnap</a>,
            <a href="https://commons.wikimedia.org/w/index.php?curid=91844335" target="_blank" rel="noopener">Joe Gratz via Wikimedia Commons</a>.
            Logo Telkom University oleh Hilfans,
            <a href="https://commons.wikimedia.org/wiki/File:Logo_Telkom_University_potrait.png" target="_blank" rel="noopener">Wikimedia Commons</a>,
            <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener">CC BY-SA 4.0</a>.
        </p>
    </div>
</footer>

<script>
    (function () {
        var teks = document.getElementById('teks');
        var hitung = document.getElementById('hitung');
        var maks = <?= MAKS_KARAKTER ?>;

        function perbaruiHitungan() { hitung.textContent = teks.value.length + ' / ' + maks; }
        teks.addEventListener('input', perbaruiHitungan);
        perbaruiHitungan();

        document.querySelectorAll('[data-contoh]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                teks.value = btn.getAttribute('data-contoh');
                perbaruiHitungan();
                teks.focus();
            });
        });

        document.getElementById('tombol-hapus').addEventListener('click', function () {
            teks.value = '';
            perbaruiHitungan();
            var hasil = document.getElementById('hasil');
            if (hasil) hasil.remove();
            teks.focus();
        });

        // Ctrl/Cmd + Enter untuk mengirim
        teks.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') document.getElementById('form-cek').requestSubmit();
        });

        document.getElementById('form-cek').addEventListener('submit', function () {
            document.getElementById('tombol-cek').classList.add('loading');
        });

        // Menu navigasi (mobile)
        var nav = document.getElementById('nav');
        var toggle = document.getElementById('nav-toggle');
        toggle.addEventListener('click', function () {
            var buka = nav.classList.toggle('buka');
            toggle.setAttribute('aria-expanded', buka ? 'true' : 'false');
        });
        nav.querySelectorAll('.nav-links a').forEach(function (a) {
            a.addEventListener('click', function () {
                nav.classList.remove('buka');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Tandai menu sesuai bagian yang sedang dilihat
        if ('IntersectionObserver' in window) {
            var tautan = {};
            nav.querySelectorAll('.nav-links a').forEach(function (a) { tautan[a.getAttribute('href').slice(1)] = a; });
            var pengamat = new IntersectionObserver(function (entri) {
                entri.forEach(function (en) {
                    if (en.isIntersecting && tautan[en.target.id]) {
                        Object.keys(tautan).forEach(function (k) { tautan[k].classList.remove('aktif'); });
                        tautan[en.target.id].classList.add('aktif');
                    }
                });
            }, { rootMargin: '-45% 0px -50% 0px' });
            Object.keys(tautan).forEach(function (id) {
                var el = document.getElementById(id);
                if (el) pengamat.observe(el);
            });
        }

        var fills = document.querySelectorAll('.meter-fill');
        if (fills.length) {
            requestAnimationFrame(function () {
                fills.forEach(function (fill) { fill.style.width = fill.getAttribute('data-width') + '%'; });
            });
            document.getElementById('hasil').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })();
</script>
</body>
</html>
