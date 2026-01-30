@extends('layouts.main')

@section('title', 'Laporan Barang Masuk')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Laporan <br><b>Barang Masuk</b></h5>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('laporan.barang-masuk.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="btn btn-secondary">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Periode Laporan -->
        <div class="alert alert-info">
            <i class="fas fa-calendar"></i> 
            <strong>Periode:</strong> {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </div>

        <!-- Ringkasan Total -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="text-primary fw-bold">{{ number_format($totalKeseluruhan->total_transaksi) }}</h3>
                        <small class="text-muted">Total Transaksi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="text-success fw-bold">{{ number_format($totalKeseluruhan->total_qty) }}</h3>
                        <small class="text-muted">Total Barang Masuk</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning fw-bold">Rp {{ number_format($totalKeseluruhan->total_harga, 0, ',', '.') }}</h3>
                        <small class="text-muted">Total Harga</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="laporanTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button">
                    Detail Transaksi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button">
                    Ringkasan per Barang
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pemasok-tab" data-bs-toggle="tab" data-bs-target="#pemasok" type="button">
                    Statistik per Pemasok
                </button>
            </li>
            {{-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status" type="button">
                    Statistik Status
                </button>
            </li> --}}
        </ul>

        <div class="tab-content" id="laporanTabsContent">
            <!-- Tab Detail Transaksi -->
            <div class="tab-pane fade show active" id="detail" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-striped" id="table-detail">
                        <thead>
                            <tr>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>Pemasok</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Batch</th>
                                <th>Qty</th>
                                <th>Harga Satuan</th>
                                <th>Total Harga</th>
                                {{-- <th>Status</th> --}}
                                <th>Tgl Kadaluarsa </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporanBarangMasuk as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('barang-masuk.show', $item->id) }}" 
                                       class="text-decoration-none">
                                        {{ $item->no_transaksi }}
                                    </a>
                                </td>
                                <td>{{ Carbon\Carbon::parse($item->tgl_masuk)->translatedFormat('j F Y') }}</td>
                                <td>{{ $item->nama_pemasok }}</td>
                                <td>{{ $item->kode_barang }}</td>
                                <td>{{ $item->nama_barang }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $item->batch_code }}</span>
                                </td>
                                <td>{{ number_format($item->qty) }}</td>
                                <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                {{-- <td>
                                    @php
                                        $statusClass = match($item->status) {
                                            'received' => 'success',
                                            'pending' => 'warning',
                                            default => 'danger'
                                        };

                                        $statusLabel = match($item->status) {
                                            'received' => 'Diterima',
                                            'pending' => 'Pending',
                                            'canceled' => 'Dibatalkan',
                                            default => ucfirst($item->status)
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td> --}}
                                <td>
                                    @if($item->expired_date)
                                        {{ Carbon\Carbon::parse($item->expired_date)->translatedFormat('j M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Ringkasan per Barang -->
            <div class="tab-pane fade" id="ringkasan" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-striped" id="table-ringkasan">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori ( / Satuan)</th>
                                <th>Total Qty</th>
                                <th>Total Harga</th>
                                <th>Rata-rata Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ringkasanBarang as $item)
                            <tr>
                                <td>{{ $item->kode_barang }}</td>
                                <td>{{ $item->nama_barang }}</td>
                                <td>{{ $item->nama_kategori ?? '-' }} / {{ $item->satuan }}</td>
                                <td>{{ number_format($item->total_qty) }}</td>
                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->rata_rata_harga, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Statistik per Pemasok -->
            <div class="tab-pane fade" id="pemasok" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-striped" id="table-pemasok">
                        <thead>
                            <tr>
                                <th>Nama Pemasok</th>
                                <th>Kontak</th>
                                <th>Total Transaksi</th>
                                <th>Total Qty</th>
                                <th>Total Harga</th>
                                <th>Persentase Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statistikPemasok as $item)
                            <tr>
                                <td>{{ $item->nama_pemasok }}</td>
                                <td>{{ $item->kontak }}</td>
                                <td>{{ number_format($item->total_transaksi) }}</td>
                                <td>{{ number_format($item->total_qty) }}</td>
                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    {{ $totalKeseluruhan->total_harga > 0 ? number_format(($item->total_harga / $totalKeseluruhan->total_harga) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Statistik Status -->
            {{-- <div class="tab-pane fade" id="status" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-striped" id="table-status">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Total Transaksi</th>
                                <th>Total Qty</th>
                                <th>Total Harga</th>
                                <th>Persentase Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statistikStatus as $item)
                            <tr>
                                <td>
                                    <span class="badge 
                                        @if($item->status == 'received') bg-success
                                        @elseif($item->status == 'pending') bg-warning
                                        @elseif($item->status == 'cancelled') bg-danger
                                        @else bg-secondary @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ number_format($item->total_transaksi) }}</td>
                                <td>{{ number_format($item->total_qty) }}</td>
                                <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                <td>
                                    {{ $totalKeseluruhan->total_harga > 0 ? number_format(($item->total_harga / $totalKeseluruhan->total_harga) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div>
    </div>
</div>

<!-- Modal Filter -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('laporan.barang-masuk.index') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable untuk tab detail
    $('#table-detail').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [
            { targets: [6, 7, 8], className: 'text-end' }
        ]
    });

    // DataTable untuk tab ringkasan
    $('#table-ringkasan').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[3, 'desc']],
        columnDefs: [
            { targets: [3, 4, 5], className: 'text-end' }
        ]
    });

    // DataTable untuk tab pemasok
    $('#table-pemasok').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[4, 'desc']],
        columnDefs: [
            { targets: [2, 3, 4, 5], className: 'text-end' }
        ]
    });

    // DataTable untuk tab status
    $('#table-status').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[3, 'desc']],
        columnDefs: [
            { targets: [1, 2, 3, 4], className: 'text-end' }
        ]
    });
});
</script>
@endpush