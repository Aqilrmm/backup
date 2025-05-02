import whisper

# Load Whisper model
model = whisper.load_model("medium")  # Bisa diganti dengan "small", "medium", dst.

# Path ke file audio kamu
audio_path = "contoh_audio.mp3"

# Transkripsi dengan deteksi bahasa otomatis
result = model.transcribe(audio_path)

# Tampilkan bahasa yang terdeteksi
print(f"Bahasa terdeteksi: {result['language']}")

# Tampilkan hasil transkripsi
print("Hasil Transkripsi:")
print(result["text"])
