<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Masuk</title>
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
        .badge-info { background-color: #17a2b8; }
        
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
        
        .batch-code {
            background-color: #404244;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN BARANG MASUK</h1>
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
            <small>Total Barang Masuk</small>
        </div>
        <div class="ringkasan-item">
            <h3>Rp {{ number_format($totalKeseluruhan->total_harga, 0, ',', '.') }}</h3>
            <small>Total Harga</small>
        </div>
    </div>

    <!-- Detail Transaksi -->
    <div class="section-title">Detail Transaksi Barang Masuk</div>
    @if($laporanBarangMasuk->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">No Transaksi</th>
                <th width="8%">Tanggal</th>
                <th width="12%">Pemasok</th>
                <th width="8%">Kode</th>
                <th width="10%">Nama Barang</th>
                <th width="8%">Batch</th>
                <th width="6%">Qty</th>
                <th width="10%">Harga Satuan</th>
                <th width="22%">Total Harga</th>
                {{-- <th width="5%">Status</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($laporanBarangMasuk as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->no_transaksi }}</td>
                <td>{{ Carbon\Carbon::parse($item->tgl_masuk)->translatedFormat('j M Y') }}</td>
                <td>{{ $item->nama_pemasok }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-center">
                    <span class="batch-code">{{ $item->batch_code }}</span>
                </td>
                <td class="text-right">{{ number_format($item->qty) }}</td>
                <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                {{-- <td class="text-center">
                    @php
                        $statusClass = match($item->status) {
                            'received' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'info'
                        };

                        $statusLabel = match($item->status) {
                            'received' => 'Diterima',
                            'pending' => 'Pending',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($item->status)
                        };
                    @endphp
                    <span class="badge badge-{{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </td> --}}
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        Tidak ada data transaksi barang masuk pada periode ini
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
                <th width="20%">Nama Barang</th>
                <th width="15%">Kategori / Satuan</th>
                <th width="10%">Total Qty</th>
                <th width="15%">Total Harga</th>
                <th width="20%">Rata-rata Harga</th>
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
                <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->rata_rata_harga ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="4" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($ringkasanBarang->sum('total_qty')) }}</td>
                <td class="text-right">Rp {{ number_format($ringkasanBarang->sum('total_harga'), 0, ',', '.') }}</td>
                <td class="text-right">-</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        Tidak ada data ringkasan barang pada periode ini
    </div>
    @endif

    <!-- Detail Expired Date (Jika Ada) -->
    @php
        $expiredItems = $laporanBarangMasuk->filter(function($item) {
            return !empty($item->expired_date);
        });
    @endphp
    
    @if($expiredItems->count() > 0)
    <div class="section-title page-break">Detail Barang dengan Tanggal Kadaluarsa</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No Transaksi</th>
                <th width="12%">Tanggal Masuk</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Barang</th>
                <th width="10%">Batch</th>
                <th width="8%">Qty</th>
                <th width="10%">Tgl Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expiredItems as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->no_transaksi }}</td>
                <td>{{ Carbon\Carbon::parse($item->tgl_masuk)->translatedFormat('j M Y') }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-center">
                    <span class="batch-code">{{ $item->batch_code }}</span>
                </td>
                <td class="text-right">{{ number_format($item->qty) }}</td>
                <td class="text-center">{{ Carbon\Carbon::parse($item->expired_date)->translatedFormat('j M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="float: left;">
            <strong>Dicetak pada:</strong> {{ Carbon\Carbon::now()->translatedFormat('j F Y H:i:s') }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Tanda Tangan (Optional) -->
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