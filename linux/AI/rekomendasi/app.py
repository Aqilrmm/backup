from flask import Flask, request, jsonify
import torch
import torch.nn as nn
import torch.optim as optim
import numpy as np
import json

# Device
device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

# Dummy Data
customers = ['cust_1', 'cust_2', 'cust_3', 'cust_4']
menus = [
    {'name': 'Nasi Goreng', 'description': 'Nasi goreng dengan telur, ayam, dan kecap manis'},
    {'name': 'Mie Ayam', 'description': 'Mie dengan potongan ayam, sawi, dan kuah kaldu'},
    {'name': 'Sate Ayam', 'description': 'Tusuk sate ayam dengan bumbu kacang'},
    {'name': 'Es Teh', 'description': 'Teh manis dingin'},
    {'name': 'Es Jeruk', 'description': 'Jeruk segar dingin'},
    {'name': 'Bakso', 'description': 'Bola daging sapi dalam kuah hangat'},
    {'name': 'Soto', 'description': 'Sup ayam kuning dengan bihun dan kentang'},
    {'name': 'Ayam Bakar', 'description': 'Ayam bakar manis pedas'}
]

customer2idx = {c: idx for idx, c in enumerate(customers)}
menu2idx = {m['name']: idx for idx, m in enumerate(menus)}

# Dummy history pembelian
purchase_history = {
    0: [0, 1],    # cust_1 beli Nasi Goreng dan Mie Ayam
    1: [2, 3],    # cust_2 beli Sate Ayam dan Es Teh
    2: [1, 5],    # cust_3 beli Mie Ayam dan Bakso
    3: [6, 7],    # cust_4 beli Soto dan Ayam Bakar
}

# Model
class RecommenderNet(nn.Module):
    def __init__(self, num_customers, num_menus, embedding_size=10):
        super(RecommenderNet, self).__init__()
        self.customer_embedding = nn.Embedding(num_customers, embedding_size)
        self.menu_embedding = nn.Embedding(num_menus, embedding_size)
        self.output = nn.Linear(embedding_size * 2, 1)
        self.sigmoid = nn.Sigmoid()

    def forward(self, customer_ids, menu_ids):
        customer_embeds = self.customer_embedding(customer_ids)
        menu_embeds = self.menu_embedding(menu_ids)
        combined = torch.cat([customer_embeds, menu_embeds], dim=1)
        out = self.output(combined)
        return self.sigmoid(out)

# Load model
model = RecommenderNet(num_customers=len(customers), num_menus=len(menus)).to(device)
model.load_state_dict(torch.load('model.pth', map_location=device))
model.eval()

# Flask App
app = Flask(__name__)

def recommend_based_on_history(customer_id):
    """Rekomendasi berdasarkan history belanja."""
    history = purchase_history.get(customer_id, [])
    purchased_set = set(history)
    candidate_menus = [idx for idx in range(len(menus)) if idx not in purchased_set]
    
    customer_tensor = torch.tensor([customer_id], dtype=torch.long).to(device)
    scores = []
    for idx in candidate_menus:
        menu_tensor = torch.tensor([idx], dtype=torch.long).to(device)
        with torch.no_grad():
            score = model(customer_tensor, menu_tensor).item()
            scores.append((menus[idx]['name'], score))

    scores.sort(key=lambda x: x[1], reverse=True)
    return scores[:5]

def recommend_similar_menu(last_purchased_idx):
    """Rekomendasi berdasarkan kemiripan deskripsi menu."""
    # Untuk simple, pakai perbandingan panjang deskripsi (dummy similarity)
    target_desc_len = len(menus[last_purchased_idx]['description'])
    similarity = []
    for idx, menu in enumerate(menus):
        if idx != last_purchased_idx:
            sim_score = 1 - abs(len(menu['description']) - target_desc_len) / max(len(menu['description']), target_desc_len)
            similarity.append((menu['name'], sim_score))
    similarity.sort(key=lambda x: x[1], reverse=True)
    return similarity[:5]

def recommend_popular():
    """Fallback jika history kosong."""
    # Misal menu yang paling sering dibeli (hardcoded dummy)
    popular = ['Nasi Goreng', 'Mie Ayam', 'Sate Ayam', 'Bakso', 'Soto']
    return [(m, 0.9) for m in popular]

@app.route('/recommend', methods=['POST'])
def recommend():
    data = request.get_json()
    customer_name = data.get('customer_id')

    if customer_name not in customer2idx:
        return jsonify({'error': 'Invalid customer ID'}), 400

    customer_id = customer2idx[customer_name]

    recommendations = []

    if purchase_history.get(customer_id):
        # 1. Rekomendasi berdasarkan history
        history_based = recommend_based_on_history(customer_id)
        recommendations.append({
            'type': 'history_based',
            'recommendations': [{'menu_name': menu, 'score': score} for menu, score in history_based]
        })

        # 2. Rekomendasi berdasarkan kemiripan
        last_purchase = purchase_history[customer_id][-1]
        similar_based = recommend_similar_menu(last_purchase)
        recommendations.append({
            'type': 'similar_menu',
            'recommendations': [{'menu_name': menu, 'similarity_score': score} for menu, score in similar_based]
        })
    else:
        # 3. Rekomendasi popular jika tidak ada history
        popular_based = recommend_popular()
        recommendations.append({
            'type': 'popular',
            'recommendations': [{'menu_name': menu, 'popularity_score': score} for menu, score in popular_based]
        })

    return jsonify({'customer_id': customer_name, 'recommendations': recommendations})

if __name__ == '__main__':
    app.run(debug=True)
