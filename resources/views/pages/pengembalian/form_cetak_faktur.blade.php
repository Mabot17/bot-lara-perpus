<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pengembalian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .kop {
            text-align: center;
        }
        .kop h1, .kop h2, .kop p {
            margin: 0;
        }
        .kop img {
            width: 100px;
        }
        .header-info, .total-info, .signature {
            margin-top: 20px;
        }
        .header-info td, .total-info td {
            padding: 5px;
        }
        .header-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .header-info th, .header-info td {
            border: 0px;
            padding: 8px;
            text-align: left;
        }
        .header-info th {
            background-color: #f2f2f2;
        }
        .nota-info, .total-info, .signature {
            margin-top: 20px;
        }
        .nota-info td, .total-info td {
            padding: 5px;
        }
        .nota-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .nota-info th, .nota-info td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .nota-info th {
            background-color: #f2f2f2;
        }
        .total-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 20px;
        }
        .total-info td {
            padding: 8px;
        }
        .total-info .right-align {
            text-align: right;
        }
        .signature {
            text-align: center;
            margin-top: 40px;
        }
        .signature p {
            margin: 5px 0;
        }
        .signature .date {
            margin-bottom: 20px;
            font-size: 12px;
            font-style: italic;
        }
        .qr-code {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto 10px;
        }
    </style>
</head>
<body>
    <div class="kop">
        <table width="100%">
            <tr>
                <td style="text-align: center">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('uploads/logo-pujangga-bot.png'))) }}" alt="Logo">
                </td>
                <td style="text-align: center">
                    <h1>PUJANGGA-BOT</h1>
                    <h2>Perpustakaan Buku Terlengkap</h2>
                    <p>Jl. Semolowaru No.45, Surabaya, Jawa Timur 60118</p>
                </td>
            </tr>
        </table>
    </div>
    <hr>
    <h4 style="text-align: center">Nota Pengembalian Buku</h4>
    <table width="100%">
        <tr>
            <td style="vertical-align: top;">
                <table class="header-info">
                    <tr>
                        <td width="60px"><strong>No Nota</strong></td>
                        <td>: {{ $result->pengembalian_no }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pelanggan</strong></td>
                        <td>: {{ $result->peminjaman_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Peminjaman</strong></td>
                        <td>: {{ date('d-m-Y', strtotime($result->pengembalian_tanggal_pinjam)) }}</td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top;">
                <table class="header-info">
                    <tr>
                        <td><strong>Tanggal Est Kembali</strong></td>
                        <td>: {{ date('d-m-Y', strtotime($result->pengembalian_tanggal_est_kembali)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Pengembalian</strong></td>
                        <td>: {{ date('d-m-Y', strtotime($result->pengembalian_tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Telat Hari</strong></td>
                        <td>: {{ $result->pengembalian_telat_hari }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <hr>
    <table class="nota-info">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Buku</th>
                <th>Denda</th>
                <th>Diskon (%)</th>
                <th>Diskon (Rp)</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result->dataBukuList as $index => $buku)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $buku->pkembali_detail_buku_nama }}</td>
                    <td>{{ number_format($buku->pkembali_detail_denda, 0, ',', '.') }}</td>
                    <td>{{ $buku->pkembali_detail_diskon }}</td>
                    <td>{{ number_format($buku->pkembali_detail_diskon_rp, 0, ',', '.') }}</td>
                    <td>{{ $buku->pkembali_detail_qty }}</td>
                    <td>{{ number_format($buku->pkembali_diskon_subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="total-info">
        <tr>
            <td><strong>Total (Rp):</strong></td>
            <td class="right-align">{{ number_format($result->pengembalian_total_denda, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Bayar (Rp):</strong></td>
            <td class="right-align">{{ number_format($result->pengembalian_total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Kembali (Rp):</strong></td>
            <td class="right-align">{{ number_format($result->pengembalian_total_kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>
    <hr>
    <div class="signature">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('uploads/qr-code-pujangga-bot.png'))) }}" class="qr-code" alt="QR Code">
        <p class="date">Surabaya, {{ date('d-m-Y H:i:s') }}</p>
        <p>Terima Kasih!</p>
    </div>
</body>
</html>
