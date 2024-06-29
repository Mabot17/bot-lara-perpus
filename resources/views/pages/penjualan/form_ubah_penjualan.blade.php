@extends('layout.main_layout')
@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Ubah Transaksi Penjualan</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            @csrf
                            <a href="{{ route('penjualan') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="createForm" action="{{ url('/api/penjualan/update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="penjualanNo">No. Penjualan</label>
                                    <input type="text" id="penjualanNo" name="penjualan_no" placeholder="(Otomatis)" class="form-control" readonly>
                                    <input type="text" id="penjualanId" name="penjualan_id" class="form-control" value="" hidden>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="penjualanTanggal">Tanggal</label>
                                    <input type="date" id="penjualanTanggal" name="penjualan_tanggal" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="penjualanPelanggan">Pelanggan</label>
                                    <input type="text" id="penjualanPelanggan" name="penjualan_pelanggan" class="form-control" value="Pelanggan Umum">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Produk Penjualan</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            <label for="penjualanSKU" class="me-2 mb-0">Scan SKU : </label>
                            <input type="text" id="penjualanSKU" name="penjualanSku" class="form-control mr-3" style="width: auto;">
                            <button type="button" class="btn mb-2 btn-primary d-flex flex-wrap align-items-left fe fe-plus-circle" data-toggle="modal" data-target="#insertProdukModal"></button>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <table class="table table-borderless table-striped" id="tableTransaksiProduk">
                                <thead>
                                    <tr role="row">
                                        <th>Id</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Diskon (%)</th>
                                        <th>Diskon (Rp)</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="caraBayar" class="me-2 mb-0">Cara Bayar</label>
                                        <select id="caraBayar" name="penjualan_cara_bayar" class="form-control" required>
                                            <option value="Tunai">Tunai</option>
                                            <option value="Kartu">Kartu</option>
                                            <option value="Transfer">Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="penjualanTotalBayar" class="me-2 mb-0">Jumlah Bayar</label>
                                        <input type="text" id="penjualanTotalBayar" name="penjualan_total_bayar" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="penjualanTotal" class="me-2 mb-0">Total</label>
                                        <input type="text" id="penjualanTotal" name="penjualan_total" class="form-control"readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="penjualanTotalKembalian" class="me-2 mb-0">Kembalian</label>
                                        <input type="text" id="penjualanTotalKembalian" name="penjualan_total_kembalian" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <div class="d-flex flex-wrap align-items-center">
                            <button type="button" class="btn btn-danger mr-3" onclick="resetForm()"><i class="fe fe-x fe-16 mr-1"></i>Reset</button>
                            <button type="button" class="btn btn-secondary" onclick="submitForm()"><i class="fe fe-save fe-16 mr-1"></i>Simpan & Cetak</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Sukses!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-12 mb-4">
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">Produk Penjualan Berhasil disimpan !</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal insert produk -->
    <div class="modal fade" id="insertProdukModal" tabindex="-1" role="dialog" aria-labelledby="insertProdukModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertProdukModalLabel">Tambah Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="produkForm">
                        <div class="form-group">
                            <label for="modalProdukNama">Nama Produk</label>
                            <select class="form-control" id="modalProdukNama" required></select>
                        </div>
                        <div class="form-group">
                            <label for="modalProdukJumlah">Jumlah</label>
                            <input type="number" class="form-control" id="modalProdukJumlah" required>
                        </div>
                        <div class="form-group">
                            <label for="modalProdukHarga">Harga</label>
                            <input type="number" class="form-control" id="modalProdukHarga" required>
                        </div>
                        <div class="form-group">
                            <label for="modalProdukDiskonPersen">Diskon (%)</label>
                            <input type="number" class="form-control" id="modalProdukDiskonPersen">
                        </div>
                        <div class="form-group">
                            <label for="modalProdukDiskonRp">Diskon (Rp)</label>
                            <input type="number" class="form-control" id="modalProdukDiskonRp">
                        </div>
                        <input type="hidden" id="modalProdukIndex">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn mb-2 btn-primary" id="saveProdukButton">Save changes</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date();
            var day = ("0" + today.getDate()).slice(-2);
            var month = ("0" + (today.getMonth() + 1)).slice(-2);
            var todayString = today.getFullYear() + "-" + month + "-" + day;
            document.getElementById('penjualanTanggal').value = todayString;

            // Fetch product list and populate dropdown
            fetchProductList();
            fetchPenjualanList();
        });

        let produkList = [];
        let editingIndex = -1;
        let productData = {};

        document.getElementById('saveProdukButton').addEventListener('click', function() {
            const selectedProductId = document.getElementById('modalProdukNama').value;
            const product = productData[selectedProductId];

            const jumlah = parseInt(document.getElementById('modalProdukJumlah').value);
            const harga = parseFloat(document.getElementById('modalProdukHarga').value);
            const diskonPersen = parseFloat(document.getElementById('modalProdukDiskonPersen').value) || 0;
            const diskonRp = parseFloat(document.getElementById('modalProdukDiskonRp').value) || 0;

            const subtotal = (harga - diskonRp) * jumlah * ((100 - diskonPersen) / 100);

            const produk = {
                pjual_detail_id          : 0,
                pjual_detail_produk_id   : product.produk_id,
                pjual_detail_produk_nama : product.produk_nama,
                pjual_detail_qty         : jumlah,
                pjual_detail_harga       : harga,
                pjual_detail_diskon      : diskonPersen,
                pjual_detail_diskon_rp   : diskonRp,
                pjual_diskon_subtotal    : subtotal
            };

            if (editingIndex === -1) {
                produkList.push(produk);
            } else {
                produkList[editingIndex] = produk;
                editingIndex = -1;
            }

            document.getElementById('produkForm').reset();
            $('#insertProdukModal').modal('hide');
            renderProdukTable();
            updateTotal();
        });

        function fetchPenjualanList (){
            var token = localStorage.getItem('token');

            if (!token) {
                window.location.href = '/login'; // Redirect to login if token is not found
                return;
            }

            // Mengambil kategori_id dari URL dengan menggunakan URLSearchParams
            const penjualanId = "{{ Request::segment(3) }}";

            // Menggunakan penjualanId dari URL untuk fetch data produk kategori dari API
            fetch(`/api/penjualan/detail/${penjualanId}`, {  // Sesuaikan dengan nama parameter yang benar
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal mengambil data produk kategori');
                }
                return response.json();
            })
            .then(data => {
                $('#penjualanId').val(data.data.penjualan_id);
                $('#penjualanNo').val(data.data.penjualan_no);
                $('#penjualanTanggal').val(data.data.penjualan_tanggal);
                $('#penjualanPelanggan').val(data.data.penjualan_pelanggan);
                $('#penjualanTotal').val(data.data.penjualan_total);
                $('#penjualanTotalBayar').val(data.data.penjualan_total_bayar);
                $('#penjualanTotalKembalian').val(data.data.penjualan_total_kembalian);

                produkList = data.data.dataProdukList;
                renderProdukTable();

            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data produk kategori.');
                window.location.href = '/penjualan'; // Redirect pada error
            });
        }

        function fetchProductList() {
            var token = localStorage.getItem('token');

            fetch('/api/produk/list', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start: 0,
                    limit: 0,
                    filter: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                populateProductDropdown(data.data);
            })
            .catch(error => console.error('Error fetching products:', error));
        }

        function populateProductDropdown(data) {
            const productDropdown = document.getElementById('modalProdukNama');
            productDropdown.innerHTML = ''; // Clear existing options
            data.forEach(product => {
                productData[product.produk_id] = product;
                const option = document.createElement('option');
                option.value = product.produk_id;
                option.text = product.produk_nama;
                productDropdown.appendChild(option);
            });

            // Add event listener for product selection
            productDropdown.addEventListener('change', function() {
                const selectedProductId = this.value;
                const product = productData[selectedProductId];
                if (product) {
                    document.getElementById('modalProdukHarga').value = product.produk_harga;
                    document.getElementById('modalProdukJumlah').value = 1; // Default to 1
                    document.getElementById('modalProdukDiskonPersen').value = 0;
                    document.getElementById('modalProdukDiskonRp').value = 0;
                }
            });

            // Trigger change event to set initial values
            productDropdown.dispatchEvent(new Event('change'));
        }

        function renderProdukTable() {
            const tableBody = document.querySelector('#tableTransaksiProduk tbody');
            tableBody.innerHTML = '';

            produkList.forEach((produk, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${produk.pjual_detail_produk_id}</td>
                    <td>${produk.pjual_detail_produk_nama}</td>
                    <td>${produk.pjual_detail_qty}</td>
                    <td>${produk.pjual_detail_harga}</td>
                    <td>${produk.pjual_detail_diskon}</td>
                    <td>${produk.pjual_detail_diskon_rp}</td>
                    <td>${produk.pjual_diskon_subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editProduk(${index})"><i class="fe fe-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduk(${index})"><i class="fe fe-trash-2"></i></button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function editProduk(index) {
            const produk = produkList[index];
            const productId = Object.keys(productData).find(id => productData[id].produk_nama === produk.pjual_detail_produk_nama);

            document.getElementById('modalProdukNama').value = productId;
            document.getElementById('modalProdukJumlah').value = produk.pjual_detail_qty;
            document.getElementById('modalProdukHarga').value = produk.pjual_detail_harga;
            document.getElementById('modalProdukDiskonPersen').value = produk.pjual_detail_diskon;
            document.getElementById('modalProdukDiskonRp').value = produk.pjual_detail_diskon_rp;
            document.getElementById('modalProdukIndex').value = index;
            editingIndex = index;

            $('#insertProdukModal').modal('show');
        }

        function deleteProduk(index) {
            produkList.splice(index, 1);
            renderProdukTable();
            updateTotal();
        }

        function updateTotal() {
            const total = produkList.reduce((acc, produk) => acc + produk.pjual_diskon_subtotal, 0);
            document.getElementById('penjualanTotal').value = total.toFixed(2);
            updateKembalian();
        }

        document.getElementById('penjualanTotalBayar').addEventListener('input', updateKembalian);

        function updateKembalian() {
            const total = parseFloat(document.getElementById('penjualanTotal').value) || 0;
            const bayar = parseFloat(document.getElementById('penjualanTotalBayar').value) || 0;
            const kembalian = bayar - total;
            document.getElementById('penjualanTotalKembalian').value = kembalian.toFixed(2);
        }

        function resetForm() {
            document.getElementById("createForm").reset();
            produkList = [];
            renderProdukTable();
            updateTotal();
        }

        function submitForm() {
            var form = document.getElementById("createForm");
            var token = localStorage.getItem('token'); // Ambil token dari localStorage
            var formData = {
                penjualan_id: document.getElementById('penjualanId').value,
                penjualan_no: document.getElementById('penjualanNo').value,
                penjualan_tanggal: document.getElementById('penjualanTanggal').value,
                penjualan_pelanggan: document.getElementById('penjualanPelanggan').value,
                penjualan_total: document.getElementById('penjualanTotal').value,
                penjualan_total_bayar: document.getElementById('penjualanTotalBayar').value,
                penjualan_cara_bayar: document.getElementById('caraBayar').value,
                penjualan_total_kembalian: document.getElementById('penjualanTotalKembalian').value,
                produk_list: produkList
            };

            fetch(form.action, {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json()) // Menguraikan JSON dari respons
            .then(data => {
                $('#successModal').modal('show');
                var printUrl = `/api/penjualan/cetak-faktur-pdf/` + data.data.id;
                prinFakturPenjualan(printUrl);
                setTimeout(function() {
                    $('#successModal').modal('hide');
                    window.location.href = '/penjualan';
                }, 2000); // Menutup modal setelah 2 detik
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            });
        }

        function prinFakturPenjualan(printUrl) {
            var token = localStorage.getItem('token'); // Ambil token dari localStorage
            $.ajax({
                url: printUrl, // Sesuaikan dengan endpoint delete
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                success: function(response) {
                    window.open(response.url, '_blank');
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting data:', error);
                    alert('Terjadi kesalahan saat menghapus produk penjualan.');
                }
            });
        }
    </script>

@endsection
