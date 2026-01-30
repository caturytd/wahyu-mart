@extends('layouts.main')

@section('title', 'Batch Barang')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Data Batch Barang</h5>
        <div>
            {{-- <a href="" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export Excel
            </a> --}}
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter"></i> Filter
        </button>
        </div>
    </div>

    <div class="card-body">
        @include('includes.alert')

         <!-- Modal Filter -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form method="GET" action="{{ route('barang-batch.index') }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filterModalLabel">Filter Batch Barang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="barang_id" class="form-label">Barang</label>
                                    <select name="barang_id" id="barang_id" class="form-control">
                                        <option value="">-- Semua Barang --</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->id }}" {{ request('barang_id') == $barang->id ? 'selected' : '' }}>
                                                {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="pemasok_id" class="form-label">Pemasok</label>
                                    <select name="pemasok_id" id="pemasok_id" class="form-control">
                                        <option value="">-- Semua Pemasok --</option>
                                        @foreach($pemasoks as $pemasok)
                                            <option value="{{ $pemasok->id }}" {{ request('pemasok_id') == $pemasok->id ? 'selected' : '' }}>
                                                {{ $pemasok->nama_pemasok }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="status_expired" class="form-label">Status Expired</label>
                                    <select name="status_expired" id="status_expired" class="form-control">
                                        <option value="">-- Semua Status --</option>
                                        <option value="expired" {{ request('status_expired') == 'expired' ? 'selected' : '' }}>Sudah Expired</option>
                                        <option value="akan_expired" {{ request('status_expired') == 'akan_expired' ? 'selected' : '' }}>Akan Expired (30 hari)</option>
                                        <option value="normal" {{ request('status_expired') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                                    <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="status_stok" class="form-label">Status Stok</label>
                                    <select name="status_stok" id="status_stok" class="form-control">
                                        <option value="">-- Semua Status --</option>
                                        <option value="tersedia" {{ request('status_stok') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                        <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('barang-batch.index') }}" class="btn btn-secondary">Reset</a>
                            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="table-2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Batch</th>
                        <th>Barang</th>
                        <th>Pemasok</th>
                        <th>Tanggal Masuk</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Qty Awal</th>
                        <th>Qty Tersisa</th>
                        <th>Harga Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td>{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                            <td>
                                <span class="badge bg-info">{{ $batch->batch_code }}</span>
                            </td>
                            <td>
                                <strong>{{ $batch->barang->kode_barang }}</strong><br>
                                <small class="text-muted">{{ $batch->barang->nama_barang }}</small>
                            </td>
                            <td>{{ $batch->pemasok->nama_pemasok }}</td>
                            <td>{{ \Carbon\Carbon::parse($batch->tanggal_masuk)->format('d/m/Y') }}</td>
                          <td>
                            @if($batch->expired_date)
                                @php
                                    $expiredDate = \Carbon\Carbon::parse($batch->expired_date)->startOfDay();
                                    $today = now()->startOfDay();
                                    $diffInDays = $today->diffInDays($expiredDate, false);
                                @endphp

                                @if($diffInDays < 0)
                                    <span class="badge bg-danger">
                                        {{ $expiredDate->format('d/m/Y') }} (Expired)
                                    </span>
                                @elseif($diffInDays <= 30)
                                    <span class="badge bg-warning text-dark">
                                        {{ $expiredDate->format('d/m/Y') }} ({{ $diffInDays }} hari lagi)
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        {{ $expiredDate->format('d/m/Y') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">Tidak ada tanggal kadaluarsa</span>
                            @endif
                        </td>
                            <td>{{ number_format($batch->qty_awal) }}</td>
                            <td>
                                @if($batch->qty_tersisa > 0)
                                    <span class="badge bg-success">{{ number_format($batch->qty_tersisa) }}</span>
                                @else
                                    <span class="badge bg-danger">Habis</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($batch->harga_satuan, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusClass = 'bg-success';
                                    $statusText = 'Baik';
                                    
                                    if ($batch->qty_tersisa <= 0) {
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Habis';
                                    } elseif ($batch->expired_date && \Carbon\Carbon::parse($batch->expired_date)->isPast()) {
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Expired';
                                    } elseif (
                                        $batch->expired_date &&
                                        \Carbon\Carbon::parse($batch->expired_date)->between(now(), now()->addDays(30))
                                    ) {
                                        $statusClass = 'bg-warning text-dark';
                                        $statusText = 'Akan Expired';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada data batch barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- <!-- Pagination -->
        @if($batches->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $batches->appends(request()->query())->links() }}
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Batch</h5>
                        <h3>{{ $batches->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Batch Aktif</h5>
                        <h3>{{ $batches->where('qty_tersisa', '>', 0)->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Akan Expired</h5>
                        <h3>{{ $batches->filter(function($batch) {
                            return $batch->expired_date && 
                                   \Carbon\Carbon::parse($batch->expired_date)->diffInDays(now()) <= 30 && 
                                   !\Carbon\Carbon::parse($batch->expired_date)->isPast();
                        })->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Expired</h5>
                        <h3>{{ $batches->filter(function($batch) {
                            return $batch->expired_date && \Carbon\Carbon::parse($batch->expired_date)->isPast();
                        })->count() }}</h3>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#barang_id, #pemasok_id').select2({
        dropdownParent: $('#filterModal'), // agar select2 muncul di modal
        placeholder: "Pilih...",
        allowClear: true
    });
});
</script>
@endpush
