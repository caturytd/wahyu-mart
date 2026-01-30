@extends('layouts.main')

@section('title', 'Barang Masuk')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
         <h5 class="card-title mb-0"><span style="font-size: 0.9rem">Transaksi</span> <br>Barang Masuk</h5>
        <div>
            <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary mb-3"><i class="fas fa-circle-plus"></i> Transaksi Baru</a>
        </div>
    </div>

    <div class="card-body">
        @include('includes.alert')
        <form method="GET" action="{{ route('barang-masuk.index') }}">
            <div class="row mb-3">
                <div class="col-md-3">
                    <select name="pemasok" class="form-control mb-3">
                        <option value="">-- Filter Pemasok --</option>
                        @foreach ($pemasoks as $pemasok)
                            <option value="{{ $pemasok->id }}" {{ request('pemasok') == $pemasok->id ? 'selected' : '' }}>
                                {{ $pemasok->nama_pemasok }}
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
                        <th>No Transaksi</th>
                        <th>Tanggal Masuk</th>
                        <th>Pemasok</th>
                        <th>Qty Total</th>
                        <th>Total Harga</th>
                        {{-- <th>Status</th> --}}
                        <th>Diterima</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangMasuks as $index => $bm)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('barang-masuk.show', $bm->id) }}">{{ $bm->no_transaksi }}</a>
                                
                            </td>
                            <td>{{ \Carbon\Carbon::parse($bm->tgl_masuk)->translatedFormat('j F Y') }}</td>
                            <td>{{ $bm->pemasok->nama_pemasok ?? '-' }}</td>
                            <td>{{ $bm->total_qty }}</td>
                            <td>Rp{{ number_format($bm->total_harga, 0, ',', '.') }}</td>
                            {{-- <td>
                                @php
                                $statusClass = match($bm->status) {
                                    'received' => 'success',
                                    'pending' => 'warning',
                                    default => 'danger'
                                };

                                $statusLabel = match($bm->status) {
                                    'received' => 'Diterima',
                                    'pending' => 'Pending',
                                    'canceled' => 'Dibatalkan',
                                    default => ucfirst($bm->status)
                                };
                            @endphp

                            <span class="badge bg-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            </td> --}}
                            <td>{{ $bm->received_by ?? '-' }}</td>
                            <td>
                                <a href="{{ route('barang-masuk.show', $bm->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                @if(auth()->user()->role === 'superadmin')
                                <a href="{{ route('barang-masuk.edit', $bm->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <button type="submit" class="btn btn-danger btn-sm delete-btn" data-id="{{ $bm->id }}"><i class="fas fa-trash-alt"></i>
                                </button>
                                <form id="delete-form-{{ $bm->id }}" action="{{ route('barang-masuk.destroy', $bm->id) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data barang masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
