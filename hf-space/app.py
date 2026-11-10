"""RuangAman API - server model deteksi ujaran kebencian untuk Cek Hatespeech.

Kode ini dipindahkan dari notebook Colab (algoritmahatespeech.ipynb) tanpa
mengubah model, ambang batas, maupun format respons, supaya aplikasi web
(api/index.php) tetap bekerja seperti sebelumnya.
"""
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from transformers import pipeline

app = FastAPI(title="RuangAman API")

# Model dipakai apa adanya, tanpa modifikasi
clf = pipeline(
    "text-classification",
    model="nahiar/hatespeech-abusive-xlm-roberta-v1",
    top_k=None,
)
LABEL_MAP = {"LABEL_0": "HATESPEECH", "LABEL_1": "ABUSIVE"}  # hasil uji pemetaan label

# Ambang batas (aturan bisnis sistem, bukan bagian model)
T_KUAT, T_TINJAU, T_ABUSIVE = 0.8, 0.5, 0.8


def klasifikasi(hs: float, ab: float):
    if hs >= T_KUAT:
        return ("INDIKASI_KUAT", "Tinggi",
                "Kemungkinan besar berisi ujaran kebencian. Moderator akan memeriksa apakah "
                "sasarannya kelompok berdasarkan identitas (Pasal 28 ayat 2) atau perorangan.")
    if hs >= T_TINJAU:
        return ("PERLU_DITINJAU", "Sedang",
                "Sistem belum yakin. Laporan seperti ini tetap ditinjau oleh moderator.")
    if ab >= T_ABUSIVE:
        return ("BAHASA_KASAR", "Rendah",
                "Berisi kata kasar, tetapi tidak terdeteksi sebagai kebencian terhadap kelompok.")
    return ("TIDAK_TERINDIKASI", "Paling rendah",
            "Tidak terdeteksi ujaran kebencian maupun kata kasar.")


class Permintaan(BaseModel):
    teks: str


@app.get("/")
def cek_server():
    return {"status": "siap", "model": "nahiar/hatespeech-abusive-xlm-roberta-v1"}


@app.post("/predict")
def predict(data: Permintaan):
    teks = data.teks.strip()
    if not teks:
        raise HTTPException(status_code=400, detail="Teks tidak boleh kosong.")
    if len(teks) > 2000:
        raise HTTPException(status_code=400, detail="Teks maksimal 2000 karakter.")

    hasil = clf(teks, truncation=True)[0]
    skor = {LABEL_MAP[h["label"]]: round(h["score"], 4) for h in hasil}
    status, prioritas, penjelasan = klasifikasi(skor["HATESPEECH"], skor["ABUSIVE"])
    return {"skor": skor, "status": status, "prioritas": prioritas, "penjelasan": penjelasan}
