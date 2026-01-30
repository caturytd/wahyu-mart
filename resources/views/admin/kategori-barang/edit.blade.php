@extends('layouts.main')

@section('title', 'Edit kategori Barang')

@section('content')

<div class="card-header">
    <h5 class="card-title mb-0">Edit kategori Barang</h5>
</div>
<div class="card-body">
    <form action="{{ route('kategori-barang.update', $kategoriBarang->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_kategori" class="form-label">Nama kategori Barang</label>
            <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" value="{{ $kategoriBarang->nama_kategori }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('kategori-barang.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@endsection
