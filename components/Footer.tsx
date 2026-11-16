import { KAMPUS, PENGEMBANG } from '@/lib/kampus';
import { Panah } from './Panah';

/** Penutup (close-word) dan footer merah. */
export function Footer() {
  return (
    <footer className="footer closing footer--dark ground-inverse" id="contact" aria-labelledby="close-h">
      <div className="wrap">
        <div className="closing__cta close--word">
          <div className="section-head--split">
            <div>
              <h2 id="close-h">Buat laporan perundungan</h2>
              <p className="t-lead">Laporanmu tersimpan aman dan langsung masuk ke dasbor {KAMPUS.tujuan}.</p>
            </div>
            <a className="btn btn--primary" href="/lapor">Buat laporan<Panah /></a>
          </div>
          <p className="close__word" aria-hidden="true">Cek Hatespeech</p>
        </div>
        <div className="closing__links">
          <div className="grid">
            <div className="span-4 stack-4">
              <a className="nav__logo" href="/"><span className="mark" data-mark="dots" aria-hidden="true"></span>Cek Hatespeech</a>
              <p className="footer__lede">Aplikasi pelaporan perundungan untuk mahasiswa {KAMPUS.nama}. Tugas akhir {PENGEMBANG.prodi}, bukan layanan resmi kampus.</p>
            </div>
            <div className="span-2 start-7 footer__col"><h3>Aplikasi</h3><ul><li><a href="/">Beranda</a></li><li><a href="/tentang">Tentang</a></li><li><a href="/lapor">Buat laporan</a></li><li><a href="/masuk">Masuk</a></li></ul></div>
            <div className="span-2 footer__col"><h3>Panduan</h3><ul><li><a href="/#tanda">Tanda perundungan</a></li><li><a href="/#pasal">Pasal UU ITE</a></li><li><a href="/dasar-hukum">Dasar hukum</a></li></ul></div>
            <div className="span-2 footer__col"><h3>Sumber</h3><ul><li><a href="https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1" rel="noopener">Model di Hugging Face</a></li><li><a href="https://github.com/ArvanMurbiyanto10/cek-hatespeech" rel="noopener">Kode di GitHub</a></li></ul></div>
          </div>
          <div className="footer__legal">
            <span>&copy; {PENGEMBANG.tahun} {PENGEMBANG.nama}</span>
            <div className="socials"></div>
            <p className="credits">Foto via rawpixel, StockSnap dan Wikimedia Commons. <a href="/kredit">Kredit foto</a></p>
          </div>
        </div>
      </div>
    </footer>
  );
}
