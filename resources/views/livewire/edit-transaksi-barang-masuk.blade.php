<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-2">
            <div class="page-title-box">
                <h4 class="page-title fw-bold">Edit Transaksi Barang Masuk</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('barang-masuk.index') }}">Barang Masuk</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('barang-masuk.show', $barangMasuk->id) }}">{{ $barangMasuk->no_transaksi }}</a></li>
                        <li class="breadcrumb-item active">Edit Transaksi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Info Alert -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Edit Transaksi:</strong> {{ $barangMasuk->no_transaksi }} - 
        Status:    @php
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
                                    <input type="text" class="form-control" value="{{ $barangMasuk->no_transaksi }}" readonly>
                                    <small class="text-muted">Nomor transaksi tidak dapat diubah</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tgl_masuk') is-invalid @enderror" 
                                           wire:model="tgl_masuk" max="{{ date('Y-m-d') }}">
                                    @error('tgl_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                                    <select class="form-select @error('pemasok_id') is-invalid @enderror" 
                                            wire:model="pemasok_id">
                                        <option value="">-- Pilih Pemasok --</option>
                                        @foreach ($pemasoks as $pemasok)
                                            <option value="{{ $pemasok->id }}">{{ $pemasok->nama_pemasok }}</option>
                                        @endforeach
                                    </select>
                                    @error('pemasok_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" wire:model="no_surat_jalan" 
                                           placeholder="Masukkan nomor surat jalan">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Diterima Oleh</label>
                                    <input type="text" class="form-control" wire:model="received_by" 
                                           placeholder="Nama penerima barang">
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($barangMasuk->status) }}" readonly>
                                    <small class="text-muted">Status tidak dapat diubah</small>
                                </div> --}}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" wire:model="keterangan" rows="3" 
                                      placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Detail Barang -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-package-variant me-2"></i>
                            Detail Barang
                        </h5>
                        <div>
                            <button type="button" class="btn btn-success btn-sm" wire:click="addRow">
                                <i class="bi bi-plus"></i> Tambah Barang
                            </button>
                            <button type="button" class="btn btn-info btn-sm ms-1" wire:click="togglePreview">
                                <i class="bi bi-eye"></i> {{ $showPreview ? 'Sembunyikan' : 'Preview' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Barang <span class="text-danger">*</span></th>
                                        <th width="10%">Qty <span class="text-danger">*</span></th>
                                        <th width="15%">Harga Satuan <span class="text-danger">*</span></th>
                                        <th width="15%">Total Harga</th>
                                        <th width="20%">Tanggal Kadaluarsa</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barangDetails as $index => $detail)
                                        @if(!isset($detail['_delete']) || !$detail['_delete'])
                                        <tr class="{{ isset($detail['is_existing']) && $detail['is_existing'] ? 'table-warning' : '' }}">
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
                                                @if($detail['satuan'])
                                                    <small class="text-muted">Satuan: {{ $detail['satuan'] }}</small>
                                                @endif
                                                @if(isset($detail['is_existing']) && $detail['is_existing'])
                                                    <br>
                                                        <small class="text-warning text-dark">
                                                        <i class="bi bi-pencil-square"></i> Item dari transaksi original
                                                    </small>
                                                @endif
                                                @if(isset($detail['batch_code']) && $detail['batch_code'])
                                                    <br><small class="text-info">Batch: {{ $detail['batch_code'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control @error('barangDetails.'.$index.'.qty') is-invalid @enderror" 
                                                       wire:model.lazy="barangDetails.{{ $index }}.qty" 
                                                       min="1" step="1">
                                                @error('barangDetails.'.$index.'.qty')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <div class="input-wrapper d-flex align-items-center">
                                                    <span class="me-1">Rp</span>
                                                    <input type="text" 
                                                           class="form-control harga-satuan-input @error('barangDetails.'.$index.'.harga_satuan') is-invalid @enderror" 
                                                           data-index="{{ $index }}"
                                                           value="{{ isset($detail['harga_satuan']) ? number_format($detail['harga_satuan'], 0, ',', '.') : '' }}"
                                                           placeholder="0">
                                                </div>
                                                @error('barangDetails.'.$index.'.harga_satuan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <div class="input-wrapper d-flex align-items-center">
                                                    <span class="me-1">Rp</span>
                                                    <input type="text" class="form-control" 
                                                           value="{{ number_format($detail['total_harga'], 0, ',', '.') }}" 
                                                           readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="date" 
                                                       class="form-control @error('barangDetails.'.$index.'.expired_date') is-invalid @enderror" 
                                                       wire:model="barangDetails.{{ $index }}.expired_date"
                                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                                @error('barangDetails.'.$index.'.expired_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        wire:click="removeRow({{ $index }})"
                                                        @if(count(array_filter($barangDetails, function($d) { return !isset($d['_delete']) || !$d['_delete']; })) <= 1) disabled @endif>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Show deleted items info -->
                        @php
                            $deletedCount = collect($barangDetails)->where('_delete', true)->count();
                        @endphp
                        @if($deletedCount > 0)
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>{{ $deletedCount }}</strong> item akan dihapus saat menyimpan perubahan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            Ringkasan Transaksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>No. Transaksi:</span>
                            <strong>{{ $barangMasuk->no_transaksi }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Barang:</span>
                            <strong>{{ count(array_filter($barangDetails, function($d) { return !isset($d['_delete']) || !$d['_delete']; })) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Qty:</span>
                            <strong>{{ $this->totalQty }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h6">Total Harga:</span>
                            <strong class="h6 text-primary">Rp {{ number_format($this->totalHarga, 0, ',', '.') }}</strong>
                        </div>

                        <!-- Change Summary -->
                        @php
                            $originalTotal = $barangMasuk->total_harga;
                            $currentTotal = $this->totalHarga;
                            $priceDifference = $currentTotal - $originalTotal;
                        @endphp
                        
                        @if($priceDifference != 0)
                            <div class="alert {{ $priceDifference > 0 ? 'alert-info' : 'alert-warning' }} p-2 mb-3">
                                <large>
                                    <strong>Perubahan Harga:</strong><br>
                                    Asli: Rp {{ number_format($originalTotal, 0, ',', '.') }}<br>
                                    Saat Ini: Rp {{ number_format($currentTotal, 0, ',', '.') }}<br>
                                    Selisih: 
                                    <span class="{{ $priceDifference > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $priceDifference > 0 ? '+' : '' }}Rp {{ number_format($priceDifference, 0, ',', '.') }}
                                    </span>
                                </large>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2 me-2"></i>
                                Update Transaksi
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Reset ke Data Asli
                            </button>
                            <a href="{{ route('barang-masuk.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Original Data Summary -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-archive me-2"></i>
                            Data Asli
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Total Item:</small>
                            <small><strong>{{ count($originalDetails) }}</strong></small>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small>Total Qty:</small>
                            <small><strong>{{ collect($originalDetails)->sum('qty') }}</strong></small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>Total Harga:</small>
                            <small><strong>Rp {{ number_format($barangMasuk->total_harga, 0, ',', '.') }}</strong></small>
                        </div>
                    </div>
                </div>

                @if($showPreview && !empty($barangDetails))
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Preview Detail</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>Qty</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($barangDetails as $detail)
                                            @if($detail['barang_id'] && (!isset($detail['_delete']) || !$detail['_delete']))
                                                <tr>
                                                    <td>
                                                        <small>{{ $detail['nama_barang'] ?: 'Barang dipilih' }}</small>
                                                    </td>
                                                    <td><small>{{ $detail['qty'] }}</small></td>
                                                    <td><small>{{ number_format($detail['harga_satuan'], 0, ',', '.') }}</small></td>
                                                    <td>
                                                        @if(isset($detail['is_existing']) && $detail['is_existing'])
                                                            <span class="badge bg-warning text-dark">Edit</span>
                                                        @else
                                                            <span class="badge bg-success">Baru</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        
                                        @foreach($barangDetails as $detail)
                                            @if(isset($detail['_delete']) && $detail['_delete'])
                                                <tr class="text-decoration-line-through text-muted">
                                                    <td><small>{{ $detail['nama_barang'] }}</small></td>
                                                    <td><small>{{ $detail['qty'] }}</small></td>
                                                    <td><small>{{ number_format($detail['harga_satuan'], 0, ',', '.') }}</small></td>
                                                    <td><span class="badge bg-danger">Hapus</span></td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
    
    <!-- Confirmation Modal untuk perubahan besar -->
    @if($priceDifference != 0 && abs($priceDifference) > 1000000)
        <div class="modal fade" id="confirmationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Perubahan Besar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Perubahan harga yang Anda lakukan cukup besar:</p>
                        <ul>
                            <li>Harga Asli: Rp {{ number_format($originalTotal, 0, ',', '.') }}</li>
                            <li>Harga Baru: Rp {{ number_format($currentTotal, 0, ',', '.') }}</li>
                            <li>Selisih: Rp {{ number_format(abs($priceDifference), 0, ',', '.') }}</li>
                        </ul>
                        <p>Apakah Anda yakin ingin melanjutkan?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" wire:click="update">
                            Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Single initialization
    initializeCurrencyInputs();
    
    // Livewire hook - lebih efisien
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', () => {
            // Delay untuk memastikan DOM sudah selesai di-update
            setTimeout(initializeCurrencyInputs, 50);
        });
    }
});

// Fungsi format currency yang dioptimasi
function formatCurrency(value) {
    // Hapus semua karakter non-digit
    const numericValue = value.toString().replace(/\D/g, '');
    
    // Return kosong jika tidak ada nilai
    if (!numericValue) return '';
    
    // Format menggunakan Intl.NumberFormat
    return new Intl.NumberFormat('id-ID').format(parseInt(numericValue));
}

// Fungsi untuk mendapatkan nilai numerik
function getNumericValue(formattedValue) {
    return formattedValue.replace(/\./g, '');
}

// Inisialisasi input currency
function initializeCurrencyInputs() {
    const inputs = document.querySelectorAll('.harga-satuan-input');
    
    inputs.forEach(input => {
        // Skip jika sudah di-initialize
        if (input.dataset.currencyInitialized) return;
        
        // Mark sebagai initialized
        input.dataset.currencyInitialized = 'true';
        
        // Format nilai awal jika ada
        if (input.value && input.value !== '0') {
            const formatted = formatCurrency(input.value);
            if (formatted !== input.value) {
                input.value = formatted;
            }
        }
        
        // Event handler yang dioptimasi
        input.addEventListener('input', function(e) {
            handleCurrencyInput(e.target);
        });
        
        // Handle paste event
        input.addEventListener('paste', function(e) {
            setTimeout(() => handleCurrencyInput(e.target), 10);
        });
        
        // Handle focus - select all untuk UX yang lebih baik
        input.addEventListener('focus', function(e) {
            setTimeout(() => e.target.select(), 10);
        });
    });
}

// Handler input currency yang dioptimasi
function handleCurrencyInput(input) {
    const index = input.dataset.index;
    if (!index) return;
    
    // Simpan posisi cursor
    const cursorPosition = input.selectionStart;
    const oldValue = input.value;
    const oldLength = oldValue.length;
    
    // Format nilai
    const formattedValue = formatCurrency(input.value);
    const newLength = formattedValue.length;
    
    // Update input value
    input.value = formattedValue;
    
    // Hitung dan set posisi cursor baru
    const lengthDiff = newLength - oldLength;
    const newCursorPosition = Math.max(0, cursorPosition + lengthDiff);
    
    // Set cursor position dengan timeout untuk browser compatibility
    setTimeout(() => {
        if (input === document.activeElement) {
            input.setSelectionRange(newCursorPosition, newCursorPosition);
        }
    }, 0);
    
    // Update Livewire dengan nilai numerik
    const numericValue = getNumericValue(formattedValue);
    
    // Gunakan setTimeout untuk menghindari konflik dengan Livewire
    setTimeout(() => {
        if (typeof Livewire !== 'undefined' && window.Livewire) {
            try {
                // Update Livewire component
                Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                    .set(`barangDetails.${index}.harga_satuan`, numericValue);
            } catch (error) {
                console.warn('Livewire update failed:', error);
                // Fallback: trigger wire:model update
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }, 100);
}

// Clean up function untuk menghapus event listeners lama
function cleanupCurrencyInputs() {
    document.querySelectorAll('.harga-satuan-input[data-currency-initialized]').forEach(input => {
        input.removeAttribute('data-currency-initialized');
    });
}

// Helper function untuk re-initialize jika diperlukan
window.reinitializeCurrency = function() {
    cleanupCurrencyInputs();
    initializeCurrencyInputs();
};

// Auto-focus script yang dioptimasi
document.addEventListener('livewire:navigated', function() {
    setTimeout(() => {
        const lastRow = document.querySelector('tbody tr:last-child select');
        if (lastRow && !lastRow.value) {
            lastRow.focus();
        }
        initializeCurrencyInputs();
    }, 100);
});

// Konfirmasi untuk perubahan besar
document.addEventListener('livewire:updated', function() {
    // Check if there's a large price difference and show modal
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        // Modal logic can be added here if needed
    }
});

// Warning untuk data yang sudah existing
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltip untuk existing data
    const existingRows = document.querySelectorAll('.table-warning');
    existingRows.forEach(row => {
        row.setAttribute('title', 'Data ini sudah ada di database. Perubahan akan mempengaruhi stok dan batch.');
    });
});
</script>
@endpush