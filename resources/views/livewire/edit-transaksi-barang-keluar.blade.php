<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-2">
            <div class="page-title-box">
                <h4 class="page-title fw-bold">Edit Transaksi Barang Keluar</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('barang-keluar.index') }}">Barang Keluar</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('barang-keluar.show', $barangKeluar->id) }}">{{ $barangKeluar->no_transaksi }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert for Original Transaction Info -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Edit Transaksi:</strong> : {{ $barangKeluar->no_transaksi }}
        {{-- yang dibuat pada {{ date('d/m/Y H:i', strtotime($barangKeluar->created_at)) }} --}}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-alert-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

        @if (session()->has('errors_stock'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-alert-circle me-2"></i>
            <strong>Stok tidak mencukupi:</strong>
            <ul class="mb-0 mt-1">
                @foreach (session('errors_stock') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    <form wire:submit.prevent="update">
        <div class="row">
            <div class="col-lg-8">
                <!-- Header Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-information-outline me-2"></i>
                            Informasi Transaksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. Transaksi</label>
                                    <input type="text" class="form-control" value="{{ $barangKeluar->no_transaksi }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Keluar <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tgl_keluar') is-invalid @enderror" 
                                           wire:model="tgl_keluar" max="{{ date('Y-m-d') }}">
                                    @error('tgl_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Keluar <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jenis_keluar') is-invalid @enderror" 
                                            wire:model.lazy="jenis_keluar">
                                        @foreach($jenisKeluarOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Dibuat pada</label>
                                    <input type="text" class="form-control" 
                                           value="{{ date('d/m/Y H:i', strtotime($barangKeluar->created_at)) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" wire:model="keterangan" rows="3" 
                                      placeholder="Keterangan transaksi barang keluar"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Detail Barang -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-package-variant-closed me-2"></i>
                            List Barang Keluar
                        </h5>
                        <div>
                            <button type="button" class="btn btn-success btn-sm" wire:click="addRow">
                                <i class="bi bi-plus"></i> Tambah Item
                            </button>
                            <button type="button" class="btn btn-info btn-sm ms-1" wire:click="toggleFifoPreview">
                                <i class="bi bi-eye"></i> {{ $showFifoPreview ? 'Sembunyikan' : 'Preview' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Barang <span class="text-danger">*</span></th>
                                        <th width="10%">Stok</th>
                                        <th width="15%">Qty Keluar <span class="text-danger">*</span></th>
                                        <th width="15%">Harga</th>
                                        <th width="20%">Batch Info (Tersedia)</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barangDetails as $index => $detail)
                                        <tr class="{{ $detail['detail_id'] ? 'table-warning' : '' }}">
                                            <td>
                                                <select class="form-select @error('barangDetails.'.$index.'.barang_id') is-invalid @enderror" 
                                                        wire:model.lazy="barangDetails.{{ $index }}.barang_id">
                                                    <option value="">-- Pilih Barang --</option>
                                                    @foreach ($barangs as $barang)
                                                        <option value="{{ $barang->id }}">
                                                            {{ $barang->nama_barang }} ({{ $barang->kode_barang }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('barangDetails.'.$index.'.barang_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($detail['nama_barang'])
                                                    <small class="text-muted">{{ $detail['nama_barang'] }} - {{ $detail['satuan'] }}</small>
                                                @endif
                                                @if($detail['detail_id'])
                                                    <small class="text-warning d-block text-dark">
                                                        <i class="bi bi-pencil-square"></i> Item dari transaksi original
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $detail['stok_tersedia'] > 0 ? 'success' : 'danger' }}">
                                                    {{ number_format($detail['stok_tersedia']) }}
                                                </span>
                                                @if($detail['detail_id'] && $detail['original_qty'])
                                                    <small class="text-muted d-block">
                                                        (Termasuk {{ number_format($detail['original_qty']) }} dari transaksi ini)
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control @error('barangDetails.'.$index.'.qty') is-invalid @enderror" 
                                                       wire:model.lazy="barangDetails.{{ $index }}.qty" 
                                                       min="1" step="1"
                                                       max="{{ $detail['stok_tersedia'] }}">
                                                @error('barangDetails.'.$index.'.qty')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($detail['detail_id'] && $detail['original_qty'])
                                                    <small class="text-muted">Original: {{ number_format($detail['original_qty']) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-primary">
                                                    Rp{{ number_format($detail['estimasi_nilai'], 0, ',', '.') }}
                                                </strong>
                                            </td>
                                            <td>
                                                @if(!empty($detail['batch_info']))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#batchModal{{ $index }}">
                                                        <i class="bi bi-information"></i> 
                                                        {{ count($detail['batch_info']) }} Batch
                                                    </button>

                                                    <!-- Batch Info Modal -->
                                                    <div class="modal fade" id="batchModal{{ $index }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Detail Batch - {{ $detail['nama_barang'] }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Kode Batch</th>
                                                                                    <th>Qty Tersisa</th>
                                                                                    <th>Tgl Masuk</th>
                                                                                    <th>Expired</th>
                                                                                    <th>Harga</th>
                                                                                    <th>Pemasok</th>
                                                                                    <th>Status</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($detail['batch_info'] as $batch)
                                                                                    <tr>
                                                                                        <td>
                                                                                            <code>{{ $batch['batch_code'] }}</code>
                                                                                        </td>
                                                                                        <td>
                                                                                            <span class="badge bg-info">
                                                                                                {{ number_format($batch['qty_tersisa']) }}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td>{{ $batch['tanggal_masuk'] }}</td>
                                                                                        <td>{{ $batch['expired_date'] }}</td>
                                                                                        <td>Rp{{ number_format($batch['harga_satuan'], 0, ',', '.') }}</td>
                                                                                        <td>{{ $batch['pemasok'] }}</td>
                                                                                        <td>
                                                                                            @if($batch['status_expired'] == 'expired')
                                                                                                <span class="badge bg-danger">Kadaluarsa</span>
                                                                                            @elseif($batch['status_expired'] == 'near_expired')
                                                                                                <span class="badge bg-warning">Hampir Kadaluarsa</span>
                                                                                            @elseif($batch['status_expired'] == 'good')
                                                                                                <span class="badge bg-success">Baik</span>
                                                                                            @else
                                                                                                <span class="badge bg-secondary">Tidak ada Tanggal Kadaluarsa</span>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        wire:click="removeRow({{ $index }})"
                                                        @if(count($barangDetails) <= 1) disabled @endif>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Baris dengan latar belakang kuning adalah item dari transaksi original. 
                            Stok yang ditampilkan sudah termasuk quantity dari transaksi yang sedang diedit.
                        </div> --}}
                    </div>
                </div>

                <!-- FIFO Preview with Detailed Batch Information -->
                @if($showFifoPreview && !empty($fifoPreview['items']))
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calculator-variant me-2"></i>
                                Detail Transaksi (Preview)
                                </h5>
                        </div>
                    <div class="card-body">
                        @foreach($fifoPreview['items'] as $previewItem)
                            @php
                                $barangDetail = collect($barangDetails)->firstWhere('barang_id', $previewItem['barang_id']);
                            @endphp
                            
                            <!-- Item Header -->
                            <div class="border rounded mb-4 overflow-hidden">
                                <div class="bg-light p-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-1 fw-bold text-primary">
                                                <i class="bi bi-package me-2"></i>
                                                {{ $barangDetail['nama_barang'] ?? 'Unknown' }}
                                            </h6>
                                            <small class="text-muted">Qty Keluar: {{ number_format($previewItem['qty']) }} {{ $barangDetail['satuan'] ?? 'pcs' }}</small>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Rata-rata Harga</small>
                                                    <span class="fw-bold">Rp{{ number_format($previewItem['average_cost'] ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Total Nilai</small>
                                                    <span class="fw-bold text-success">Rp{{ number_format($previewItem['total_cost'] ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Batch Details -->
                                @if(!empty($previewItem['batch_details']))
                                    <div class="p-3">
                                        <h6 class="mb-3 text-secondary">
                                            <i class="bi bi-layers me-2"></i>
                                            Detail Batch yang Digunakan ({{ count($previewItem['batch_details']) }} batch)
                                        </h6>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th width="15%">Kode Batch</th>
                                                        <th width="12%">Qty Digunakan</th>
                                                        <th width="15%">Harga Satuan</th>
                                                        <th width="15%">Subtotal</th>
                                                        <th width="12%">Tgl Masuk</th>
                                                        <th width="12%">Tgl Kadaluarsa</th>
                                                        <th width="19%">Pemasok</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($previewItem['batch_details'] as $batch)
                                                        <tr>
                                                            <td>
                                                                <code class="bg-primary text-white px-2 py-1 rounded">
                                                                    {{ $batch['batch_code'] }}
                                                                </code>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info text-white">
                                                                    {{ number_format($batch['qty_digunakan']) }}
                                                                </span>
                                                                <small class="text-muted d-block">
                                                                    dari {{ number_format($batch['qty_tersisa_awal']) }}
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <span class="fw-medium">
                                                                    Rp{{ number_format($batch['harga_satuan'], 0, ',', '.') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-bold text-success">
                                                                    Rp{{ number_format($batch['subtotal'], 0, ',', '.') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small>{{ date('d/m/Y', strtotime($batch['tanggal_masuk'])) }}</small>
                                                            </td>
                                                            <td>
                                                                @if($batch['expired_date'])
                                                                    <small class="{{ 
                                                                        $batch['status_expired'] == 'expired' ? 'text-danger' : 
                                                                        ($batch['status_expired'] == 'near_expired' ? 'text-warning' : 'text-success')
                                                                    }}">
                                                                        {{ date('d/m/Y', strtotime($batch['expired_date'])) }}
                                                                    </small>
                                                                    <br>
                                                                    @if($batch['status_expired'] == 'expired')
                                                                        <span class="badge bg-danger">Kadaluarsa</span>
                                                                    @elseif($batch['status_expired'] == 'near_expired')
                                                                        <span class="badge bg-warning">Hampir Kadaluarsa</span>
                                                                    @else
                                                                        <span class="badge bg-success">Baik</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                    <br>
                                                                    <span class="badge bg-secondary">Tidak Ada Tgl Kadaluarsa</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <small class="text-dark">{{ $batch['pemasok_nama'] ?? 'Unknown' }}</small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light border-top-2">
                                                    <tr>
                                                        <td class="fw-bold">Total</td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                {{ number_format(collect($previewItem['batch_details'])->sum('qty_digunakan')) }}
                                                            </span>
                                                        </td>
                                                        <td colspan="2">
                                                            <span class="fw-bold text-success">
                                                                Rp{{ number_format(collect($previewItem['batch_details'])->sum('subtotal'), 0, ',', '.') }}
                                                            </span>
                                                        </td>
                                                        <td colspan="3">
                                                            <small class="text-muted">
                                                                {{ count($previewItem['batch_details']) }} batch digunakan
                                                            </small>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-3">
                                        <div class="alert alert-warning mb-0">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Detail batch tidak tersedia untuk item ini.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        <!-- Grand Total Summary -->
                        <div class="row mt-4">
                            <div class="col-md-8">
                                <div class="alert alert-info mb-0">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Total Item</small>
                                            <span class="fw-bold">{{ count($fifoPreview['items']) }} jenis barang</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Total Qty</small>
                                            <span class="fw-bold">{{ number_format(collect($fifoPreview['items'])->sum('qty')) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0 text-center">
                                    <small class="text-muted d-block">Grand Total</small>
                                    <h5 class="mb-0 fw-bold">
                                        Rp{{ number_format($fifoPreview['grand_total'] ?? 0, 0, ',', '.') }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </div>

            <!-- Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            Ringkasan Transaksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Original Transaction Info -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                Transaksi Original
                            </h6>
                            <small>
                                <strong>No:</strong> {{ $barangKeluar->no_transaksi }}<br>
                                <strong>Dibuat:</strong> {{ date('d/m/Y H:i', strtotime($barangKeluar->created_at)) }}<br>
                            </small>
                        </div>

                        <!-- Current Summary -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Item:</span>
                            <strong>{{ count($barangDetails) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Qty:</span>
                            <strong>{{ number_format($this->totalQty) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fs-5">Total Harga:</span>
                            <strong class="fs-5 text-primary">Rp{{ number_format($this->totalNilai, 0, ',', '.') }}</strong>
                        </div>
                        
                        <!-- Jenis Keluar Info -->
                        <div class="alert alert-info mb-3">
                            <strong>Jenis:</strong> {{ $jenisKeluarOptions[$jenis_keluar] ?? 'Distribusi' }}
                            @if($jenis_keluar == 'distribusi')
                                <i class="bi bi-truck ms-2"></i>
                            {{-- @elseif($jenis_keluar == 'retur')
                                <i class="bi bi-arrow-return-left ms-2"></i>
                            @elseif($jenis_keluar == 'rusak')
                                <i class="bi bi-exclamation-triangle ms-2"></i>
                            @elseif($jenis_keluar == 'expired')
                                <i class="bi bi-calendar-x ms-2"></i>
                            @elseif($jenis_keluar == 'hilang')
                                <i class="bi bi-question-circle ms-2"></i> --}}
                            @endif
                        </div>

                        <!-- Changes Summary -->
                             @php
                                $originalTotal = collect($originalDetails)->sum('qty');
                                $currentTotal = $this->totalQty;
                                $difference = $currentTotal - $originalTotal;
                            @endphp

                        @if(($difference != 0))
                            <div class="alert alert-warning mb-3">
                                <h6 class="mb-2">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Perubahan
                                </h6>
                                <large>
                                    <strong>Qty Original:</strong> {{ number_format($originalTotal) }}<br>
                                    <strong>Qty Sekarang:</strong> {{ number_format($currentTotal) }}<br>
                                    <strong>Selisih:</strong> 
                                    @if($difference > 0)
                                        <span class="text-success">+{{ number_format($difference) }}</span>
                                    @elseif($difference < 0)
                                        <span class="text-danger">{{ number_format($difference) }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </small>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                Update Transaksi
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Reset ke Original
                            </button>
                            <a href="{{ route('barang-keluar.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Items Status Info -->
                @if(!empty($barangDetails))
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-list-check me-2"></i>
                                Status Item
                            </h6>
                        </div>
                        <div class="card-body">
                            @php
                                $originalItems = collect($barangDetails)->where('detail_id', '!=', null)->count();
                                $newItems = collect($barangDetails)->where('detail_id', null)->count();
                                $changedItems = 0;
                                
                                foreach($barangDetails as $detail) {
                                    if($detail['detail_id'] && $detail['qty'] != $detail['original_qty']) {
                                        $changedItems++;
                                    }
                                }
                            @endphp
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-warning fw-bold">{{ $originalItems }}</div>
                                        <small class="text-muted">Original</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-success fw-bold">{{ $newItems }}</div>
                                        <small class="text-muted">Baru</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-info fw-bold">{{ $changedItems }}</div>
                                        <small class="text-muted">Diubah</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Stock Warning -->
                {{-- @if(!empty($barangDetails))
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Peringatan Stok
                            </h6>
                        </div>
                        <div class="card-body">
                            @php
                                $hasLowStock = false;
                                $hasInsufficientStock = false;
                            @endphp
                            
                            @foreach($barangDetails as $detail)
                                @if($detail['barang_id'])
                                    @php
                                        $availableStock = $detail['stok_tersedia'];
                                        $requestedQty = $detail['qty'];
                                    @endphp
                                    
                                    @if($requestedQty > $availableStock)
                                        @php $hasInsufficientStock = true; @endphp
                                        <div class="alert alert-danger alert-sm mb-2">
                                            <small>
                                                <i class="bi bi-x-circle me-1"></i>
                                                <strong>{{ $detail['nama_barang'] }}</strong><br>
                                                Stok tidak cukup: {{ number_format($availableStock) }}/{{ number_format($requestedQty) }}
                                            </small>
                                        </div>
                                    @elseif($availableStock < 10)
                                        @php $hasLowStock = true; @endphp
                                        <div class="alert alert-warning alert-sm mb-2">
                                            <small>
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <strong>{{ $detail['nama_barang'] }}</strong><br>
                                                Stok rendah: {{ number_format($availableStock) }} {{ $detail['satuan'] }}
                                            </small>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                            
                            @if(!$hasLowStock && !$hasInsufficientStock)
                                <div class="alert alert-success alert-sm mb-0">
                                    <small>
                                        <i class="bi bi-check-circle me-1"></i>
                                        Semua stok mencukupi
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif --}}
            </div>
    </form>
    <!-- Loading Overlay -->
{{-- <div wire:loading class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50" style="z-index: 9999;">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div> --}}
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-focus on new row when added
        document.addEventListener('livewire:update', function () {
            const lastRow = document.querySelector('tbody tr:last-child select');
            if (lastRow && !lastRow.value) {
                lastRow.focus();
            }
        });

        // Prevent form submission on Enter key for number inputs
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.type === 'number') {
                e.preventDefault();
                e.target.blur(); // Trigger the wire:model update
            }
        });

        // (Konfirmasi reset dihapus)

        // Auto-update preview when values change
        let updateTimeout;
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[type="number"]') || e.target.matches('select')) {
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(() => {
                    if (window.Livewire) {
                        window.Livewire.emit('updateFifoPreview');
                    }
                }, 500);
            }
        });

        // Highlight changed items
        document.addEventListener('livewire:update', function() {
            document.querySelectorAll('tr.table-warning').forEach(row => {
                const originalQty = row.querySelector('small:contains("Original:")');
                const currentQty = row.querySelector('input[type="number"]');
                
                if (originalQty && currentQty) {
                    const original = parseFloat(originalQty.textContent.replace(/[^0-9.-]+/g, ''));
                    const current = parseFloat(currentQty.value);
                    
                    if (original !== current) {
                        row.classList.add('table-info');
                        row.classList.remove('table-warning');
                    }
                }
            });
        });
    });

    // Handle batch modal focus
    document.addEventListener('shown.bs.modal', function (e) {
        if (e.target.id.includes('batchModal')) {
            e.target.querySelector('.modal-body').focus();
        }
    });

    // Smooth scroll to alerts
    document.addEventListener('livewire:update', function() {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>


<style>
    .alert-sm {
        padding: 0.375rem 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    .border-top-2 {
        border-top: 2px solid #dee2e6 !important;
    }
    
    .bg-opacity-50 {
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
    
    /* Custom styling for changed items */
    tr.table-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
        border-left: 3px solid #0dcaf0;
    }
    
    tr.table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
        border-left: 3px solid #ffc107;
    }
    
    /* Loading state for inputs */
    .form-control:disabled,
    .form-select:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Batch info styling */
    .modal-lg {
        max-width: 90%;
    }
    
    @media (max-width: 768px) {
        .modal-lg {
            max-width: 95%;
        }
    }
</style>