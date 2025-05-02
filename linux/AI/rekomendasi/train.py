import torch
import torch.nn as nn
import torch.optim as optim
import pandas as pd
import numpy as np

# Data dummy
data = [
    ['cust_1', 'Nasi Goreng', 1],
    ['cust_1', 'Es Teh', 1],
    ['cust_1', 'Ayam Bakar', 1],
    ['cust_2', 'Nasi Goreng', 1],
    ['cust_2', 'Mie Ayam', 1],
    ['cust_2', 'Es Jeruk', 1],
    ['cust_3', 'Sate Ayam', 1],
    ['cust_3', 'Nasi Goreng', 1],
    ['cust_3', 'Ayam Bakar', 1],
    ['cust_4', 'Mie Ayam', 1],
    ['cust_4', 'Es Teh', 1],
    ['cust_4', 'Sate Ayam', 1],
    ['cust_5', 'Mie Ayam', 1],
    ['cust_5', 'Ayam Bakar', 1],
    ['cust_5', 'Es Teh', 1],
]

df = pd.DataFrame(data, columns=["customer_id", "menu_item", "label"])

# Encode ke angka
customer_mapping = {id_: idx for idx, id_ in enumerate(df['customer_id'].unique())}
menu_mapping = {id_: idx for idx, id_ in enumerate(df['menu_item'].unique())}

df['customer_encoded'] = df['customer_id'].map(customer_mapping)
df['menu_encoded'] = df['menu_item'].map(menu_mapping)

# Dataset
customer_tensor = torch.tensor(df['customer_encoded'].values, dtype=torch.long)
menu_tensor = torch.tensor(df['menu_encoded'].values, dtype=torch.long)
label_tensor = torch.tensor(df['label'].values, dtype=torch.float32)

# Model
class RecommenderNet(nn.Module):
    def __init__(self, num_customers, num_menus, embedding_size=8):
        super(RecommenderNet, self).__init__()
        self.customer_embedding = nn.Embedding(num_customers, embedding_size)
        self.menu_embedding = nn.Embedding(num_menus, embedding_size)
        
        self.fc1 = nn.Linear(embedding_size * 2, 64)
        self.fc2 = nn.Linear(64, 32)
        self.fc3 = nn.Linear(32, 1)
        self.sigmoid = nn.Sigmoid()

    def forward(self, customer, menu):
        customer_emb = self.customer_embedding(customer)
        menu_emb = self.menu_embedding(menu)
        x = torch.cat([customer_emb, menu_emb], dim=1)
        x = torch.relu(self.fc1(x))
        x = torch.relu(self.fc2(x))
        x = self.sigmoid(self.fc3(x))
        return x.squeeze()

# Inisialisasi model
num_customers = len(customer_mapping)
num_menus = len(menu_mapping)
model = RecommenderNet(num_customers, num_menus)

# Training
optimizer = optim.Adam(model.parameters(), lr=0.01)
criterion = nn.BCELoss()

epochs = 30
for epoch in range(epochs):
    model.train()
    optimizer.zero_grad()
    outputs = model(customer_tensor, menu_tensor)
    loss = criterion(outputs, label_tensor)
    loss.backward()
    optimizer.step()

    if (epoch+1) % 5 == 0:
        print(f'Epoch [{epoch+1}/{epochs}], Loss: {loss.item():.4f}')

# Save model
torch.save(model.state_dict(), 'model.pth')
print("Model saved as model.pth")
