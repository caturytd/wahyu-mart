@extends('layouts.main')

@section('title', 'Tambah Barang')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Tambah Barang</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="kode_barang" class="form-label">Kode Barang</label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="{{ $newCode }}" disabled>
                <input type="hidden" name="kode_barang" value="{{ $newCode }}">
            </div>
            <div class="mb-3">
                <label for="nama_barang" class="form-label">Nama Barang</label>
                <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select class="form-control" id="kategori_id" name="kategori_id" required>
                    @foreach ($kategori as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" readonly value="0">
            </div>
               <div class="mb-3">
                <label for="satuan" class="form-label">Satuan</label>
               <input type="text" class="form-control" id="satuan" name="satuan" required>
            </div>
            <div class="mb-3">
                <label for="min_stok" class="form-label">Min Stok</label>
                <input type="number" class="form-control" id="min_stok" name="min_stok" required>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar <span class="text-danger">*</span><small>opsional</small></label>
                <input type="file" class="form-control" id="gambar" name="gambar">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection