'use client';
import { useActionState } from 'react';
import { DAFTAR_STATUS, STATUS, type StatusLaporan } from '@/lib/kampus';
import { Panah } from '@/components/Panah';
import { ubahStatus } from './actions';

export function FormStatus({ id, kode, status, prioritas }: { id: string; kode: string; status: StatusLaporan; prioritas: string }) {
  const [hasil, aksi, sibuk] = useActionState(ubahStatus, null);
  return (
    <form action={aksi} className="kartu status-form">
      <h2>Tindak lanjut Satgas</h2>
      <input type="hidden" name="id" value={id} />
      <input type="hidden" name="kode" value={kode} />
      <div className="status-form__isian">
        <div className="f"><label htmlFor="status">Status</label>
          <select id="status" name="status" defaultValue={status}>{DAFTAR_STATUS.map((s) => <option key={s} value={s}>{STATUS[s].label}</option>)}</select>
        </div>
        <div className="f"><label htmlFor="prioritas">Prioritas</label>
          <select id="prioritas" name="prioritas" defaultValue={prioritas}><option value="tinggi">Tinggi</option><option value="sedang">Sedang</option><option value="normal">Normal</option></select>
        </div>
      </div>
      <div className="f"><label htmlFor="catatan">Catatan untuk pelapor</label><textarea id="catatan" name="catatan" maxLength={1000} placeholder="Misalnya jadwal pemanggilan, bukti tambahan yang dibutuhkan, atau alasan penolakan." /></div>
      {hasil && <p className={`pesan ${hasil.ok ? 'pesan--ok' : 'pesan--galat'}`} role="status">{hasil.pesan}</p>}
      <button className="btn btn--primary" type="submit" disabled={sibuk}>{sibuk ? 'Menyimpan…' : 'Simpan tindak lanjut'}<Panah /></button>
    </form>
  );
}
