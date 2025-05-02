<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kasir Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Admin Panel</h1>
        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
    </header>

    <main class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md p-4 hidden md:block">
            <nav class="space-y-2">
                <button class="block p-2 w-full text-left rounded hover:bg-gray-200" onclick="admin.showPage('dashboard')">Dashboard</button>
                <button class="block p-2 w-full text-left rounded hover:bg-gray-200" onclick="admin.showPage('products')">Produk</button>
                <button class="block p-2 w-full text-left rounded hover:bg-gray-200" onclick="admin.showPage('transactions')">Transaksi</button>
                <button class="block p-2 w-full text-left rounded hover:bg-gray-200" onclick="admin.showPage('users')">User Kasir</button>
                <button class="block p-2 w-full text-left rounded hover:bg-gray-200" onclick="admin.showPage('settings')">Pengaturan</button>
            </nav>
        </aside>

        <!-- Main Content -->
        <section class="flex-1 p-6">
            <div id="dashboard" class="content-page bg-white rounded-xl shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Dashboard</h2>
                <p class="text-gray-600">Statistik dan ringkasan.</p>
            </div>
            <div id="products" class="content-page hidden bg-white rounded-xl shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Manajemen Produk</h2>
                <p class="text-gray-600 mb-4">Tambah, ubah, dan hapus produk.</p>

                <form id="productForm" class="space-y-4 mb-6" onsubmit="saveProduct(event)">
                    <input type="hidden" id="productId" />
                    <input type="text" id="productName" placeholder="Nama Produk" required class="w-full px-4 py-2 border rounded-lg" />
                    <input type="number" id="productPrice" placeholder="Harga Produk" required class="w-full px-4 py-2 border rounded-lg" />
                    <input type="text" id="productImage" placeholder="URL Gambar" class="w-full px-4 py-2 border rounded-lg" />
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Simpan Produk
                    </button>
                </form>

                <div id="productTable" class="overflow-x-auto">
                    <table class="min-w-full table-auto text-left text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-2">Nama</th>
                                <th class="p-2">Harga</th>
                                <th class="p-2">Gambar</th>
                                <th class="p-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productListTable"></tbody>
                    </table>
                </div>
            </div>

            <div id="transactions" class="content-page hidden bg-white rounded-xl shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Riwayat Transaksi</h2>
                <p class="text-gray-600">Lihat daftar transaksi yang terjadi.</p>
            </div>
            <div id="users" class="content-page hidden bg-white rounded-xl shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Manajemen User Kasir</h2>
                <p class="text-gray-600">Kelola akun kasir dan akses mereka.</p>
            </div>
            <div id="settings" class="content-page hidden bg-white rounded-xl shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Pengaturan Sistem</h2>
                <p class="text-gray-600">Konfigurasi sistem kasir.</p>
            </div>
        </section>
    </main>

    <footer class="bg-white shadow p-4 text-center text-sm text-gray-500">
        &copy; 2025 Kasir Modern. Semua Hak Dilindungi.
    </footer>

    <script>
        class AdminPanel {
            constructor() {
                this.pages = ['dashboard', 'products', 'transactions', 'users', 'settings'];
                this.currentPage = 'dashboard';
                this.showPage(this.currentPage);
            }

            showPage(pageId) {
                this.pages.forEach(page => {
                    document.getElementById(page).classList.add('hidden');
                });
                document.getElementById(pageId).classList.remove('hidden');
                this.currentPage = pageId;
            }
        }

        const admin = new AdminPanel();
    </script>
    <script>
        const API_BASE = '/api/products'; // Sesuaikan dengan endpoint CI4 kamu

        async function fetchProducts() {
            const res = await fetch(API_BASE);
            const data = await res.json();
            renderProductTable(data);
        }

        function renderProductTable(products) {
            const tbody = document.getElementById('productListTable');
            tbody.innerHTML = '';
            products.forEach((product) => {
                tbody.innerHTML += `
        <tr>
          <td class="p-2">${product.name}</td>
          <td class="p-2">Rp ${parseInt(product.price).toLocaleString()}</td>
          <td class="p-2"><img src="${product.img}" class="w-16 h-16 object-cover rounded" /></td>
          <td class="p-2 space-x-2">
            <button onclick="editProduct(${product.id}, '${product.name}', '${product.price}', '${product.img}')" class="bg-yellow-400 px-2 py-1 rounded text-white">Edit</button>
            <button onclick="deleteProduct(${product.id})" class="bg-red-600 px-2 py-1 rounded text-white">Hapus</button>
          </td>
        </tr>
      `;
            });
        }

        async function saveProduct(event) {
            event.preventDefault();

            const id = document.getElementById('productId').value;
            const payload = {
                name: document.getElementById('productName').value,
                price: document.getElementById('productPrice').value,
                image: document.getElementById('productImage').value || 'https://via.placeholder.com/150',
            };

            const options = {
                method: id ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload),
            };

            const url = id ? `${API_BASE}/${id}` : API_BASE;
            await fetch(url, options);

            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            fetchProducts();
        }

        function editProduct(id, name, price, image) {
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('productPrice').value = price;
            document.getElementById('productImage').value = image;
        }

        async function deleteProduct(id) {
            if (confirm('Yakin ingin menghapus produk ini?')) {
                await fetch(`${API_BASE}/${id}`, {
                    method: 'DELETE'
                });
                fetchProducts();
            }
        }

        // Jalankan saat awal load
        fetchProducts();
    </script>

</body>

</html>