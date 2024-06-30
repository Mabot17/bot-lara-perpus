@extends('layout.main_layout')

@section('content')
@include('pages.buku_kategori.form_detail_buku_kategori')
<div class="col-12">
    <div class="col-md-12">
        <div class="card shadow d-flex">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h2 class="mb-2 page-title">Data Buku Kategori</h2>
                <div class="d-flex align-items-center">
                    <!-- Tombol Export -->
                    <div class="dropdown" style="margin-right: 10px">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Cetak
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="#" onclick="kategoriCetakPDF('xls-list')">Cetak Excel</a>
                            <a class="dropdown-item" href="#" onclick="kategoriCetakPDF('pdf-list')">Cetak PDF</a>
                        </div>
                    </div>

                    <!-- Tombol Tambah Data -->
                    <a class="btn btn-primary ms-2" href="{{ route('bukuKategori.tambah') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                            <path d="M8 0a1.5 1.5 0 0 1 1.5 1.5V6h4.5a1.5 1.5 0 1 1 0 3H9.5v4.5a1.5 1.5 0 1 1-3 0V9.5H1.5a1.5 1.5 0 1 1 0-3h4.5V1.5A1.5 1.5 0 0 1 8 0z"/>
                        </svg>
                        Tambah Data
                    </a>
                </div>
            </div>
        </div>
        <div class="row my-4">
            <!-- Small table -->
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <!-- table -->
                        <table class="table datatables" id="dataTable-buku_kategori">
                            <thead>
                                <tr>
                                    <th>Kategori ID</th>
                                    <th>Kategori Kode</th>
                                    <th>Kategori Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- simple table -->
            </div> <!-- end section -->
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="hapusModal" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hapusModalLabel">Hapus Buku Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-body-hapus">
                Apakah Anda yakin ingin menghapus buku kategori ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteKategori">Hapus</button>
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

        // Menggunakan jQuery untuk memuat data dari API
        $.ajax({
            url: '/api/bukuKategori/list', // URL endpoint POST
            method: 'POST', // Method POST
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            dataType: 'json', // Tipe data yang diharapkan dari server
            data: JSON.stringify({
                start: 0,
                limit: 0,
                filter: '' // Sesuaikan dengan parameter filter yang dibutuhkan
            }),
            success: function(response) {
                // Mengisi data ke dalam tabel DataTables
                $('#dataTable-buku_kategori').DataTable({
                    data: response.data, // Menggunakan data dari response API
                    lengthMenu: [
                        [5, 10, 15, 20, -1], // Pilihan jumlah item per halaman
                        [5, 10, 15, 20, 'All'] // Label untuk setiap pilihan
                    ],
                    pageLength: 5,
                    columns: [
                        { data: 'kategori_id' },
                        { data: 'kategori_kode' },
                        { data: 'kategori_nama' },
                        {
                            // Kolom aksi
                            render: function(data, type, full, meta) {
                                var editUrl = "{{ url('/bukuKategori/ubah') }}/" + full.kategori_id;
                                return `
                                    <a href="${editUrl}" class="btn btn-sm btn-info me-2"><i class="fe fe-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="konfirmasiHapusBukuKategori('${token}', '${full.kategori_id}', '${full.kategori_kode}', '${full.kategori_nama}')"><i class="fe fe-trash-2"></i></button>
                                `;
                            }
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    });

    function konfirmasiHapusBukuKategori(token, kategori_id, kategori_kode, kategori_nama) {
        // Memasukkan data buku kategori ke dalam modal body
        var modalBody = `
            <b>Anda yakin ingin menghapus buku kategori dengan:</b>
            <table class="ml-3">
                <tr>
                    <td><b>Kode</b></td>
                    <td><b>: ${kategori_kode}</b></td>
                </tr>
                <tr>
                    <td><b>Nama</b></td>
                    <td><b>: ${kategori_nama}</b></td>
                </tr>
            </table>
            <br>
            <p><b>Data yang dihapus tidak bisa dikembalikan!!</b></p>
        `;

        // Memasukkan isi modal body yang telah dibuat ke dalam elemen dengan id 'modal-body-hapus'
        $('#modal-body-hapus').html(modalBody);
        $('#hapusModal').modal('show');
        // Mengatur action untuk tombol hapus
        $('#confirmDeleteKategori').on('click', function() {
            $.ajax({
                url: `/api/bukuKategori/delete/${kategori_id}`, // Sesuaikan dengan endpoint delete
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    $('#hapusModal').modal('hide');
                    window.location.reload(); // Melakukan reload halaman
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting data:', error);
                    alert('Terjadi kesalahan saat menghapus buku kategori.');
                }
            });
        });
    }

    function kategoriCetakPDF(type) {
        const token = localStorage.getItem('token');

        let api_url = null;
        if (type == 'xls-list') {
            api_url = "/api/bukuKategori/cetak-list-xls";
        }else{
            api_url = "/api/bukuKategori/cetak-list-pdf";
        }

        $.ajax({
            url: api_url,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.url) {
                    // Buka URL dalam tab baru
                    window.open(response.url, '_blank');
                } else {
                    alert("URL tidak ditemukan dalam respons.");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mencetak buku kategori.');
            }
        });
    }
</script>
@endsection
