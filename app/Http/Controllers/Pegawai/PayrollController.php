<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil filter bulan/tahun (default ke bulan ini)
        $periode = $request->periode ?? Carbon::now()->format('Y-m');

        // Cari data gaji pegawai di bulan tersebut
        $payroll = Payroll::where('user_id', $user->id)
                          ->where('periode', $periode)
                          ->first();

        return view('pegawai.payroll.index', compact('payroll', 'periode'));
    }

    // Fungsi untuk cetak/download PDF (Menggunakan view cetak admin yang sudah kita buat sebelumnya)
    public function print($id)
    {
        // Pastikan pegawai HANYA BISA mencetak slip gajinya sendiri
        $payroll = Payroll::with(['user.employee.position', 'user.employee.division'])
                          ->where('user_id', Auth::id())
                          ->findOrFail($id);
                          
        return view('admin.payroll.print', compact('payroll'));
    }
}