<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />

	<title>Login</title>

	<link
		href="<?= base_url('Public/Panel/') ?>vendor/fontawesome-free/css/all.min.css"
		rel="stylesheet"
		type="text/css" />
	<link
		href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
		rel="stylesheet" />

	<!-- Custom styles for this template-->
	<link
		href="<?= base_url('Public/Panel/') ?>css/sb-admin-2.min.css"
		rel="stylesheet" />
</head>

<body class="bg-gradient-light">
	<div class="container" style="padding-top: 7%;">
		<!-- Outer Row -->
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-12 col-md-9">
				<div class="card o-hidden border-0 shadow-lg my-5">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
							<div class="col-lg-12">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
									</div>
									<form class="user">
										<div class="form-group">
											<input
												type="email"
												class="form-control form-control-user"
												id="exampleInputEmail"
												aria-describedby="emailHelp"
												placeholder="Enter Email Address..." />
										</div>
										<div class="form-group">
											<input
												type="password"
												class="form-control form-control-user"
												id="exampleInputPassword"
												placeholder="Password" />
										</div>
										<div class="form-group">
											<div class="custom-control custom-checkbox small">
												<input
													type="checkbox"
													class="custom-control-input"
													id="customCheck" />
												<label class="custom-control-label" for="customCheck">Remember Me</label>
											</div>
										</div>
										<a
											href="index.html"
											class="btn btn-info btn-user btn-block">
											Login
										</a>

									</form>
									<hr />
									<div class="text-center">
										<a class="small" href="forgot-password.html">Forgot Password?</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		// File: assets/js/crud-ajax.js

		document.addEventListener('DOMContentLoaded', function() {
			// Base URL untuk API
			const API_URL = `${BASE_URL}api/items`; // Pastikan BASE_URL didefinisikan di view CI3 Anda

			// Elemen DOM
			const itemsList = document.getElementById('items-list');
			const itemForm = document.getElementById('item-form');
			const submitBtn = document.getElementById('submit-btn');
			const searchInput = document.getElementById('search-input');

			let editMode = false;
			let currentItemId = null;

			// Ambil semua item saat halaman dimuat
			fetchItems();

			// Event listener untuk form submit
			itemForm.addEventListener('submit', function(e) {
				e.preventDefault();
				const formData = new FormData(itemForm);

				if (editMode) {
					updateItem(currentItemId, formData);
				} else {
					createItem(formData);
				}
			});

			// Event listener untuk pencarian
			if (searchInput) {
				searchInput.addEventListener('input', function() {
					fetchItems(searchInput.value);
				});
			}

			// FETCH API FUNCTIONS

			// Ambil semua item
			function fetchItems(search = '') {
				let url = API_URL;
				if (search) {
					url += `?search=${encodeURIComponent(search)}`;
				}

				fetch(url)
					.then(response => {
						if (!response.ok) {
							throw new Error('Terjadi kesalahan saat mengambil data');
						}
						return response.json();
					})
					.then(data => {
						displayItems(data);
					})
					.catch(error => {
						showAlert(error.message, 'danger');
					});
			}

			// Tampilkan item di tabel
			function displayItems(items) {
				itemsList.innerHTML = '';

				if (items.length === 0) {
					itemsList.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>';
					return;
				}

				items.forEach(item => {
					const row = document.createElement('tr');
					row.innerHTML = `
                <td>${item.id}</td>
                <td>${item.name}</td>
                <td>${item.description}</td>
                <td>
                    <button class="btn btn-sm btn-info edit-btn" data-id="${item.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">Hapus</button>
                </td>
            `;
					itemsList.appendChild(row);
				});

				// Tambahkan event listener untuk tombol edit dan delete
				document.querySelectorAll('.edit-btn').forEach(btn => {
					btn.addEventListener('click', function() {
						const id = this.getAttribute('data-id');
						fetchItemDetails(id);
					});
				});

				document.querySelectorAll('.delete-btn').forEach(btn => {
					btn.addEventListener('click', function() {
						const id = this.getAttribute('data-id');
						if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
							deleteItem(id);
						}
					});
				});
			}

			// Ambil detail item untuk diedit
			function fetchItemDetails(id) {
				fetch(`${API_URL}/${id}`)
					.then(response => {
						if (!response.ok) {
							throw new Error('Terjadi kesalahan saat mengambil detail item');
						}
						return response.json();
					})
					.then(item => {
						// Isi form dengan data item
						document.getElementById('name').value = item.name;
						document.getElementById('description').value = item.description;

						// Ubah mode ke edit
						editMode = true;
						currentItemId = id;
						submitBtn.textContent = 'Update';

						// Scroll ke form
						itemForm.scrollIntoView({
							behavior: 'smooth'
						});
					})
					.catch(error => {
						showAlert(error.message, 'danger');
					});
			}

			// Buat item baru
			function createItem(formData) {
				const data = {
					name: formData.get('name'),
					description: formData.get('description')
				};

				fetch(API_URL, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-Requested-With': 'XMLHttpRequest'
						},
						body: JSON.stringify(data)
					})
					.then(response => {
						if (!response.ok) {
							throw new Error('Terjadi kesalahan saat membuat item');
						}
						return response.json();
					})
					.then(result => {
						showAlert('Item berhasil ditambahkan', 'success');
						itemForm.reset();
						fetchItems();
					})
					.catch(error => {
						showAlert(error.message, 'danger');
					});
			}

			// Update item
			function updateItem(id, formData) {
				const data = {
					name: formData.get('name'),
					description: formData.get('description')
				};

				fetch(`${API_URL}/${id}`, {
						method: 'PUT',
						headers: {
							'Content-Type': 'application/json',
							'X-Requested-With': 'XMLHttpRequest'
						},
						body: JSON.stringify(data)
					})
					.then(response => {
						if (!response.ok) {
							throw new Error('Terjadi kesalahan saat mengupdate item');
						}
						return response.json();
					})
					.then(result => {
						showAlert('Item berhasil diupdate', 'success');
						itemForm.reset();
						submitBtn.textContent = 'Simpan';
						editMode = false;
						currentItemId = null;
						fetchItems();
					})
					.catch(error => {
						showAlert(error.message, 'danger');
					});
			}

			// Hapus item
			function deleteItem(id) {
				fetch(`${API_URL}/${id}`, {
						method: 'DELETE',
						headers: {
							'X-Requested-With': 'XMLHttpRequest'
						}
					})
					.then(response => {
						if (!response.ok) {
							throw new Error('Terjadi kesalahan saat menghapus item');
						}
						return response.json();
					})
					.then(result => {
						showAlert('Item berhasil dihapus', 'success');
						fetchItems();
					})
					.catch(error => {
						showAlert(error.message, 'danger');
					});
			}

			// Tampilkan alert
			function showAlert(message, type) {
				const alertDiv = document.createElement('div');
				alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
				alertDiv.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;

				const container = document.querySelector('.container');
				container.insertBefore(alertDiv, container.firstChild);

				// Otomatis hilangkan alert setelah 3 detik
				setTimeout(() => {
					alertDiv.remove();
				}, 3000);
			}
		});
	</script>
	<!-- Bootstrap core JavaScript-->
	<script src="<?= base_url('Public/Panel/') ?>vendor/jquery/jquery.min.js"></script>
	<script src="<?= base_url('Public/Panel/') ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- Core plugin JavaScript-->
	<script src="<?= base_url('Public/Panel/') ?>vendor/jquery-easing/jquery.easing.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="<?= base_url('Public/Panel/') ?>js/sb-admin-2.min.js"></script>
</body>

</html>