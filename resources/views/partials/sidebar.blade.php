<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="{{ route('dashboard') }}">
            <span class="align-middle fw-bold fs-1">{{ config('app.name') }}</span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('dashboard') }}">
                    <i class="align-middle fas fa-home"></i> <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-header">
                Master Data
            </li>

            <li class="sidebar-item {{ request()->routeIs('kategori-barang.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('kategori-barang.index') }}">
                    <i class="align-middle fas fa-shapes"></i> <span class="align-middle">Kategori Barang</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('barang.index') }}">
                    <i class="align-middle fas fa-box"></i> <span class="align-middle">Data Barang</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('pemasok.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('pemasok.index') }}">
                    <i class="align-middle fas fa-address-book"></i> <span class="align-middle">Pemasok</span>
                </a>
            </li>
{{-- 
            <li class="sidebar-header">
                Batch
            </li>

            <li class="sidebar-item {{ request()->routeIs('barang-batch.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('barang-batch.index') }}">
                    <i class="align-middle fas fa-cubes"></i> <span class="align-middle">Barang Batch</span>
                </a>
            </li> --}}

            <li class="sidebar-header">
                Transaksi
            </li>

            <li class="sidebar-item {{ request()->routeIs('barang-masuk.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('barang-masuk.index') }}">
                    <i class="align-middle fas fa-arrow-right"></i> <span class="align-middle">Barang Masuk</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('barang-keluar.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('barang-keluar.index') }}">
                    <i class="align-middle fas fa-arrow-left"></i> <span class="align-middle">Barang Keluar</span>
                </a>
            </li>

            <li class="sidebar-header">
                Laporan
            </li>

            <li class="sidebar-item {{ request()->routeIs('laporan.barang-masuk.index') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('laporan.barang-masuk.index') }}">
                    <i class="align-middle fas fa-arrow-right"></i> <span class="align-middle">Barang Masuk</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('laporan.barang-keluar.index') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('laporan.barang-keluar.index') }}">
                    <i class="align-middle fas fa-arrow-left"></i> <span class="align-middle">Barang Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
