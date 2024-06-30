@extends('layout.main_layout')
@section('content')
    <div class="container-fluid">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between breadcrumb-content">
                        <h5>Tambah Buku Kategori</h5>
                        <div class="d-flex flex-wrap align-items-center">
                            @csrf
                            <a href="{{ route('bukuKategori') }}" class="btn btn-warning"><i class="fe fe-skip-back fe-16 mr-1"></i>Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form id="createForm" action="{{ url('/api/bukuKategori/create') }}" method="POST">
                        @csrf
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
                            <h4 class="alert-heading">Buku Kategori Berhasil disimpan !</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById("createForm").reset();
        }

        function submitForm() {
            var form = document.getElementById("createForm");
            var formData = new FormData(form);
            var token = localStorage.getItem('token'); // Ambil token dari localStorage

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
                        window.location.href = '/bukuKategori';
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
