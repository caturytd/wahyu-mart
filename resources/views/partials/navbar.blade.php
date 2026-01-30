<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
      <i class="hamburger align-self-center"></i>
    </a>
  
    <div class="navbar-collapse collapse">
      <ul class="navbar-nav navbar-align">
        <li class="nav-item dropdown">
          <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
            <i class="align-middle" data-feather="settings"></i>
          </a>
  
          <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
            <i class="align-middle me-1" data-feather="user"></i> <span class="text-dark">{{ Auth::user()->nama }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="nav-item">
              <a class="nav-link fs-6" href="{{ route('password.change') }}"><i class="fas fa-key align-middle me-2"></i>Ganti Password</a>
          </li>
            {{-- <li class="nav-item">
              <a class="nav-link fs-6" href="{{ route('pengaturan-toko.index') }}"><i class="fas fa-gear align-middle me-2"></i>Pengaturan Toko</a>
          </li> --}}
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="align-middle me-2" data-feather="log-out"></i>
              <span class="align-middle">Keluar</span>
            </a>
          </li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
              @csrf 
            </form>
          </ul>
        </li>
      </ul>
    </div>
  </nav>