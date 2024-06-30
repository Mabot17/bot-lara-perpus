@extends('layout.main_layout')
@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Ubah Transaksi Pengembalian</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            @csrf
                            <a href="{{ route('pengembalian') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="createForm" action="{{ url('/api/pengembalian/update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianNo">No. Pengembalian</label>
                                    <input type="text" id="pengembalianNo" name="pengembalian_no" placeholder="(Otomatis)" class="form-control" readonly>
                                    <input type="text" id="pengembalianId" name="pengembalian_id" class="form-control" value="" hidden>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianTanggal">Tanggal</label>
                                    <input type="date" id="pengembalianTanggal" name="pengembalian_tanggal" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianPelanggan">Pelanggan</label>
                                    <input type="text" id="pengembalianPelanggan" name="pengembalian_pelanggan" class="form-control" value="Pelanggan Umum">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Buku Pengembalian</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            <label for="pengembalianSKU" class="me-2 mb-0">Scan SKU : </label>
                            <input type="text" id="pengembalianSKU" name="pengembalianSku" class="form-control mr-3" style="width: auto;">
                            <button type="button" class="btn mb-2 btn-primary d-flex flex-wrap align-items-left fe fe-plus-circle" data-toggle="modal" data-target="#insertBukuModal"></button>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <table class="table table-borderless table-striped" id="tableTransaksiBuku">
                                <thead>
                                    <tr role="row">
                                        <th>Id</th>
                                        <th>Buku</th>
                                        <th>Jumlah</th>
                                        <th>Denda</th>
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
                                        <select id="caraBayar" name="pengembalian_cara_bayar" class="form-control" required>
                                            <option value="Tunai">Tunai</option>
                                            <option value="Kartu">Kartu</option>
                                            <option value="Transfer">Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="pengembalianTotalBayar" class="me-2 mb-0">Jumlah Bayar</label>
                                        <input type="text" id="pengembalianTotalBayar" name="pengembalian_total_bayar" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="pengembalianTotal" class="me-2 mb-0">Total</label>
                                        <input type="text" id="pengembalianTotal" name="pengembalian_total" class="form-control"readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label for="pengembalianTotalKembalian" class="me-2 mb-0">Kembalian</label>
                                        <input type="text" id="pengembalianTotalKembalian" name="pengembalian_total_kembalian" class="form-control" readonly>
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
                            <h4 class="alert-heading">Buku Pengembalian Berhasil disimpan !</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal insert buku -->
    <div class="modal fade" id="insertBukuModal" tabindex="-1" role="dialog" aria-labelledby="insertBukuModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insertBukuModalLabel">Tambah Buku</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="bukuForm">
                        <div class="form-group">
                            <label for="modalBukuNama">Nama Buku</label>
                            <select class="form-control" id="modalBukuNama" required></select>
                        </div>
                        <div class="form-group">
                            <label for="modalBukuJumlah">Jumlah</label>
                            <input type="number" class="form-control" id="modalBukuJumlah" required>
                        </div>
                        <div class="form-group">
                            <label for="modalBukuDenda">Denda</label>
                            <input type="number" class="form-control" id="modalBukuDenda" required>
                        </div>
                        <div class="form-group">
                            <label for="modalBukuDiskonPersen">Diskon (%)</label>
                            <input type="number" class="form-control" id="modalBukuDiskonPersen">
                        </div>
                        <div class="form-group">
                            <label for="modalBukuDiskonRp">Diskon (Rp)</label>
                            <input type="number" class="form-control" id="modalBukuDiskonRp">
                        </div>
                        <input type="hidden" id="modalBukuIndex">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn mb-2 btn-primary" id="saveBukuButton">Save changes</button>
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
            document.getElementById('pengembalianTanggal').value = todayString;

            // Fetch product list and populate dropdown
            fetchProductList();
            fetchPengembalianList();
        });

        let bukuList = [];
        let editingIndex = -1;
        let productData = {};

        document.getElementById('saveBukuButton').addEventListener('click', function() {
            const selectedProductId = document.getElementById('modalBukuNama').value;
            const product = productData[selectedProductId];

            const jumlah = parseInt(document.getElementById('modalBukuJumlah').value);
            const denda = parseFloat(document.getElementById('modalBukuDenda').value);
            const diskonPersen = parseFloat(document.getElementById('modalBukuDiskonPersen').value) || 0;
            const diskonRp = parseFloat(document.getElementById('modalBukuDiskonRp').value) || 0;

            const subtotal = (denda - diskonRp) * jumlah * ((100 - diskonPersen) / 100);

            const buku = {
                pinjam_detail_id          : 0,
                pinjam_detail_buku_id   : product.buku_id,
                pinjam_detail_buku_nama : product.buku_nama,
                pinjam_detail_qty         : jumlah,
                pinjam_detail_denda       : denda,
                pinjam_detail_diskon      : diskonPersen,
                pinjam_detail_diskon_rp   : diskonRp,
                pinjam_diskon_subtotal    : subtotal
            };

            if (editingIndex === -1) {
                bukuList.push(buku);
            } else {
                bukuList[editingIndex] = buku;
                editingIndex = -1;
            }

            document.getElementById('bukuForm').reset();
            $('#insertBukuModal').modal('hide');
            renderBukuTable();
            updateTotal();
        });

        function fetchPengembalianList (){
            var token = localStorage.getItem('token');

            if (!token) {
                window.location.href = '/login'; // Redirect to login if token is not found
                return;
            }

            // Mengambil kategori_id dari URL dengan menggunakan URLSearchParams
            const pengembalianId = "{{ Request::segment(3) }}";

            // Menggunakan pengembalianId dari URL untuk fetch data buku kategori dari API
            fetch(`/api/pengembalian/detail/${pengembalianId}`, {  // Sesuaikan dengan nama parameter yang benar
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal mengambil data buku kategori');
                }
                return response.json();
            })
            .then(data => {
                $('#pengembalianId').val(data.data.pengembalian_id);
                $('#pengembalianNo').val(data.data.pengembalian_no);
                $('#pengembalianTanggal').val(data.data.pengembalian_tanggal);
                $('#pengembalianPelanggan').val(data.data.pengembalian_pelanggan);
                $('#pengembalianTotal').val(data.data.pengembalian_total);
                $('#pengembalianTotalBayar').val(data.data.pengembalian_total_bayar);
                $('#pengembalianTotalKembalian').val(data.data.pengembalian_total_kembalian);

                bukuList = data.data.dataBukuList;
                renderBukuTable();

            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data buku kategori.');
                window.location.href = '/pengembalian'; // Redirect pada error
            });
        }

        function fetchProductList() {
            var token = localStorage.getItem('token');

            fetch('/api/buku/list', {
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
            const productDropdown = document.getElementById('modalBukuNama');
            productDropdown.innerHTML = ''; // Clear existing options
            data.forEach(product => {
                productData[product.buku_id] = product;
                const option = document.createElement('option');
                option.value = product.buku_id;
                option.text = product.buku_nama;
                productDropdown.appendChild(option);
            });

            // Add event listener for product selection
            productDropdown.addEventListener('change', function() {
                const selectedProductId = this.value;
                const product = productData[selectedProductId];
                if (product) {
                    document.getElementById('modalBukuDenda').value = product.buku_denda;
                    document.getElementById('modalBukuJumlah').value = 1; // Default to 1
                    document.getElementById('modalBukuDiskonPersen').value = 0;
                    document.getElementById('modalBukuDiskonRp').value = 0;
                }
            });

            // Trigger change event to set initial values
            productDropdown.dispatchEvent(new Event('change'));
        }

        function renderBukuTable() {
            const tableBody = document.querySelector('#tableTransaksiBuku tbody');
            tableBody.innerHTML = '';

            bukuList.forEach((buku, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${buku.pinjam_detail_buku_id}</td>
                    <td>${buku.pinjam_detail_buku_nama}</td>
                    <td>${buku.pinjam_detail_qty}</td>
                    <td>${buku.pinjam_detail_denda}</td>
                    <td>${buku.pinjam_detail_diskon}</td>
                    <td>${buku.pinjam_detail_diskon_rp}</td>
                    <td>${buku.pinjam_diskon_subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editBuku(${index})"><i class="fe fe-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteBuku(${index})"><i class="fe fe-trash-2"></i></button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function editBuku(index) {
            const buku = bukuList[index];
            const productId = Object.keys(productData).find(id => productData[id].buku_nama === buku.pinjam_detail_buku_nama);

            document.getElementById('modalBukuNama').value = productId;
            document.getElementById('modalBukuJumlah').value = buku.pinjam_detail_qty;
            document.getElementById('modalBukuDenda').value = buku.pinjam_detail_denda;
            document.getElementById('modalBukuDiskonPersen').value = buku.pinjam_detail_diskon;
            document.getElementById('modalBukuDiskonRp').value = buku.pinjam_detail_diskon_rp;
            document.getElementById('modalBukuIndex').value = index;
            editingIndex = index;

            $('#insertBukuModal').modal('show');
        }

        function deleteBuku(index) {
            bukuList.splice(index, 1);
            renderBukuTable();
            updateTotal();
        }

        function updateTotal() {
            const total = bukuList.reduce((acc, buku) => acc + buku.pinjam_diskon_subtotal, 0);
            document.getElementById('pengembalianTotal').value = total.toFixed(2);
            updateKembalian();
        }

        document.getElementById('pengembalianTotalBayar').addEventListener('input', updateKembalian);

        function updateKembalian() {
            const total = parseFloat(document.getElementById('pengembalianTotal').value) || 0;
            const bayar = parseFloat(document.getElementById('pengembalianTotalBayar').value) || 0;
            const kembalian = bayar - total;
            document.getElementById('pengembalianTotalKembalian').value = kembalian.toFixed(2);
        }

        function resetForm() {
            document.getElementById("createForm").reset();
            bukuList = [];
            renderBukuTable();
            updateTotal();
        }

        function submitForm() {
            var form = document.getElementById("createForm");
            var token = localStorage.getItem('token'); // Ambil token dari localStorage
            var formData = {
                pengembalian_id: document.getElementById('pengembalianId').value,
                pengembalian_no: document.getElementById('pengembalianNo').value,
                pengembalian_tanggal: document.getElementById('pengembalianTanggal').value,
                pengembalian_pelanggan: document.getElementById('pengembalianPelanggan').value,
                pengembalian_total: document.getElementById('pengembalianTotal').value,
                pengembalian_total_bayar: document.getElementById('pengembalianTotalBayar').value,
                pengembalian_cara_bayar: document.getElementById('caraBayar').value,
                pengembalian_total_kembalian: document.getElementById('pengembalianTotalKembalian').value,
                buku_list: bukuList
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
                var printUrl = `/api/pengembalian/cetak-faktur-pdf/` + data.data.id;
                prinFakturPengembalian(printUrl);
                setTimeout(function() {
                    $('#successModal').modal('hide');
                    window.location.href = '/pengembalian';
                }, 2000); // Menutup modal setelah 2 detik
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            });
        }

        function prinFakturPengembalian(printUrl) {
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
                    alert('Terjadi kesalahan saat menghapus buku pengembalian.');
                }
            });
        }
    </script>

@endsection
