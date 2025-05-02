<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kasir Modern</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .scrollbar::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 6px;
    }
    .modal-bg {
      background-color: rgba(0, 0, 0, 0.5);
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

  <!-- Login Page -->
  <div id="loginPage" class="bg-white w-full max-w-sm p-6 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-4 text-center">Login Kasir</h2>
    <input id="username" type="text" placeholder="Username" class="w-full mb-3 px-4 py-2 border rounded-lg" />
    <input id="password" type="password" placeholder="Password" class="w-full mb-3 px-4 py-2 border rounded-lg" />
    <button onclick="login()" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Login</button>
    <p id="loginError" class="text-red-500 text-sm mt-2 text-center hidden">Username atau password salah</p>
  </div>

  <!-- Kasir Page -->
  <div id="kasirPage" class="hidden bg-white w-full max-w-7xl h-[90vh] rounded-2xl shadow-xl flex flex-col md:flex-row relative">

    <!-- Fullscreen Button -->
    <button onclick="toggleFullscreen()" class="absolute top-3 right-3 text-gray-600 hover:text-black transition text-2xl">
      ⛶
    </button>

    <!-- Product List -->
    <div class="md:w-2/3 w-full border-b md:border-b-0 md:border-r flex flex-col">
      <div class="p-4 border-b flex justify-between items-center">
        <input
          type="text"
          placeholder="Cari produk..."
          class="w-3/4 px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-400"
          id="searchInput"
        />
        <span class="text-sm text-gray-600">Kasir: <strong id="kasirName">-</strong></span>
      </div>
      <div class="p-4 overflow-y-auto scrollbar grid grid-cols-2 sm:grid-cols-3 gap-4 flex-1" id="productList"></div>
    </div>

    <!-- Cart Section -->
    <div class="md:w-1/3 w-full p-4 flex flex-col">
      <h2 class="text-xl font-semibold mb-4">Keranjang</h2>
      <div class="flex-1 overflow-y-auto scrollbar space-y-2 mb-4" id="cartItems"></div>
      <div class="mt-auto border-t pt-4">
        <div class="flex justify-between text-lg font-bold">
          <span>Total:</span>
          <span id="totalAmount">Rp 0</span>
        </div>
        <button
          class="mt-4 w-full bg-green-600 text-white py-2 rounded-xl text-lg hover:bg-green-700 transition"
          onclick="startCheckout()"
        >
          Bayar
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: Member -->
  <div id="memberModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-bg">
    <div class="bg-white p-6 rounded-xl shadow-lg w-96 text-center">
      <h3 class="text-lg font-semibold mb-4">Masukkan Nomor HP Member</h3>
      <input id="memberPhone" type="text" placeholder="08xxxxxxxxxx" class="w-full mb-4 px-4 py-2 border rounded-lg" />
      <div class="flex justify-center gap-4">
        <button onclick="skipMember()" class="bg-gray-400 text-white px-4 py-2 rounded-lg">Tanpa Member</button>
        <button onclick="nextPayment()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Lanjut</button>
      </div>
    </div>
  </div>

  <!-- Modal: Uang Customer -->
  <div id="paymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-bg">
    <div class="bg-white p-6 rounded-xl shadow-lg w-96 text-center">
      <h3 class="text-lg font-semibold mb-4">Masukkan Uang Customer</h3>
      <input id="cashInput" type="number" placeholder="Contoh: 50000" class="w-full mb-4 px-4 py-2 border rounded-lg" />
      <button onclick="finishPayment()" class="bg-green-600 text-white px-4 py-2 rounded-lg w-full">Bayar</button>
    </div>
  </div>

  <!-- Modal: Kembalian -->
  <div id="thanksModal" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-bg">
    <div class="bg-white p-6 rounded-xl shadow-lg w-96 text-center">
      <h3 class="text-2xl font-bold text-green-600 mb-4">Terima Kasih!</h3>
      <p class="mb-2">Kembalian: <strong id="changeAmount">Rp 0</strong></p>
      <button onclick="resetKasir()" class="bg-blue-600 text-white px-4 py-2 rounded-lg w-full mt-4">Tutup</button>
    </div>
  </div>

  <script>
    class ApiClient {
      async login(username, password) {
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ username, password }),
        });
        return response.json();
      }

      async getProducts() {
        const response = await fetch('/api/products');
        return response.json();
      }

      async checkout(cart, memberPhone) {
        const response = await fetch('/api/checkout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ cart, memberPhone }),
        });
        return response.json();
      }
    }

    class Kasir {
      constructor() {
        this.apiClient = new ApiClient();
        this.cart = [];
        this.totalHarga = 0;
        this.currentUser = '';
        this.memberPhone = '';
      }

      async login(username, password) {
        const data = await this.apiClient.login(username, password);
        if (data.success) {
          this.currentUser = username;
          document.getElementById('loginPage').classList.add('hidden');
          document.getElementById('kasirPage').classList.remove('hidden');
          document.getElementById('kasirName').innerText = username;
          this.renderProducts();
        } else {
          document.getElementById('loginError').classList.remove('hidden');
        }
      }

      async renderProducts(filter = '') {
        const products = await this.apiClient.getProducts();
        const container = document.getElementById('productList');
        container.innerHTML = '';
        products
          .filter(p => p.name.toLowerCase().includes(filter.toLowerCase()))
          .forEach((product) => {
            const div = document.createElement('div');
            div.className = 'bg-white rounded-xl shadow p-2 cursor-pointer hover:bg-blue-50 transition';
            div.innerHTML = `
              <img src="${product.img}" alt="${product.name}" class="rounded-lg h-32 w-full object-cover mb-2"/>
              <h3 class="text-md font-semibold text-gray-800">${product.name}</h3>
              <p class="text-sm text-gray-600">${this.formatRupiah(product.price)}</p>
            `;
            div.onclick = () => this.addToCart(product);
            container.appendChild(div);
          });
      }

      renderCart() {
        const cartContainer = document.getElementById('cartItems');
        cartContainer.innerHTML = '';
        this.totalHarga = 0;

        this.cart.forEach((item, index) => {
          this.totalHarga += item.price * item.qty;
          const row = document.createElement('div');
          row.className = 'flex justify-between items-center bg-gray-100 p-2 rounded-lg';
          row.innerHTML = `
            <div>
              <h4 class="font-medium">${item.name}</h4>
              <p class="text-sm text-gray-500">x${item.qty}</p>
            </div>
            <div class="text-right">
              <p class="font-semibold">${this.formatRupiah(item.price * item.qty)}</p>
              <button class="text-red-500 text-xs mt-1 hover:underline" onclick="kasir.removeItem(${index})">Hapus</button>
            </div>
          `;
          cartContainer.appendChild(row);
        });

        document.getElementById('totalAmount').innerText = this.formatRupiah(this.totalHarga);
      }

      formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
      }

      addToCart(product) {
        const found = this.cart.find(item => item.id === product.id);
        if (found) {
          found.qty += 1;
        } else {
          this.cart.push({ ...product, qty: 1 });
        }
        this.renderCart();
      }

      removeItem(index) {
        this.cart.splice(index, 1);
        this.renderCart();
      }

      async startCheckout() {
        if (this.cart.length === 0) return alert('Keranjang kosong!');
        document.getElementById('memberModal').classList.remove('hidden');
      }

      skipMember() {
        this.memberPhone = '';
        this.nextPayment();
      }

      nextPayment() {
        this.memberPhone = document.getElementById('memberPhone').value;
        document.getElementById('memberModal').classList.add('hidden');
        document.getElementById('paymentModal').classList.remove('hidden');
      }

      async finishPayment() {
        const cash = parseInt(document.getElementById('cashInput').value);
        if (isNaN(cash) || cash < this.totalHarga) {
          alert('Uang tidak cukup!');
          return;
        }
        const data = await this.apiClient.checkout(this.cart, this.memberPhone);
        const change = cash - this.totalHarga;
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('changeAmount').innerText = this.formatRupiah(change);
        document.getElementById('thanksModal').classList.remove('hidden');
      }

      resetKasir() {
        this.cart = [];
        document.getElementById('cashInput').value = '';
        document.getElementById('memberPhone').value = '';
        this.renderCart();
        document.getElementById('thanksModal').classList.add('hidden');
      }


    }

    const kasir = new Kasir();

    document.getElementById('searchInput').addEventListener('input', e => {
      kasir.renderProducts(e.target.value);
    });
    function toggleFullscreen() {
        const app = document.documentElement;
        if (!document.fullscreenElement) {
          app.requestFullscreen();
        } else {
          document.exitFullscreen();
        }
      }
    function login() {
      const user = document.getElementById('username').value;
      const pass = document.getElementById('password').value;
      kasir.login(user, pass);
    }
    
  </script>
</body>
</html>
