<?php
declare(strict_types=1);

// Server bawaan PHP (lokal): biarkan file statis seperti assets/img dilayani langsung.
if (PHP_SAPI === 'cli-server') {
    $berkas = dirname(__DIR__) . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($berkas) && !str_ends_with($berkas, '.php')) {
        return false;
    }
}

// Folder akar proyek (tempat folder assets/ berada).
define('AKAR', dirname(__DIR__));

/* =========================================================================
 *  CEK HATESPEECH
 *  Penilaian utama: model AI nahiar/hatespeech-abusive-xlm-roberta-v1
 *  yang dijalankan sebagai API Python (FastAPI di Google Colab).
 *  Kamus kata hanya dipakai sebagai lapisan bantu untuk menyorot kata
 *  dan memperkirakan sasaran/kategori, BUKAN untuk menentukan hasil.
 * ========================================================================= */

const MAKS_KARAKTER = 2000;

// Alamat API model. Di Vercel, isi lewat Environment Variable bernama API_URL
// (Settings > Environment Variables), lalu Redeploy. Nilai di bawah hanya cadangan
// untuk dijalankan lokal. Link trycloudflare berubah setiap Colab dijalankan ulang.
define('API_URL', getenv('API_URL') ?: 'https://mountain-leo-dicke-observer.trycloudflare.com/predict');

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
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 25, // di bawah batas 30 detik fungsi Vercel
    ]);
    $respon = curl_exec($ch);
    $kode   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $galat  = curl_error($ch);
    $errno  = curl_errno($ch);
    unset($ch); // curl_close() sudah usang sejak PHP 8.5

    // Server sedang dinyalakan/bangun dari tidur (mis. Hugging Face Space) atau terlalu lama merespons.
    if ($errno === CURLE_OPERATION_TIMEDOUT || in_array($kode, [502, 503, 504], true)) {
        throw new RuntimeException('Server model sedang dinyalakan atau sibuk. Tunggu sekitar 1 menit, lalu tekan Cek Sekarang lagi.');
    }
    if ($respon === false || $kode !== 200) {
        throw new RuntimeException('Model AI tidak dapat dihubungi. Pastikan server model aktif dan API_URL sudah benar. ('
            . ($galat !== '' ? $galat : 'kode ' . $kode) . ')');
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
        'status'     => (string)$model['status'],
        'hs'         => (float)$model['skor']['HATESPEECH'],
        'ab'         => (float)$model['skor']['ABUSIVE'],
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
            $galat = $ex->getMessage();
        }
    }
}

/* ============================ Label & bantuan tampilan ============================ */

// Judul tiap status hasil (dipakai di jendela Cek teks).
$infoLabel = [
    'hate'    => ['judul' => 'Indikasi kuat ujaran kebencian', 'ikon' => '!'],
    'tinjau'  => ['judul' => 'Perlu ditinjau manusia',         'ikon' => '?'],
    'abusive' => ['judul' => 'Bahasa kasar',                   'ikon' => '!'],
    'aman'    => ['judul' => 'Tidak terindikasi',              'ikon' => '✓'],
];
$namaTipe = ['slur' => 'Ujaran kebencian', 'fisik' => 'Hinaan fisik', 'ancaman' => 'Ancaman',
             'kasar' => 'Kata kasar', 'kelompok' => 'Sasaran kelompok', 'individu' => 'Sasaran individu'];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** Skor 0-1 menjadi persen gaya Indonesia, mis. 0.9933 -> "99,33%". */
function persen(float $skor): string
{
    return number_format($skor * 100, 2, ',', '.') . '%';
}

/** Alamat file di folder assets dengan penanda versi dari isi file (cache selalu segar). */
function aset(string $jalur): string
{
    $berkas = AKAR . '/assets/' . ltrim($jalur, '/');
    $versi = is_file($berkas) ? '?v=' . substr((string)md5_file($berkas), 0, 8) : '';
    return '/assets/' . ltrim($jalur, '/') . $versi;
}

/** Cek apakah file foto untuk slot tertentu sudah ada di assets/img. */
function adaFoto(string $nama): bool
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file(AKAR . "/assets/img/{$nama}.{$ext}")) {
            return true;
        }
    }
    return false;
}

/** Alamat foto assets/img/<nama>.<ext> yang ada, atau string kosong. */
function urlFoto(string $nama): string
{
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file(AKAR . "/assets/img/{$nama}.{$ext}")) {
            return aset("img/{$nama}.{$ext}");
        }
    }
    return '';
}

/** Inisial nama, mis. "Arvan Murbiyanto" -> "AM". */
function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim(preg_replace('/[^\p{L}\s]/u', '', $nama))) ?: [];
    $huruf = array_map(fn($k) => mb_strtoupper(mb_substr($k, 0, 1)), array_slice(array_filter($kata), 0, 2));
    return implode('', $huruf) ?: '?';
}

/* ============================ Router halaman ============================ */

$jalur = rtrim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'), '/');
$daftarHalaman = ['' => 'beranda', '/dasar-hukum' => 'dasar-hukum', '/tentang' => 'tentang', '/kredit' => 'kredit'];
$halaman = $daftarHalaman[$jalur] ?? null;
if ($halaman === null) {
    http_response_code(404);
    $halaman = '404';
}

require AKAR . '/views/layout.php';
require AKAR . "/views/{$halaman}.php";
