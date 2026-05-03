<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil periode dari request, default ke bulan saat ini
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);

        // Ambil data absensi berdasarkan bulan & tahun yang dipilih
        $riwayat = Attendance::where('user_id', $user->id)
                             ->whereMonth('tanggal', $bulan)
                             ->whereYear('tanggal', $tahun)
                             ->orderBy('tanggal', 'desc')
                             ->get();

        return view('pegawai.history.index', compact('riwayat', 'periode'));
    }
}