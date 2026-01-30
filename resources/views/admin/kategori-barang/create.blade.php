@extends('layouts.main')

@section('title', 'Tambah kategori Barang')

@section('content')

<div class="card-header">
    <h5 class="card-title mb-0">Tambah kategori Barang</h5>
</div>
<div class="card-body">
    <form action="{{ route('kategori-barang.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nama_kategori" class="form-label">Nama kategori Barang</label>
            <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('kategori-barang.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@endsection
