<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah pengguna sudah login
        if (Auth::check()) {
            // Jika sudah login, alihkan ke halaman yang sesuai, misalnya dashboard
            return redirect()->route('dashboard'); // Ganti dengan route yang sesuai
        }

        // Jika belum login, lanjutkan request
        return $next($request);
    }
}
