
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="{{ asset('assets/img/icons/icon-48x48.png') }}" />


	<title>Dashboard | {{ config('app.name') }}</title>

    @include('includes.style')
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <div class="main flex-grow-1">
            @include('partials.navbar')
            <main class="content p-5">
                <div class="container-fluid">
                    <h3 class="card-title ms-2.5 mb-1 fs-3"><span class="fw-normal fs-6">Home</span> <br>Dashboard</h4>
                    <div class="row">
                        <!-- Servis -->
                        <div class="col-xl-4 mb-4">
                            <div class="card shadow h-65 py-2">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-primary text-white d-flex justify-content-center align-items-center rounded p-3 me-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-layer-group fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase fw-bold fs-5">Barang</div>
                                        <div class="h5 fw-bold text-gray-800 mt-2">{{ $barangc ?? '1x' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Mekanik -->
                        <div class="col-xl-4 mb-4">
                            <div class="card border-start-success shadow-sm h-65 py-2">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-success text-white d-flex justify-content-center align-items-center rounded p-3 me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-box-arrow-in-right text-gray-300 fw-bold" style="font-size: 2.2rem"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase fw-bold fs-5">Barang Masuk</div>
                                        <div class="h5 fw-bold text-gray-800 mt-2">{{ $barangMasukc ?? '1x' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Pemasukan Harian -->
                        <div class="col-xl-4 mb-4">
                            <div class="card border-start-success shadow h-65 py-2">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-info text-white d-flex justify-content-center align-items-center rounded p-3 me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-box-arrow-in-left text-gray-300 fw-bold" style="font-size: 2.2rem"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase fw-bold fs-5">Barang Keluar</div>
                                        <div class="h5 fw-bold text-gray-800 mt-2">{{ $barangKeluarc ?? '1x' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-start">
                        <!-- Barang Masuk Terakhir -->
                        <div class="col-md-4">
                            <div class="card shadow py-2 border">
                                <div class="card-header text-capitalize fw-bold border-secondary" style="font-size: 15.5px">
                                    Barang Masuk Terakhir
                                </div>
                                <div class="card-body">
                                    @if($barangMasuk->count() > 0)
                                        @foreach($barangMasuk as $masuk)
                                            <div class="border-bottom pb-2 mb-2">
                                                <strong>#{{ $masuk->no_transaksi }}</strong> <br>
                                                📅 {{ \Carbon\Carbon::parse($masuk->tgl_masuk)->format('d M Y') }} <br>
                                                📦 {{ $masuk->nama_barang }} ({{ $masuk->kode_barang }}) <br>
                                                ➡️ {{ $masuk->qty }}
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Tidak ada data barang masuk.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    
                        <!-- Barang Keluar Terakhir -->
                        <div class="col-md-4">
                            <div class="card shadow py-2 border">
                                <div class="card-header text-capitalize border-secondary fw-bold" style="font-size: 15.5px">
                                    Barang Keluar Terakhir
                                </div>
                                <div class="card-body">
                                    @if($barangKeluar->count() > 0)
                                        @foreach($barangKeluar as $keluar)
                                            <div class="border-bottom pb-2 mb-2">
                                                <strong>#{{ $keluar->no_transaksi }}</strong> <br>
                                                📅 {{\Carbon\Carbon::parse($keluar->tgl_keluar)->format(' d M Y')  }} <br>
                                                📦 {{ $keluar->nama_barang }} ({{ $keluar->kode_barang }}) <br>
                                                ⬅️ {{ $keluar->qty }}
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Tidak ada data barang keluar.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    
                        <!-- Stok Hampir Habis -->
                        <div class="col-md-4">
                            <div class="card shadow py-2 border">
                                <div class="card-header text-capitalize border-secondary fw-bold" style="font-size: 15.5px">
                                    Stok Hampir Habis
                                </div>
                                <div class="card-body">
                                    @forelse ($barangHampirHabis as $barang)
                                    <div class="border-bottom d-flex justify-content-between pb-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="text-warning fas fa-info-circle me-2"></i> {{ $barang->nama_barang }} ({{ $barang->kode_barang }})
                                        </div>
                                        <span class="badge bg-danger">Sisa Stok: {{ $barang->stok }}</span>
                                    </div> 
                                    @empty
                                        <p class="text-muted">Tidak ada stok barang hampir habis.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </main>
        </div>
    </div>

    @include('includes.script')

</body>

</html>