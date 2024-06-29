@extends('layout.main_layout')

@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Ubah Produk Kategori</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            <a href="{{ route('produkKategori') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="kategori_id" id="kategoriId" value="{{ Request::segment(3) }}">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="kategoriKode">Kategori Kode</label>
                                    <input type="text" id="kategoriKode" name="kategori_kode" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="kategoriNama">Kategori Nama</label>
                                    <input type="text" id="kategoriNama" name="kategori_nama" class="form-control">
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
                            <h4 class="alert-heading">Produk Kategori Berhasil diubah!</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mendapatkan token dari localStorage
            const token = localStorage.getItem('token');

            if (!token) {
                window.location.href = '/login'; // Redirect to login if token is not found
                return;
            }

            // Mengambil kategori_id dari URL dengan menggunakan URLSearchParams
            const kategoriId = "{{ Request::segment(3) }}";

            // Menggunakan kategoriId dari URL untuk fetch data produk kategori dari API
            fetch(`/api/produkKategori/detail/${kategoriId}`, {  // Sesuaikan dengan nama parameter yang benar
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
                $('#kategoriKode').val(data.data.kategori_kode);
                $('#kategoriNama').val(data.data.kategori_nama);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data produk kategori.');
                window.location.href = '/produkKategori'; // Redirect pada error
            });


        });
        // Fungsi untuk reset form
        function resetForm() {
            document.getElementById("editForm").reset();
        }

        // Fungsi untuk submit form
        function submitForm() {
            const token = localStorage.getItem('token');
            var kategoriId = document.getElementById("kategoriId").value;
            var kategoriKode = document.getElementById("kategoriKode").value;
            var kategoriNama = document.getElementById("kategoriNama").value;

            fetch("{{ url('/api/produkKategori/update') }}", {
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json' // Tentukan Content-Type sebagai JSON
                },
                body: JSON.stringify({
                    kategori_id: kategoriId,
                    kategori_kode: kategoriKode,
                    kategori_nama: kategoriNama
                })
            })
            .then(response => {
                if (response.ok) {
                    $('#successModal').modal('show');
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                        window.location.href = '/produkKategori';
                    }, 2000); // Menutup modal setelah 2 detik
                } else {
                    throw new Error('Gagal menyimpan data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            });
        }

    </script>

@endsection
