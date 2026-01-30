<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
    
        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->intended('dashboard');
        }
    
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }
    

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken(); 

        return redirect('/login');
    }

    public function showChangePasswordForm()
    {
        return view('auth.profil');
    }

    public function changePassword(Request $request)
    {
       $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed',
    ],[
        'current_password.required' => 'Password lama wajib diisi.',
        'new_password.required' => 'Password baru wajib diisi.',
        'new_password.min' => 'Password baru minimal harus terdiri dari 6 karakter.',
        'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
    ]);

    $user = User::find(Auth::guard('web')->user()->id);

         if (!$user || !Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
    }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('password.change')->with('success', 'Password berhasil diubah.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function dashboard()
    {
        // Ambil 5 transaksi barang masuk terbaru
        $barangMasuk = DB::table('barang_masuk_detail')
        ->join('barang_masuk', 'barang_masuk_detail.barang_masuk_id', '=', 'barang_masuk.id')
        ->join('barang', 'barang_masuk_detail.barang_id', '=', 'barang.id')
        ->select('barang_masuk.no_transaksi', 'barang.nama_barang', 'barang.kode_barang', 'barang_masuk_detail.qty', 'barang_masuk.tgl_masuk')
        ->orderBy('barang_masuk.tgl_masuk', 'desc')
        ->limit(5)
        ->get();

        // Ambil 5 transaksi barang keluar terbaru
        $barangKeluar = DB::table('barang_keluar_detail')
        ->join('barang_keluar', 'barang_keluar_detail.barang_keluar_id', '=', 'barang_keluar.id')
        ->join('barang', 'barang_keluar_detail.barang_id', '=', 'barang.id')
        ->select('barang_keluar.no_transaksi', 'barang.nama_barang', 'barang.kode_barang', 'barang_keluar_detail.qty', 'barang_keluar.tgl_keluar')
        ->orderBy('barang_keluar.tgl_keluar', 'desc')
        ->limit(5)
        ->get();

    
        // Ambil semua barang yang stoknya kurang dari atau sama dengan min_stok
        $barangHampirHabis = Barang::whereColumn('stok', '<=', 'min_stok')->get();
        $barangMasukc = BarangMasuk::count();
        $barangKeluarc = BarangKeluar::count();
        $barangc = Barang::count();
    
        return view('admin.dashboard', compact(
            'barangHampirHabis',
            'barangKeluar',
            'barangMasuk',
            'barangMasukc',
            'barangKeluarc',
            'barangc'
        ));
    }
    
}
