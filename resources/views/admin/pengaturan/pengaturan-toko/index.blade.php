@extends('layouts.main')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Pengaturan Toko</h5>
    </div>
    <div class="card-body">
        @include('includes.alert')

        <form action="{{ route('pengaturan-toko.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="nama_toko" class="form-label">Nama Toko</label>
                <input type="text" name="nama_toko" class="form-control" value="{{ old('nama_toko', $pengaturanToko->nama_toko ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="desa" class="form-label">Desa</label>
                <input type="text" name="desa" class="form-control" value="{{ old('desa', $pengaturanToko->desa ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="kecamatan" class="form-label">Kecamatan</label>
                <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', $pengaturanToko->kecamatan ?? '') }}">
            </div>

            <div class="mb-3">
                <label for="kabupaten" class="form-label">Kabupaten</label>
                <input type="text" name="kabupaten" class="form-control" value="{{ old('kabupaten', $pengaturanToko->kabupaten ?? '') }}">
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection
