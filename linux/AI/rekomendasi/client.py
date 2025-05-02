import requests
import json
import time
import sys

def loading_animation(message, duration=3):
    for _ in range(duration):
        for frame in "|/-\\":
            sys.stdout.write(f"\r{message} {frame}")
            sys.stdout.flush()
            time.sleep(0.2)
    sys.stdout.write("\r" + " " * (len(message) + 2) + "\r")  # clear line

def get_recommendations(customer_id):
    url = "http://127.0.0.1:5000/recommend"
    headers = {
        "Content-Type": "application/json"
    }
    data = {
        "customer_id": customer_id
    }

    try:
        print("[INFO] Mengirim permintaan rekomendasi ke server...")
        loading_animation("Menunggu server membalas", duration=5)

        response = requests.post(url, headers=headers, json=data)
        response.raise_for_status()  # cek error HTTP

        result = response.json()
        recommendations = result.get("recommendations", [])

        print(f"\n[SUKSES] Rekomendasi untuk Customer ID {customer_id}:")

        for section in recommendations:
            rec_type = section.get('type')
            rec_list = section.get('recommendations', [])

            # Judul sesuai tipe rekomendasi
            if rec_type == 'history_based':
                print("\n🔵 Rekomendasi Berdasarkan Riwayat Pembelian:")
                for idx, rec in enumerate(rec_list, 1):
                    print(f"{idx}. {rec['menu_name']} (skor: {rec['score']:.2f})")

            elif rec_type == 'similar_menu':
                print("\n🟢 Rekomendasi Menu Serupa:")
                for idx, rec in enumerate(rec_list, 1):
                    print(f"{idx}. {rec['menu_name']} (similarity: {rec['similarity_score']:.2f})")

            elif rec_type == 'popular':
                print("\n🟠 Rekomendasi Menu Populer:")
                for idx, rec in enumerate(rec_list, 1):
                    print(f"{idx}. {rec['menu_name']} (popularity: {rec['popularity_score']:.2f})")

            else:
                print("\n⚪ Tipe rekomendasi tidak dikenali.")
    
    except requests.exceptions.RequestException as e:
        print("\n[ERROR] Gagal mendapatkan rekomendasi:", e)

if __name__ == "__main__":
    print("=== Sistem Rekomendasi Menu ===")
    cust_id = input("Masukkan Customer ID (contoh: cust_1): ")
    get_recommendations(cust_id)
