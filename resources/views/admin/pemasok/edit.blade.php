@extends('layouts.main')

@section('title', 'Edit Pemasok')

@section('content')

<div class="card-header">
    <h5 class="card-title mb-0">Edit Pemasok</h5>
</div>
<div class="card-body">
    <form action="{{ route('pemasok.update', $pemasok->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_pemasok" class="form-label">Nama Pemasok</label>
            <input type="text" name="nama_pemasok" id="nama_pemasok" class="form-control" value="{{ $pemasok->nama_pemasok }}" required>
        </div>
        <div class="mb-3">
            <label for="kontak" class="form-label">Kontak</label>
            <input type="text" name="kontak" id="kontak" class="form-control" value="{{ $pemasok->kontak }}" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control" required>{{ $pemasok->alamat }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('pemasok.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@endsection
