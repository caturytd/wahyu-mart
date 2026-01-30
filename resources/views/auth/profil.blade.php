@extends('layouts.main')

@section('title', 'Ganti Password')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Ganti Password</h5>
    </div>

    <div class="card-body">
        @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible show fade">
            <div class="alert-body">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <span></span>
                </button>
                {{ $message }}
            </div>
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Password Lama</label>
                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                @error('new_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
