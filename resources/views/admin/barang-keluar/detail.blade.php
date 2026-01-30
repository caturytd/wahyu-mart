@extends('layouts.app')
@section('title', 'Detail Barang Keluar')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <span style="font-size: 0.9rem">BARANG KELUAR</span> <br>
        <strong>{{ $barangKeluar->no_transaksi }}</strong>
    </h5>
    <div>
        <a href="#" class="btn btn-primary" onclick="printCard()">
            <i class="fa fa-print"></i> Print
        </a>
        @if($barangKeluar->barcode_path)
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#barcodeModal">
            <i class="fa fa-barcode"></i> Barcode
        </button>
        @endif
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('barang-keluar.edit', $barangKeluar->id) }}" class="btn btn-secondary">
            <i class="fa fa-edit"></i> Edit
        </a>
        <button type="submit" class="btn btn-danger delete-btn" data-id="{{ $barangKeluar->id }}">
            <i class="fas fa-trash-alt"></i> Hapus
        </button>
        <form id="delete-form-{{ $barangKeluar->id }}" action="{{ route('barang-keluar.destroy', $barangKeluar->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body" id="printableArea">
        @include('includes.alert')
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="fw-bold">{{ config('app.name') }}</h6>
                <p class="mb-1">{{ $pengaturanToko->desa ?? 'Desa' }} <br>{{ $pengaturanToko->kecamatan ?? 'Kecamatan' }}, {{ $pengaturanToko->kabupaten ?? 'Purbalingga' }}</p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="fw-bold">Informasi Keluar</h6>
                <p class="mb-1"><strong>Jenis Keluar:</strong> 
                    @php
                        $jenisClass = match($barangKeluar->jenis_keluar) {
                            'distribusi' => 'success',
                            'retur' => 'info',
                            'rusak' => 'danger',
                            'expired' => 'warning',
                            'hilang' => 'dark',
                            default => 'secondary'
                        };

                        $jenisLabel = match($barangKeluar->jenis_keluar) {
                            'distribusi' => 'Distribusi',
                            'retur' => 'Retur',
                            'rusak' => 'Rusak',
                            'expired' => 'Expired',
                            'hilang' => 'Hilang',
                            default => ucfirst($barangKeluar->jenis_keluar)
                        };
                    @endphp
                    <span class="badge bg-{{ $jenisClass }}">{{ $jenisLabel }}</span>
                </p>
                <p class="mb-0"><strong>Total Qty:</strong> {{ number_format($barangKeluar->total_qty) }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <h5 class="fw-bold">{{ $barangKeluar->no_transaksi }}</h5>
                <p class="mb-1"><strong>Tanggal Keluar:</strong> {{ \Carbon\Carbon::parse($barangKeluar->tgl_keluar)->translatedFormat('j F Y') }}</p>
                <p class="mb-1"><strong>Total Harga:</strong> <span class="fw-bold">Rp {{ number_format($barangKeluar->total_nilai, 0, ',', '.') }}</span></p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-1"><strong>Dibuat:</strong> {{ $barangKeluar->created_at->translatedFormat('j F Y H:i') }}</p>
                @if($barangKeluar->updated_at != $barangKeluar->created_at)
                <p class="mb-1"><strong>Diperbarui:</strong> {{ $barangKeluar->updated_at->translatedFormat('j F Y H:i') }}</p>
                @endif
            </div>
        </div>

        @if($barangKeluar->keterangan)
        <div class="alert alert-info">
            <strong>Keterangan:</strong> {{ $barangKeluar->keterangan }}
        </div>
        @endif

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Barang</th>
                    <th width="12%">Jumlah</th>
                    <th width="15%">Rata Rata Harga</th>
                    <th width="15%">Total Harga</th>
                    <th width="28%">Detail Batch Barang</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($barangKeluar->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->barang->nama_barang }}</strong><br>
                        <small class="text-muted">{{ $detail->barang->kode_barang }}</small><br>
                        <small class="text-info">{{ $detail->barang->satuan ?? 'pcs' }}</small>
                    </td>
                    <td class="fw-bold">{{ number_format($detail->qty) }}</td>
                    <td>Rp {{ number_format($detail->nilai_satuan, 0, ',', '.') }}</td>
                    <td class="fw-bold">Rp {{ number_format($detail->total_nilai, 0, ',', '.') }}</td>
                    <td>
                        @if($detail->batchDetails && $detail->batchDetails->count() > 0)
                            @foreach($detail->batchDetails as $batchDetail)
                            <div class="mb-2">
                                <span class="badge bg-secondary mb-1">{{ $batchDetail->barangBatch->batch_code }}</span>
                                <div class="small">
                                    <strong>Qty:</strong> {{ number_format($batchDetail->qty_digunakan) }}<br>
                                    <strong>Harga:</strong> Rp {{ number_format($batchDetail->nilai_satuan, 0, ',', '.') }}<br>
                                    <strong>Total:</strong> Rp {{ number_format($batchDetail->total_nilai, 0, ',', '.') }}
                                    @if($batchDetail->barangBatch->expired_date)
                                        <br><strong>Exp:</strong> 
                                        @php
                                            $expDate = \Carbon\Carbon::parse($batchDetail->barangBatch->expired_date);
                                            $isExpired = $expDate->isPast();
                                        @endphp
                                        <small class="fw-bold text-{{ $isExpired ? 'danger' : 'success' }}">
                                            {{ $expDate->translatedFormat('j M Y') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            @if(!$loop->last)<hr class="my-1">@endif
                            @endforeach
                        @else
                            <span class="text-muted">No batch data</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL</td>
                    <td class="fw-bold">Rp {{ number_format($barangKeluar->total_nilai, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

     
    </div>
</div>

@if($barangKeluar->barcode_path)
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Barcode Transaksi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <strong>{{ $barangKeluar->no_transaksi }}</strong>

                <img
                    src="{{ Storage::url($barangKeluar->barcode_path) }}"
                    alt="Barcode"
                    class="img-fluid my-2"
                    onerror="this.onerror=null;this.replaceWith('❌ Barcode gagal dimuat')"
                >

                <small class="text-muted d-block">
                    {{ \Carbon\Carbon::parse($barangKeluar->tgl_keluar)->translatedFormat('j F Y') }}
                </small>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary btn-sm" onclick="printBarcode()">Print</button>
            </div>
        </div>
    </div>
</div>
@endif



<script>
function printCard() {
    var printContents = document.getElementById('printableArea').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}

</script>

<style>
@media print {
    .btn, .card-header, .breadcrumb {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background: transparent !important;
    }
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.table th {
    background-color: #f8f9fa !important;
    font-weight: 600;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.75em;
}

.small {
    font-size: 0.875em;
    line-height: 1.4;
}

hr.my-1 {
    margin: 0.25rem 0;
    opacity: 0.3;
}
</style>

<script>
function printBarcode() {
    const printWindow = window.open('', '_blank', 'width=400,height=600');

    printWindow.document.write(`
        <html>
            <head>
                <title>Print Barcode</title>
                <style>
                    body {
                        margin: 0;
                        padding: 20px;
                        text-align: center;
                        font-family: Arial, sans-serif;
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                    }
                </style>
            </head>
            <body>
             
                <img 
                    src="{{ Storage::url($barangKeluar->barcode_path) }}" 
                    alt="Barcode"
                >


                <script>
                    window.onload = function () {
                        window.print();
                        window.onafterprint = window.close;
                    }
                <\/script>
            </body>
        </html>
    `);

    printWindow.document.close();
}
</script>


@endsection