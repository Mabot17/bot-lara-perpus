@extends('layout.main_layout')
@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Tambah Transaksi Pengembalian</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            @csrf
                            <a href="{{ route('pengembalian') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="createForm" action="{{ url('/api/pengembalian/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="PeminjamanSelect">No. Peminjaman</label>
                                    <select class="form-control" id="PeminjamanSelect" required></select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianKode">No. Pengembalian</label>
                                    <input type="text" id="pengembalianKode" name="pengembalian_no" placeholder="(Otomatis)" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianPinjamTanggal">Tanggal Pinjam</label>
                                    <input type="date" id="pengembalianPinjamTanggal" name="pengembalian_tanggal_pinjam" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianPinjamTanggalEstKembali">Tanggal Est Kembali</label>
                                    <input type="date" id="pengembalianPinjamTanggalEstKembali" name="pengembalian_tanggal_est_kembali" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label for="pengembalianNama">Pelanggan</label>
                                    <input type="text" id="pengembalianNama" name="pengembalian_pelanggan" class="form-control" value="" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianTanggal">Tanggal Kembali</label>
                                    <input type="date" id="pengembalianTanggal" name="pengembalian_tanggal" class="form-control" onchange="calculateLateDays()">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="pengembalianTelatHari" class="me-2 mb-0">Telat Hari</label>
                                    <input type="text" id="pengembalianTelatHari" name="pengembalian_telat_hari" class="form-control" readonly>
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
                            <button id="insertBukuButton" type="button" class="btn mb-2 btn-primary d-flex flex-wrap align-items-left fe fe-plus-circle" data-toggle="modal" data-target="#insertBukuModal"></button>
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
                                        <input type="text" id="pengembalianTotalBayar" name="pengembalian_total_bayar" class="form-control" required>
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

    <div id="requiredModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="requiredModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requiredModalLabel">Warning!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-12 mb-4">
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">Mohon Isikan Data Buku dan Nominal Pembayaran !</h4>
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
                        <div class="form-group">
                            <label for="modalBukuTelat">Telat Hari</label>
                            <input type="number" class="form-control" id="modalBukuTelat" readonly>
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
            fetchPeminjamanList();
            calculateLateDays();
        });

        function calculateLateDays() {
            const estKembaliInput = document.getElementById('pengembalianPinjamTanggalEstKembali');
            const kembaliInput = document.getElementById('pengembalianTanggal');
            const telatHariInput = document.getElementById('pengembalianTelatHari');

            const estKembaliDate = new Date(estKembaliInput.value);
            const kembaliDate = new Date(kembaliInput.value);

            if (kembaliDate && estKembaliDate) {
                const diffTime = kembaliDate - estKembaliDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                telatHariInput.value = diffDays > 0 ? diffDays : 0;
            }
        }

        let bukuList = [];
        let editingIndex = -1;
        let productData = {};
        let peminjamanData = {};

        function fetchPeminjamanList() {
            var token = localStorage.getItem('token');

            fetch('/api/pengembalian/pinjaman_list', {
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
                populatePeminjamanDropdown(data.data);
            })
            .catch(error => console.error('Error fetching products:', error));
        }

        function populatePeminjamanDropdown(data) {
            const peminjamanDropdown = document.getElementById('PeminjamanSelect');
            peminjamanDropdown.innerHTML = ''; // Clear existing options
            data.forEach(peminjaman => {
                peminjamanData[peminjaman.peminjaman_id] = peminjaman;
                const option = document.createElement('option');
                option.value = peminjaman.peminjaman_id;
                option.text = '(' + peminjaman.peminjaman_no + ') ' + peminjaman.peminjaman_pelanggan;
                peminjamanDropdown.appendChild(option);
            });

            // Add event listener for peminjaman selection
            peminjamanDropdown.addEventListener('change', function() {
                resetTableForm();
                const selectedPinjamanId = this.value;
                const peminjaman = peminjamanData[selectedPinjamanId];
                if (peminjaman) {
                    document.getElementById('pengembalianPinjamTanggal').value = peminjaman.peminjaman_tanggal;
                    document.getElementById('pengembalianPinjamTanggalEstKembali').value = peminjaman.peminjaman_tanggal_est_kembali;
                    document.getElementById('pengembalianNama').value = peminjaman.peminjaman_pelanggan;
                    document.getElementById('pengembalianTanggal').value = peminjaman.peminjaman_tanggal_est_kembali;
                }
                calculateLateDays();
            });

            // Trigger change event to set initial values
            peminjamanDropdown.dispatchEvent(new Event('change'));
        }

        document.getElementById('insertBukuButton').addEventListener('click', function() {
            fetchProductList();
        });

        document.getElementById('saveBukuButton').addEventListener('click', function() {
            const selectedProductId = document.getElementById('modalBukuNama').value;
            const product = productData[selectedProductId];

            const jumlah = parseInt(document.getElementById('modalBukuJumlah').value);
            const telatHariModal = parseFloat(document.getElementById('modalBukuTelat').value) || 0;
            const denda = parseFloat(document.getElementById('modalBukuDenda').value * telatHariModal);
            const diskonPersen = parseFloat(document.getElementById('modalBukuDiskonPersen').value) || 0;
            const diskonRp = parseFloat(document.getElementById('modalBukuDiskonRp').value) || 0;

            const subtotal = (denda - diskonRp) * jumlah * ((100 - diskonPersen) / 100);

            const buku = {
                pkembali_detail_id          : 0,
                pkembali_detail_buku_id     : product.buku_id,
                pkembali_detail_buku_nama   : product.buku_nama,
                pkembali_detail_qty         : jumlah,
                pkembali_detail_denda       : denda,
                pkembali_detail_telat_hari  : telatHariModal,
                pkembali_detail_diskon      : diskonPersen,
                pkembali_detail_diskon_rp   : diskonRp,
                pkembali_diskon_subtotal    : subtotal
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

        function fetchProductList() {
            var token = localStorage.getItem('token');

            fetch('/api/pengembalian/buku_by_pinjaman_list', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    peminjaman_id: document.getElementById('PeminjamanSelect').value,
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
                    document.getElementById('modalBukuTelat').value = document.getElementById('pengembalianTelatHari').value;
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
                    <td>${buku.pkembali_detail_buku_id}</td>
                    <td>${buku.pkembali_detail_buku_nama}</td>
                    <td>${buku.pkembali_detail_qty}</td>
                    <td>${buku.pkembali_detail_denda}</td>
                    <td>${buku.pkembali_detail_diskon}</td>
                    <td>${buku.pkembali_detail_diskon_rp}</td>
                    <td>${buku.pkembali_diskon_subtotal.toFixed(2)}</td>
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
            const productId = Object.keys(productData).find(id => productData[id].buku_nama === buku.pkembali_detail_buku_nama);

            document.getElementById('modalBukuNama').value = productId;
            document.getElementById('modalBukuJumlah').value = buku.pkembali_detail_qty;
            document.getElementById('modalBukuDenda').value = buku.pkembali_detail_denda;
            document.getElementById('modalBukuDiskonPersen').value = buku.pkembali_detail_diskon;
            document.getElementById('modalBukuDiskonRp').value = buku.pkembali_detail_diskon_rp;
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
            const total = bukuList.reduce((acc, buku) => acc + buku.pkembali_diskon_subtotal, 0);
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
            resetTableForm();
        }

        function resetTableForm(){
            bukuList = [];
            renderBukuTable();
            updateTotal();
        }

        function submitForm() {
            const totalBayar = parseFloat(document.getElementById('pengembalianTotalBayar').value) || 0;

            if (bukuList.length < 1 || totalBayar < 0) {
                $('#requiredModal').modal('show');
                return;
            }
            var form = document.getElementById("createForm");
            var token = localStorage.getItem('token'); // Ambil token dari localStorage
            var formData = {
                pengembalian_no                  : document.getElementById('pengembalianKode').value,
                pengembalian_pinjam_id           : document.getElementById('PeminjamanSelect').value,
                pengembalian_tanggal_pinjam      : document.getElementById('pengembalianPinjamTanggal').value,
                pengembalian_tanggal_est_kembali : document.getElementById('pengembalianPinjamTanggalEstKembali').value,
                pengembalian_tanggal             : document.getElementById('pengembalianTanggal').value,
                pengembalian_telat_hari          : document.getElementById('pengembalianTelatHari').value,
                pengembalian_total_denda         : document.getElementById('pengembalianTotal').value,
                pengembalian_total_bayar         : document.getElementById('pengembalianTotalBayar').value,
                pengembalian_cara_bayar          : document.getElementById('caraBayar').value,
                pengembalian_total_kembalian     : document.getElementById('pengembalianTotalKembalian').value,
                buku_list                        : bukuList
            };

            fetch(form.action, {
                method: 'POST',
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
