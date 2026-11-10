FROM python:3.11-slim

# Hugging Face Spaces menjalankan container sebagai user dengan UID 1000
RUN useradd -m -u 1000 user
USER user
ENV HOME=/home/user \
    PATH=/home/user/.local/bin:$PATH \
    HF_HOME=/home/user/.cache/huggingface \
    PYTHONUNBUFFERED=1
WORKDIR /home/user/app

COPY --chown=user requirements.txt .
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# Unduh model saat build, supaya server cepat siap ketika dinyalakan/bangun dari tidur
RUN python -c "from transformers import pipeline; pipeline('text-classification', model='nahiar/hatespeech-abusive-xlm-roberta-v1', top_k=None)"

COPY --chown=user app.py .

EXPOSE 7860
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "7860"]
