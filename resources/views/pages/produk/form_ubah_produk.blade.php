@extends('layout.main_layout')
@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Ubah Produk</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            @csrf
                            <a href="{{ route('produk') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="updateForm" action="{{ url('/api/produk/update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkSku">Produk SKU</label>
                                    <input type="text" id="produkId" name="produk_id" class="form-control" value="" hidden>
                                    <input type="text" id="produkSku" name="produk_sku" class="form-control" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkNama">Produk Nama</label>
                                    <input type="text" id="produkNama" name="produk_nama" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkSatuan">Produk Satuan</label>
                                    <input type="text" id="produkSatuan" name="produk_satuan" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkStok">Produk Stok</label>
                                    <input type="number" id="produkStok" name="produk_stok" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <!-- Combobox Kategori Produk -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkKategori">Produk Kategori</label>
                                    <select id="produkKategori" name="produk_kategori_id" class="form-control" required>
                                        <!-- Options akan diisi melalui JavaScript -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkAktif">Produk Aktif</label>
                                    <select id="produkAktif" name="produk_aktif" class="form-control" required>
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Input File dan Preview Gambar -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkFoto">Produk Foto</label>
                                    <input type="file" id="produkFoto" name="produk_foto_path" class="form-control" accept="image/*" onchange="previewImage(event)">
                                    <div id="fotoPreview" style="margin-top: 10px;">
                                        <img class="produk-pic" id="outputImage" width="200px" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="produkHarga">Produk Harga</label>
                                    <input type="number" id="produkHarga" name="produk_harga" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <div class="d-flex flex-wrap align-items-center">
                            <button type="button" class="btn btn-danger mr-3" onclick="resetForm()"><i class="fe fe-x fe-16 mr-1"></i>Reset</button>
                            <button type="button" class="btn btn-secondary" onclick="submitForm()"><i class="fe fe-save fe-16 mr-1"></i>Simpan</button>
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
                            <h4 class="alert-heading">Produk Berhasil diubah!</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById("updateForm").reset();
            document.getElementById("outputImage").src = '';
        }

        function submitForm() {
            var form = document.getElementById("updateForm");
            var formData = new FormData(form);
            var token = localStorage.getItem('token');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    $('#successModal').modal('show');
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                        window.location.href = '/produk';
                    }, 2000);
                } else {
                    throw new Error('Gagal menyimpan data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            });
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('outputImage');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var token = localStorage.getItem('token');
            // Load combobox data
            fetch('/api/produkKategori/list', {
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
                var kategoriSelect = document.getElementById('produkKategori');
                data.data.forEach(kategori => {
                    var option = document.createElement('option');
                    option.value = kategori.kategori_id;
                    option.text = kategori.kategori_nama;
                    kategoriSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching categories:', error));

            // Load produk data
            // Mengambil produk_id dari URL dengan menggunakan URLSearchParams
            const produkId = "{{ Request::segment(3) }}";

            // Menggunakan produkId dari URL untuk fetch data produk kategori dari API
            fetch(`/api/produk/detail/${produkId}`, {  // Sesuaikan dengan nama parameter yang benar
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
                // Mengisi nilai form dengan data produk kategori
                $('#produkId').val(data.data.produk_id);
                $('#produkSku').val(data.data.produk_sku);
                $('#produkNama').val(data.data.produk_nama);
                $('#produkSatuan').val(data.data.produk_satuan);
                $('#produkStok').val(data.data.produk_stok);
                $('#produkKategori').val(data.data.produk_kategori_id);
                $('#produkAktif').val(data.data.produk_aktif);
                $('#produkHarga').val(data.data.produk_harga);
                if (data.data.produk_foto_path != null) {
                    document.querySelector('.produk-pic').src = '{{ asset("/") }}' + data.data.produk_foto_path;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data produk.');
                window.location.href = '/produk'; // Redirect pada error
            });
        });
        previewImage();

    </script>
@endsection
