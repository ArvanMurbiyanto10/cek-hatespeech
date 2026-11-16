import { KAMPUS, PASAL } from '@/lib/kampus';
import './dasar-hukum.css';

export const metadata = { title: 'Dasar hukum', description: 'UU ITE, KUHP baru, UU No. 40 Tahun 2008 dan Permendikbudristek 55/2024 tentang ujaran kebencian dan perundungan.' };

const lcfirst = (s: string) => s.charAt(0).toLowerCase() + s.slice(1);

export default function Halaman() {
  return (
<main id="top">

  <section className="doc-sec doc-intro" aria-labelledby="dh-h">
    <div className="wrap">
      <div className="grid grid--top">
        <div className="span-6">
          <h1 id="dh-h">Dasar hukum ujaran kebencian di Indonesia</h1>
          <p className="t-lead">Ujaran kebencian di ruang digital diatur terutama oleh UU ITE yang terakhir diubah dengan UU No. 1 Tahun 2024. KUHP baru, yang berlaku sejak 2 Januari 2026, dan UU No. 40 Tahun 2008 mengatur perbuatan serupa.</p>
        </div>
        <div className="frame frame--photo span-6 start-7">
          <img className="frame__img" src="/assets/img/hukum.jpg" alt="Palu hakim di atas meja sidang dengan tumpukan buku" fetchPriority="high" data-source="library" data-subject="palu hakim di ruang sidang" />
        </div>
      </div>
      <nav className="doc-toc" aria-label="Isi halaman">
        <a href="#uu-ite">UU ITE<span>Pasal 28 (2), 27A, 29, 28 (3)</span></a>
        <a href="#kuhp">KUHP baru<span>Pasal 242, 243, 300</span></a>
        <a href="#uu-40-2008">Peraturan lainnya<span>UU 40/2008, SE Kapolri, aturan kampus</span></a>
        <a href="#ringkasan">Ringkasan pidana<span>Semua ancaman dalam satu tabel</span></a>
      </nav>
    </div>
  </section>

  <section className="doc-sec ground-surface" id="uu-ite" aria-labelledby="ite-h">
    <div className="wrap">
      <div className="doc-head"><h2 id="ite-h">UU ITE</h2><p>UU No. 11 Tahun 2008 jo. UU No. 19 Tahun 2016 jo. UU No. 1 Tahun 2024</p></div>
      <div className="grid grid--top">
        <article className="card pasal m-nolift span-6" id="pasal-28-2">
          <h3>Pasal 28 ayat (2) jo. Pasal 45A ayat (2)</h3>
          <p className="pasal__topik">Menyebarkan kebencian berdasarkan identitas</p>
          <p>Melarang setiap orang yang dengan sengaja dan tanpa hak menyebarkan informasi elektronik yang menghasut, mengajak atau memengaruhi orang lain sehingga menimbulkan rasa kebencian atau permusuhan terhadap individu atau kelompok berdasarkan identitasnya.</p>
          <h4>Unsur-unsur</h4>
          <ol>
            <li>Setiap orang</li>
            <li>Dengan sengaja dan tanpa hak</li>
            <li>Mendistribusikan atau mentransmisikan informasi atau dokumen elektronik</li>
            <li>Bersifat menghasut, mengajak atau memengaruhi orang lain</li>
            <li>Menimbulkan rasa kebencian atau permusuhan</li>
            <li>Terhadap individu atau kelompok berdasarkan ras, kebangsaan, etnis, warna kulit, agama, kepercayaan, jenis kelamin, disabilitas mental atau disabilitas fisik</li>
          </ol>
          <p className="pasal__catatan">Putusan MK No. 105/PUU-XXII/2024 menegaskan pasal ini hanya berlaku untuk informasi yang secara substantif memuat kebencian berdasarkan identitas, dilakukan dengan sengaja dan di muka umum, serta menimbulkan risiko nyata diskriminasi, permusuhan atau kekerasan.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</p>
        </article>
        <article className="card pasal m-nolift span-6" id="pasal-27a">
          <h3>Pasal 27A jo. Pasal 45 ayat (4)</h3>
          <p className="pasal__topik">Menyerang kehormatan atau nama baik</p>
          <p>Melarang setiap orang dengan sengaja menyerang kehormatan atau nama baik orang lain dengan cara menuduhkan suatu hal, dengan maksud agar diketahui umum, melalui sistem elektronik. Pasal ini menggantikan Pasal 27 ayat (3) yang lama.</p>
          <h4>Unsur-unsur</h4>
          <ol>
            <li>Setiap orang dengan sengaja</li>
            <li>Menyerang kehormatan atau nama baik orang lain</li>
            <li>Dengan cara menuduhkan suatu hal</li>
            <li>Dengan maksud agar diketahui umum</li>
            <li>Dalam bentuk informasi atau dokumen elektronik melalui sistem elektronik</li>
          </ol>
          <p className="pasal__catatan">Delik aduan: hanya diproses jika korban sendiri yang melapor. Tidak dipidana jika dilakukan untuk kepentingan umum atau karena terpaksa membela diri. Menurut Putusan MK No. 105/PUU-XXII/2024, "orang lain" berarti perseorangan, bukan lembaga pemerintah, institusi, korporasi, profesi atau jabatan.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 2 tahun dan/atau denda paling banyak Rp400 juta</p>
        </article>
        <article className="card pasal m-nolift span-6" id="pasal-29">
          <h3>Pasal 29 jo. Pasal 45B</h3>
          <p className="pasal__topik">Ancaman kekerasan atau menakut-nakuti</p>
          <p>Melarang pengiriman informasi elektronik berisi ancaman kekerasan atau upaya menakut-nakuti yang ditujukan secara pribadi kepada korban.</p>
          <p className="pasal__catatan">Di Cek Hatespeech, kata ancaman seperti "bunuh" atau "bantai" disorot merah oleh kamus bantu.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 4 tahun dan/atau denda paling banyak Rp750 juta</p>
        </article>
        <article className="card pasal m-nolift span-6" id="pasal-28-3">
          <h3>Pasal 28 ayat (3) jo. Pasal 45A ayat (3)</h3>
          <p className="pasal__topik">Berita bohong yang menimbulkan kerusuhan</p>
          <p>Melarang penyebaran informasi elektronik yang diketahui memuat pemberitahuan bohong dan menimbulkan kerusuhan di masyarakat. Hoaks sering dipakai untuk menyulut kebencian terhadap kelompok tertentu.</p>
          <p className="pasal__catatan">Cek Hatespeech tidak memeriksa kebenaran berita; pasal ini dicantumkan sebagai konteks.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 6 tahun dan/atau denda paling banyak Rp1 miliar</p>
        </article>
      </div>
    </div>
  </section>

  <section className="doc-sec" id="kuhp" aria-labelledby="kuhp-h">
    <div className="wrap">
      <div className="doc-head"><h2 id="kuhp-h">KUHP baru</h2><p>UU No. 1 Tahun 2023, berlaku sejak 2 Januari 2026</p></div>
      <div className="grid grid--top">
        <article className="card pasal m-nolift span-4">
          <h3>Pasal 242</h3>
          <p className="pasal__topik">Penghinaan terhadap golongan penduduk</p>
          <p>Melarang pernyataan permusuhan atau penghinaan di muka umum terhadap satu atau beberapa golongan penduduk Indonesia berdasarkan ras, kebangsaan, etnis, warna kulit, jenis kelamin, disabilitas mental atau disabilitas fisik.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
        <article className="card pasal m-nolift span-4">
          <h3>Pasal 243</h3>
          <p className="pasal__topik">Menyebarluaskan konten permusuhan</p>
          <p>Melarang menyiarkan, mempertunjukkan atau menempelkan tulisan dan gambar, maupun memperdengarkan rekaman, termasuk melalui sarana teknologi informasi, yang berisi permusuhan terhadap golongan sebagaimana Pasal 242.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 4 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
        <article className="card pasal m-nolift span-4">
          <h3>Pasal 300</h3>
          <p className="pasal__topik">Permusuhan atas dasar agama atau kepercayaan</p>
          <p>Melarang perbuatan bersifat permusuhan, pernyataan kebencian, atau hasutan untuk permusuhan, kekerasan atau diskriminasi di muka umum terhadap agama, kepercayaan, atau kelompok atas dasar agama atau kepercayaan.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 3 tahun atau denda paling banyak kategori IV (Rp200 juta)</p>
        </article>
      </div>
    </div>
  </section>

  <section className="doc-sec ground-surface" id="uu-40-2008" aria-labelledby="lain-h">
    <div className="wrap">
      <div className="doc-head"><h2 id="lain-h">Peraturan lainnya</h2><p>Undang-undang khusus, pedoman kepolisian dan aturan kampus</p></div>
      <div className="grid grid--top">
        <article className="card pasal m-nolift span-6">
          <h3>UU No. 40 Tahun 2008, Pasal 4 huruf b jo. Pasal 16</h3>
          <p className="pasal__topik">Kebencian karena perbedaan ras dan etnis</p>
          <p>Melarang tindakan yang menunjukkan kebencian atau rasa benci kepada orang karena perbedaan ras dan etnis, antara lain lewat tulisan atau gambar di tempat umum, maupun pidato dan ucapan di tempat umum yang dapat didengar orang lain.</p>
          <p className="pasal__ancaman"><span>Ancaman pidana</span>Penjara paling lama 5 tahun dan/atau denda paling banyak Rp500 juta</p>
        </article>
        <article className="card pasal m-nolift span-6" id="se-kapolri">
          <h3>Surat Edaran Kapolri No. SE/6/X/2015</h3>
          <p className="pasal__topik">Pedoman penanganan ujaran kebencian</p>
          <p>Surat edaran ini bukan undang-undang, melainkan pedoman bagi kepolisian. Isinya menjelaskan pengertian, bentuk dan aspek yang disasar ujaran kebencian, serta mengutamakan pencegahan dan mediasi sebelum penegakan hukum.</p>
          <p className="pasal__catatan">Menjadi acuan definisi dan kategori ujaran kebencian di Cek Hatespeech.</p>
        </article>
        <article className="card pasal m-nolift span-12" id="aturan-kampus">
          <h3>{PASAL.kampus.nama}</h3>
          <p className="pasal__topik">{PASAL.kampus.judul}</p>
          <p>{PASAL.kampus.isi} Di Telkom University Purwokerto, laporan diserahkan ke {KAMPUS.tujuan}.</p>
          <p className="pasal__catatan">Sanksi bagi pelaku: {lcfirst(PASAL.kampus.ancaman)}. Inilah dasar kampus menangani laporan yang dibuat lewat Cek Hatespeech.</p>
        </article>
      </div>
    </div>
  </section>

  <section className="doc-sec" id="ringkasan" aria-labelledby="ringkasan-h">
    <div className="wrap">
      <div className="doc-head"><h2 id="ringkasan-h">Ringkasan ancaman pidana</h2><p>Ancaman terberat menurut tiap pasal</p></div>
      <div className="doc-tabel-wrap">
        <table className="doc-tabel">
          <thead><tr><th scope="col">Pasal</th><th scope="col">Perbuatan</th><th scope="col">Ancaman maksimal</th></tr></thead>
          <tbody>
            <tr><td>UU ITE Pasal 28 (2)</td><td>Menyebarkan kebencian berdasarkan identitas secara elektronik</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
            <tr><td>UU ITE Pasal 28 (3)</td><td>Berita bohong yang menimbulkan kerusuhan</td><td>6 tahun dan/atau Rp1 miliar</td></tr>
            <tr><td>UU 40/2008 Pasal 16</td><td>Kebencian karena perbedaan ras dan etnis</td><td>5 tahun dan/atau Rp500 juta</td></tr>
            <tr><td>UU ITE Pasal 29</td><td>Ancaman kekerasan atau menakut-nakuti secara pribadi</td><td>4 tahun dan/atau Rp750 juta</td></tr>
            <tr><td>KUHP Pasal 243</td><td>Menyebarluaskan konten permusuhan terhadap golongan</td><td>4 tahun atau Rp200 juta</td></tr>
            <tr><td>KUHP Pasal 242</td><td>Penghinaan terhadap golongan penduduk</td><td>3 tahun atau Rp200 juta</td></tr>
            <tr><td>KUHP Pasal 300</td><td>Permusuhan atas dasar agama atau kepercayaan</td><td>3 tahun atau Rp200 juta</td></tr>
            <tr><td>UU ITE Pasal 27A</td><td>Menyerang kehormatan atau nama baik (delik aduan)</td><td>2 tahun dan/atau Rp400 juta</td></tr>
          </tbody>
        </table>
      </div>
      <p className="doc-catatan">Penjelasan di halaman ini adalah ringkasan untuk edukasi, bukan nasihat hukum. Untuk keperluan hukum, rujuk naskah resmi peraturan dan putusan pengadilan yang berlaku.</p>
    </div>
  </section>

</main>
  );
}
