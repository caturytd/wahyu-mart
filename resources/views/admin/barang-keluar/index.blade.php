@extends('layouts.main')

@section('title', 'Data Barang Keluar')

@section('content')

<div class="card-header d-flex justify-content-between">
    <h5 class="card-title mb-0"><span style="font-size: 0.9rem">Transaksi</span> <br>Barang Keluar</h5>
    <div>
        <a href="{{ route('barang-keluar.create') }}" class="btn btn-primary mb-3"><i class="fas fa-circle-plus"></i> Transaksi Baru</a>
    </div>
</div>
<div class="card-body">
    @include('includes.alert')

    <div class="table-responsive">
        <table class="table table-striped" id="table-2">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal Keluar</th>
                    <th>Total Qty</th>
                    <th>Total Harga</th>
                    @if(auth()->user()->role === 'superadmin')
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($barangKeluar as $noTransaksi => $keluars)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('barang-keluar.show', $keluars->first()->id) }}">
                            {{ $noTransaksi }}
                        </a>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($keluars->first()->tgl_keluar)->translatedFormat('j F Y') }}</td>
                    <td>{{ $keluars->sum('total_qty') }}</td>
                    <td>Rp. {{ number_format($keluars->sum('total_nilai'), 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('barang-keluar.show', $keluars->first()->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('barang-keluar.edit', $keluars->first()->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <button type="submit" class="btn btn-danger btn-sm delete-btn" data-id="{{ $keluars->first()->id }}"><i class="fas fa-trash-alt"></i></button>
                        <form id="delete-form-{{ $keluars->first()->id }}" action="{{ route('barang-keluar.destroy', $keluars->first()->id) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data barang keluar.</td>
                    </tr>
            @endforelse
            
            </tbody>
        </table>
    </div>
</div>

@endsection
