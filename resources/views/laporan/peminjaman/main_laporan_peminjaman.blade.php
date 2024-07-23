@extends('layout.main_layout')

@section('content')
<div class="col-12">
    <div class="col-md-12">
        <div class="card shadow d-flex">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h2 class="mb-2 page-title">Data Laporan Peminjaman</h2>
            </div>
        </div>
        <div class="row my-4">
            <!-- Small table -->
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <!-- Form pencarian -->
                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex">
                                <input type="date" id="tgl_awal" class="form-control mr-2" placeholder="Tanggal Awal">
                                <input type="date" id="tgl_akhir" class="form-control mr-2" placeholder="Tanggal Akhir">
                                <button id="btnCari" class="btn btn-primary">Cari</button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Cetak
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" onclick="peminjamanCetakPDF('xls-list')">Cetak Excel</a>
                                    <a class="dropdown-item" href="#" onclick="peminjamanCetakPDF('pdf-list')">Cetak PDF</a>
                                </div>
                            </div>
                        </div>
                        <!-- table -->
                        <table class="table datatables" id="dataTable-peminjaman">
                            <thead>
                                <tr>
                                    <th>No. Peminjaman</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Est Kembali</th>
                                    <th>Est Total Denda (Rp)</th>
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

<script src="{{ asset('js/jquery.min.js')}}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date();
        var day = ("0" + today.getDate()).slice(-2);
        var month = ("0" + (today.getMonth() + 1)).slice(-2);
        var todayString = today.getFullYear() + "-" + month + "-" + day;
        document.getElementById('tgl_awal').value = todayString;
        document.getElementById('tgl_akhir').value = todayString;
    });

    $(document).ready(function() {
        // Mendapatkan token dari localStorage
        const token = localStorage.getItem('token');

        // Format mata uang
        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }

        // Fungsi untuk memuat data dari API
        function loadData(tgl_awal, tgl_akhir) {
            $.ajax({
                url: '/api/laporan/peminjaman', // URL endpoint POST
                method: 'POST', // Method POST
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                dataType: 'json', // Tipe data yang diharapkan dari server
                data: JSON.stringify({
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
                }),
                success: function(response) {
                    $('#dataTable-peminjaman').DataTable({
                        destroy: true,
                        data: response.data, // Menggunakan data dari response API
                        lengthMenu: [
                            [10, 15, 20, -1], // Pilihan jumlah item per halaman
                            [10, 15, 20, 'All'] // Label untuk setiap pilihan
                        ],
                        pageLength: 10,
                        columns: [
                            { data: 'peminjaman_no' },
                            { data: 'peminjaman_pelanggan' },
                            { data: 'peminjaman_tanggal' },
                            { data: 'peminjaman_tanggal_est_kembali' },
                            { data: 'peminjaman_total_est_denda',
                                render: function(data, type, row) {
                                    return '<div style="text-align: right;">' + formatCurrency(data) + '</div>';
                                }
                            }
                        ]
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        // Mengosongkan data tabel pada awal
        $('#dataTable-peminjaman').DataTable({
            data: [],
            columns: [
                { data: 'peminjaman_no' },
                { data: 'peminjaman_pelanggan' },
                { data: 'peminjaman_tanggal' },
                { data: 'peminjaman_tanggal_est_kembali' },
                { data: 'peminjaman_total_est_denda',
                    render: function(data, type, row) {
                        return '<div style="text-align: right;">' + formatCurrency(data) + '</div>';
                    }
                }
            ]
        });

        // Event handler untuk tombol cari
        $('#btnCari').on('click', function() {
            const tgl_awal = $('#tgl_awal').val();
            const tgl_akhir = $('#tgl_akhir').val();
            loadData(tgl_awal, tgl_akhir);
        });
    });

    function peminjamanCetakPDF(type) {
        const tgl_awal = $('#tgl_awal').val();
        const tgl_akhir = $('#tgl_akhir').val();
        const token = localStorage.getItem('token');

        let api_url = null;
        if (type == 'xls-list') {
            api_url = "/api/laporan/peminjaman/cetak-list-xls";
        } else {
            api_url = "/api/laporan/peminjaman/cetak-list-pdf";
        }

        $.ajax({
            url: api_url,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                tgl_awal: tgl_awal,
                tgl_akhir: tgl_akhir
            }),
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
                alert('Terjadi kesalahan saat mencetak buku peminjaman.');
            }
        });
    }
</script>
@endsection
