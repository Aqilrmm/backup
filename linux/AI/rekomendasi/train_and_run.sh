#!/bin/bash

# Aktifkan virtual environment
source venv/bin/activate

# Cek kalau model.pth belum ada
if [ ! -f "model.pth" ]; then
    echo "Model tidak ditemukan. Mulai training model dulu..."
    python3 train.py
else
    echo "Model sudah ada. Langsung ke server!"
fi

# Set environment variable untuk Flask
export FLASK_APP=app.py
export FLASK_ENV=development  # Enable auto-reload Flask kalau file berubah

# Jalankan Flask server
echo "Menjalankan server Flask dengan auto-reload..."
flask run --host=0.0.0.0 --port=5000
