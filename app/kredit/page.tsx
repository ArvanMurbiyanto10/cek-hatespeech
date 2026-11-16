import './kredit.css';

export const metadata = { title: 'Kredit foto', description: 'Sumber dan lisensi foto yang dipakai di Cek Hatespeech.' };

const KREDIT: [string, string, string][] = [
  ['Perempuan memakai media sosial (hero.jpg)', 'rawpixel, CC0', 'https://www.rawpixel.com/image/5921379/photo-image-public-domain-student-woman'],
  ['Penggunaan media digital (tentang.jpg)', 'Rawpixel.com via Wikimedia Commons, CC0', 'https://commons.wikimedia.org/w/index.php?curid=78993089'],
  ['People Woman (hate-speech.jpg)', 'Siddharth Bhogra via StockSnap, CC0', 'https://stocksnap.io/photo/people-woman-NXWB8SXF45'],
  ['Palu hakim di ruang sidang (hukum.jpg)', 'Joe Gratz via Wikimedia Commons, CC0', 'https://commons.wikimedia.org/w/index.php?curid=91844335'],
  ['Logo Telkom University', 'Hilfans via Wikimedia Commons, CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Logo_Telkom_University_potrait.png'],
];

export default function Halaman() {
  return (
<main id="top">
  <section className="doc-kredit" aria-labelledby="kredit-h">
    <div className="wrap">
      <h1 id="kredit-h">Kredit foto Cek Hatespeech</h1>
      <ul className="rows">
        {KREDIT.map(([isi, sumber, url]) => (
          <li key={url}><span className="rows__name">{isi}</span><span className="rows__value"><a href={url} rel="noopener">{sumber}</a></span></li>
        ))}
      </ul>
    </div>
  </section>
</main>
  );
}
