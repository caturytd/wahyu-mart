@extends('layouts.main')

@section('title', 'Edit Barang')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Edit Barang</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="kode_barang" class="form-label">Kode Barang</label>
                <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="{{ $barang->kode_barang }}" readonly>
            </div>
            <div class="mb-3">
                <label for="nama_barang" class="form-label">Nama Barang</label>
                <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="{{ $barang->nama_barang }}" required>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select class="form-control" id="kategori_id" name="kategori_id" required>
                    @foreach ($kategori as $item)
                        <option value="{{ $item->id }}" {{ $barang->kategori_id == $item->id ? 'selected' : '' }}>{{ $item->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" value="{{ $barang->stok }}" readonly>
            </div>
             <div class="mb-3">
                <label for="satuan" class="form-label">Satuan</label>
               <input type="text" class="form-control" id="satuan" name="satuan" value="{{ $barang->satuan }}" required>
            </div>
            <div class="mb-3">
                <label for="min_stok" class="form-label">Min Stok</label>
                <input type="number" class="form-control" id="min_stok" name="min_stok" value="{{ $barang->min_stok }}" required>
            </div>
            {{-- <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" class="form-control" id="gambar" name="gambar">
                @if ($barang->gambar)
                    <p class="mt-2"><img src="{{ asset('storage/' . $barang->gambar) }}" width="100"></p>
                @endif
            </div> --}}
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
@endsection
