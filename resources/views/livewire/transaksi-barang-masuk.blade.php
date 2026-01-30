<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-2">
            <div class="page-title-box">
                <h4 class="page-title fw-bold">Transaksi Barang Masuk</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('barang-masuk.index') }}">Barang Masuk</a></li>
                        <li class="breadcrumb-item active">Transaksi</li>
                    </ol>
                </div>
            </div>
        </div>
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

    <form wire:submit.prevent="simpan">
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
                                    <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tgl_masuk') is-invalid @enderror" 
                                           wire:model="tgl_masuk" max="{{ date('Y-m-d') }}">
                                    @error('tgl_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" wire:model="no_surat_jalan" 
                                           placeholder="Masukkan nomor surat jalan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Diterima Oleh</label>
                                    <input type="text" class="form-control" wire:model="received_by" 
                                           placeholder="Nama penerima barang">
                                </div>
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
                                        <tr>
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
                                                        @if(count($barangDetails) <= 1) disabled @endif>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                            <span>Total Barang:</span>
                            <strong>{{ count($barangDetails) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Qty:</span>
                            <strong>{{ $this->totalQty }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h6">Total Harga:</span>
                            <strong class="h6 text-primary">Rp.{{ number_format($this->totalHarga, 0, ',', '.') }}</strong>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2 me-2"></i>
                                Simpan Transaksi
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Reset Form
                            </button>
                            <a href="{{ route('barang-masuk.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Kembali
                            </a>
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
                                    <tbody>
                                        @foreach($barangDetails as $detail)
                                            @if($detail['barang_id'])
                                                <tr>
                                                    <td>{{ $detail['nama_barang'] ?: 'Barang dipilih' }}</td>
                                                    <td>{{ $detail['qty'] }}</td>
                                                    <td>{{ number_format($detail['harga_satuan'], 0, ',', '.') }}</td>
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
</script>
@endpush