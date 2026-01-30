<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .periode {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        
        .ringkasan {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .ringkasan-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        
        .ringkasan-item h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .ringkasan-item small {
            color: #666;
            font-size: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            font-size: 10px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .badge-dark { background-color: #343a40; }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 30px 0 15px 0;
            padding: 10px;
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        @page {
            margin: 1cm;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN BARANG KELUAR</h1>
        <h2>{{ config('app.name', 'Sistem Inventory') }}</h2>
    </div>

    <!-- Periode -->
    <div class="periode">
        <strong>Periode:</strong> {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </div>

    <!-- Ringkasan Total -->
    <div class="ringkasan">
        <div class="ringkasan-item">
            <h3>{{ number_format($totalKeseluruhan->total_transaksi) }}</h3>
            <small>Total Transaksi</small>
        </div>
        <div class="ringkasan-item">
            <h3>{{ number_format($totalKeseluruhan->total_qty) }}</h3>
            <small>Total Barang Keluar</small>
        </div>
        <div class="ringkasan-item">
            <h3>Rp {{ number_format($totalKeseluruhan->total_nilai, 0, ',', '.') }}</h3>
            <small>Total Harga</small>
        </div>
    </div>

    <!-- Detail Transaksi -->
    <div class="section-title">Detail Transaksi Barang Keluar</div>
    @if($laporanBarangKeluar->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">No Transaksi</th>
                <th width="10%">Tanggal</th>
                <th width="8%">Jenis</th>
                <th width="10%">Kode Barang</th>
                <th width="20%">Nama Barang</th>
                <th width="8%">Qty</th>
                <th width="12%">Harga Satuan</th>
                <th width="15%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanBarangKeluar as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->no_transaksi }}</td>
                <td>{{ Carbon\Carbon::parse($item->tgl_keluar)->translatedFormat('j F Y') }}</td>
                <td class="text-center">
                    <span class="badge 
                        @if($item->jenis_keluar == 'distribusi') badge-success
                        @elseif($item->jenis_keluar == 'retur') badge-warning
                        @elseif($item->jenis_keluar == 'rusak') badge-danger
                        @elseif($item->jenis_keluar == 'expired') badge-secondary 
                        @else badge-dark @endif">
                        {{ ucfirst($item->jenis_keluar) }}
                    </span>
                </td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-right">{{ number_format($item->qty) }}</td>
                <td class="text-right">Rp {{ number_format($item->nilai_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        Tidak ada data transaksi barang keluar pada periode ini
    </div>
    @endif

    <!-- Ringkasan per Barang -->
    <div class="section-title page-break">Ringkasan per Barang</div>
    @if($ringkasanBarang->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="35%">Nama Barang</th>
                <th width="15%">Kategori ( / Satuan)</th>
                <th width="10%">Total Qty</th>
                <th width="20%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ringkasanBarang as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->nama_kategori ?? '-' }} / {{ $item->satuan }}</td>
                <td class="text-right">{{ number_format($item->total_qty) }}</td>
                <td class="text-right">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="4" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($ringkasanBarang->sum('total_qty')) }}</td>
                <td class="text-right">Rp {{ number_format($ringkasanBarang->sum('total_nilai'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        Tidak ada data ringkasan barang pada periode ini
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="float: left;">
            <strong>Dicetak pada:</strong> {{ Carbon\Carbon::now()->translatedFormat('j F Y H:i:s') }}
        </div>
        {{-- <div style="float: right;">
            <strong>Halaman:</strong> <span class="pagenum"></span>
        </div>
        <div style="clear: both;"></div> --}}
    </div>

    <!-- Tanda Tangan -->
    {{-- <div style="margin-top: 50px;">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 50%; text-align: center;">
                    <div style="margin-bottom: 80px;">
                        <strong>Mengetahui,</strong><br>
                        <strong>Manager</strong>
                    </div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>(...........................)</strong>
                    </div>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <div style="margin-bottom: 80px;">
                        <strong>Dibuat oleh,</strong><br>
                        <strong>Admin Gudang</strong>
                    </div>
                    <div style="border-top: 1px solid #333; padding-top: 5px;">
                        <strong>(...........................)</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div> --}}
</body>
</html>