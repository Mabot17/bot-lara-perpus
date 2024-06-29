<!DOCTYPE html>
<html>
<head>
    <title>Data Produk Kategori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
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
        .nota-info, .total-info, .signature {
            margin-top: 20px;
        }
        .nota-info th {
            background: #f2f2f2;
        }
        .nota-info td, .total-info td {
            padding: 5px;
        }
        .nota-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px; /* Mengubah ukuran font tabel */
        }
        .nota-info th, .nota-info td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .nota-info .right-align {
            text-align: right;
        }
        .barcode {
            padding: 10px;
            text-align: center;
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
            margin-right: 40px;
        }
    </style>
</head>
<body>
    <div class="kop">
        <table width="100%" style="border: 0px!important">
            <tr>
                <td style="text-align: center"><img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('uploads/logo-pujangga-bot.png'))) }}" alt="Logo"></td>
                <td style="text-align: center">
                    <h1>PUJANGGA-BOT</h1>
                    <h2>Toko Produk Elektronik Terlengkap</h2>
                    <p>Jl. Semolowaru No.45, Surabaya, Jawa Timur 60118</p>
                </td>
            </tr>
        </table>
    </div>
    <hr>
    <h1>Data Produk Kategori</h1>
    <table class="nota-info">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Kategori</th>
                <th>Nama Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data_produk_kategori as $produk_kategori)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $produk_kategori->kategori_kode }}</td>
                    <td>{{ $produk_kategori->kategori_nama }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <div class="signature">
        <p style="text-align: right" class="date">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('uploads/qr-code-pujangga-bot.png'))) }}" class="qr-code" alt="QR Code"><br>
            <i>Dicetak Pada, {{ date('d-m-Y H:i:s') }}</i>
        </p>
    </div>
    <div class="page-number"></div>
</body>
</html>
