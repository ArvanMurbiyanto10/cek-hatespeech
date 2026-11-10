---
title: RuangAman API
emoji: 🛡️
colorFrom: blue
colorTo: gray
sdk: docker
app_port: 7860
pinned: false
license: apache-2.0
short_description: API deteksi ujaran kebencian untuk Cek Hatespeech
---

# RuangAman API

Server model untuk aplikasi web [Cek Hatespeech](https://cek-hatespeech.vercel.app/). API ini menjalankan model [`nahiar/hatespeech-abusive-xlm-roberta-v1`](https://huggingface.co/nahiar/hatespeech-abusive-xlm-roberta-v1) (XLM-RoBERTa, multilabel HATESPEECH dan ABUSIVE).

## Endpoint

| Metode | Path | Keterangan |
|---|---|---|
| GET | `/` | Cek server: `{"status": "siap", ...}` |
| POST | `/predict` | Body `{"teks": "..."}` (maksimal 2000 karakter) |
| GET | `/docs` | Dokumentasi interaktif (Swagger) |

Contoh respons `/predict`:

```json
{
  "skor": {"HATESPEECH": 0.9933, "ABUSIVE": 0.0027},
  "status": "INDIKASI_KUAT",
  "prioritas": "Tinggi",
  "penjelasan": "Kemungkinan besar berisi ujaran kebencian. ..."
}
```

## Aturan status

| Status | Syarat |
|---|---|
| `INDIKASI_KUAT` | skor HATESPEECH ≥ 0,8 |
| `PERLU_DITINJAU` | 0,5 ≤ skor HATESPEECH < 0,8 |
| `BAHASA_KASAR` | skor HATESPEECH < 0,5 dan skor ABUSIVE ≥ 0,8 |
| `TIDAK_TERINDIKASI` | selain itu |
