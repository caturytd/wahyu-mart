@extends('layouts.main')

@section('title', 'Barang')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Data Barang</h5>
        <div>
        
            <a href="{{ route('barang.create') }}" class="btn btn-primary mb-3"><i class="fas fa-circle-plus"></i> Tambah Barang</a>
            {{-- <a href="{{ route('barang.laporan', ['kategori' => request('kategori')]) }}" class="btn btn-secondary mb-3"><i class="fas fa-file-pdf"></i> PDF</a> --}}

        </div>
    </div>

    <div class="card-body">

        @include('includes.alert')

        <form method="GET" action="{{ route('barang.index') }}">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select name="kategori" class="form-control mb-3">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped" id="table-2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Min. Stok</th>
                        <th>Satuan</th>
                        {{-- <th>Gambar</th> --}}
                        @if(auth()->user()->role === 'superadmin')
                        <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangs as $barang)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $barang->kode_barang }}</td>
                            <td>{{ $barang->nama_barang }}</td>
                            <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ $barang->stok }} </td>
                            <td>{{ $barang->min_stok }}</td>
                            <td>{{ $barang->satuan }}</td>
                              {{-- <td>
                                @if ($barang->gambar)
                                    <img src="{{ asset('storage/' . $barang->gambar) }}" alt="Gambar" 
                                        class="img-fluid rounded border preview-image"
                                        style="max-height: 100px; width: auto; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#imageModal"
                                        data-image="{{ asset('storage/' . $barang->gambar) }}">
                                @else
                                    <img src="{{ asset("assets/img/default.png") }}" alt="Gambar" 
                                        class="img-fluid rounded border preview-image"
                                        style="max-height: 100px; width: auto; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#imageModal"
                                        data-image="{{ asset("assets/img/default.png") }}">
                                @endif
                            </td> --}}
                        @if(auth()->user()->role === 'superadmin')
                     <td>
                            <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pen-to-square"></i>
                            </a>

                            <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $barang->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <form id="delete-form-{{ $barang->id }}"
                                action="{{ route('barang.destroy', $barang->id) }}"
                                method="POST"
                                style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                    </td>
                    @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@include('admin.modal.barang')
@endsection
