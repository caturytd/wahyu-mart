@extends('layouts.main')

@section('title', 'Data Pemasok')

@section('content')

<div class="card-header d-flex justify-content-between">
    <h5 class="card-title mb-0">Data Pemasok</h5>
    <div>

        <a href="{{ route('pemasok.create') }}" class="btn btn-primary mb-3"><i class="fas fa-circle-plus"></i> Tambah Pemasok</a>

    </div>
</div>
<div class="card-body">
  @include('includes.alert')

    <div class="table-responsive">
        <table class="table table-striped" id="table-2">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Alamat</th>
                    @if(auth()->user()->role === 'superadmin')
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($pemasoks as $pemasok)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pemasok->nama_pemasok }}</td>
                    <td>{{ $pemasok->kontak }}</td>
                    <td>{{ $pemasok->alamat }}</td>
                    @if(auth()->user()->role === 'superadmin')
                    <td>

                        <a href="{{ route('pemasok.show', $pemasok->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <button type="submit" class="btn btn-danger btn-sm delete-btn" data-id="{{ $pemasok->id }}"><i class="fas fa-trash-alt"></i>
                        </button>
                        <form id="delete-form-{{ $pemasok->id }}" action="{{ route('pemasok.destroy', $pemasok->id) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection


