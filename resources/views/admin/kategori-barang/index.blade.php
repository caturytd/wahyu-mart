@extends('layouts.main')

@section('title', 'Data kategori Barang')

@section('content')

<div class="card-header d-flex justify-content-between">
    <h5 class="card-title mb-0">Data kategori Barang</h5>
    <div>
        <a href="{{ route('kategori-barang.create') }}" class="btn btn-primary mb-3"><i class="fas fa-circle-plus"></i> Tambah Kategori</a>
    </div>
</div>
<div class="card-body">
    @include('includes.alert')

    <div class="table-responsive">
        <table class="table table-striped" id="table-2">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kategoriBarang as $kategori)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kategori->nama_kategori }}</td>
                    <td>
                        <a href="{{ route('kategori-barang.edit', $kategori->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <button type="submit" class="btn btn-danger btn-sm delete-btn" data-id="{{ $kategori->id }}"><i class="fas fa-trash-alt"></i>
                        </button>
                        <form id="delete-form-{{ $kategori->id }}" action="{{ route('kategori-barang.destroy', $kategori->id) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
