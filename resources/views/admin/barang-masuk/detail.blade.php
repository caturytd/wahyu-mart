@extends('layouts.app')
@section('title', 'Detail Barang Masuk')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">
        <span style="font-size: 0.9rem">BARANG MASUK</span> <br>
        <strong>{{ $barangMasuk->no_transaksi }}</strong>
    </h5>
    <div>
        <a href="#" class="btn btn-primary" onclick="printCard()">
            <i class="fa fa-print"></i> Print
        </a>
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('barang-masuk.edit', $barangMasuk->id) }}" class="btn btn-secondary">
            <i class="fa fa-edit"></i> Edit
        </a>
      <button type="submit" class="btn btn-danger delete-btn" data-id="{{ $barangMasuk->id }}"><i class="fas fa-trash-alt"></i>
        Hapus
        </button>
        <form  id="delete-form-{{ $barangMasuk->id }}" action="{{ route('barang-masuk.destroy', $barangMasuk->id) }}" method="POST" class="d-inline">
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
                <p class="mb-1">Jl. Raya Ciberem, Kebenaran, Ciberem, <br>Kec. Sumbang, <br>Kabupaten Banyumas, Jawa Tengah 53183</p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="fw-bold">Pemasok</h6>
                <p class="mb-1">{{ $barangMasuk->pemasok->nama_pemasok }}</p>
                <p class="mb-1">{{ $barangMasuk->pemasok->alamat }}</p>
                <p class="mb-0">{{ $barangMasuk->pemasok->kontak }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <h5 class="fw-bold">{{ $barangMasuk->no_transaksi }}</h5>
                <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($barangMasuk->tgl_masuk)->translatedFormat('j F Y') }}</p>
                {{-- <p class="mb-1"><strong>Status:</strong> 
                     @php
                        $statusClass = match($barangMasuk->status) {
                        'received' => 'success',
                        'pending' => 'warning',
                        default => 'danger'
                        };

                        $statusLabel = match($barangMasuk->status) {
                        'received' => 'Diterima',
                        'pending' => 'Pending',
                        'canceled' => 'Dibatalkan',
                        default => ucfirst($barangMasuk->status)
                                };
                            @endphp

                            <span class="badge bg-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                </p> --}}
            </div>
            <div class="col-md-6 text-end">
                @if($barangMasuk->no_surat_jalan)
                <p class="mb-1"><strong>No. Surat Jalan:</strong> {{ $barangMasuk->no_surat_jalan }}</p>
                @endif
                @if($barangMasuk->received_by)
                <p class="mb-1"><strong>Diterima oleh:</strong> {{ $barangMasuk->received_by }}</p>
                @endif
                <p class="mb-1"><strong>Total Qty:</strong> {{ $barangMasuk->total_qty }}</p>
            </div>
        </div>

        @if($barangMasuk->keterangan)
        <div class="alert alert-info">
            <strong>Keterangan:</strong> {{ $barangMasuk->keterangan }}
        </div>
        @endif

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Barang</th>
                    <th width="12%">Kode Batch</th>
                    <th width="10%">Jumlah</th>
                    <th width="12%">Harga Satuan</th>
                    <th width="12%">Total Harga</th>
                    <th width="12%">Tanggal Kadaluarsa</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($barangMasuk->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->barang->nama_barang }}</strong><br>
                        <small class="text-muted">{{ $detail->barang->kode_barang }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $detail->batch_code }}</span>
                    </td>
                    <td>{{ number_format($detail->qty) }} {{ $detail->barang->satuan ?? 'pcs' }}</td>
                    <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                    <td>
                        @if($detail->expired_date)
                              @php
                                    $expiredDate = \Carbon\Carbon::parse($detail->expired_date)->startOfDay();
                                    $today = now()->startOfDay();
                                    $diffInDays = $today->diffInDays($expiredDate, false);
                                @endphp
                            <span class="badge bg-{{ $diffInDays < 0 ? 'danger' : ($diffInDays <= 30 ? 'warning' : 'success') }}">
                                {{ $expiredDate->translatedFormat('j M Y') }}
                            </span>
                            @if($diffInDays < 0)
                                <br><small class="text-danger fw-bold">Expired</small>
                            @elseif($diffInDays <= 30)
                                <br><small class="text-warning fw-bold">{{ $diffInDays }} hari lagi</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">TOTAL</td>
                    <td class="fw-bold">Rp {{ number_format($barangMasuk->total_harga, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Informasi Batch Detail -->
        {{-- <div class="mt-4">
            <h6 class="fw-bold">Ringkasan Batch</h6>
            <div class="row">
                @php
                    $batchGroups = $barangMasuk->details->groupBy('batch_code');
                @endphp
                @foreach($batchGroups as $batchCode => $details)
                <div class="col-md-4 mb-3">
                    <div class="card border-left-primary shadow-md">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1">{{ $batchCode }}</h6>
                            <p class="card-text mb-1">
                                <small class="text-muted">
                                    Total Items: {{ $details->count() }}<br>
                                    Total Qty: {{ $details->sum('qty') }}<br>
                                    Total Value: Rp {{ number_format($details->sum('total_harga'), 0, ',', '.') }}
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div> --}}
    </div>
</div>

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
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}
</style>
@endsection