<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    {{-- <div class="header">
        <h2>Naymoz Bag</h2>
        <p>Desa Penaruban Kecamatan Kaligondang Kabupaten Purbalingga</p>
    </div> --}}

    <h1>Laporan Barang</h1>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Barang</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Min Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barang as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->kategori->nama_kategori }}</td>
                <td>Rp. {{ number_format($item->harga, 0, ',', '.' ) }}</td>
                <td>{{ $item->stok }} {{ $item->satuan->nama_satuan }}</td>
                <td>{{ $item->min_stok }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
