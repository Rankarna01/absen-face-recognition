<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        
        $payrolls = Payroll::with(['user.employee.position'])
                           ->where('periode', $periode)
                           ->latest()
                           ->get();
                           
        $employees = User::where('role', 'pegawai')->orderBy('name')->get();

        return view('admin.payroll.index', compact('payrolls', 'employees', 'periode'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'periode' => 'required|date_format:Y-m',
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
        ]);

        try {
            $year = substr($request->periode, 0, 4);
            $month = substr($request->periode, 5, 2);

            $setting = Setting::first();
            $dendaPerTelat = $setting->nominal_potongan_telat ?? 0;
            $dendaPerAlfa = $setting->nominal_potongan_alfa ?? 0;

            // 1. Jumlah Telat
            $jumlahTelat = Attendance::where('user_id', $request->user_id)
                                     ->whereYear('tanggal', $year)
                                     ->whereMonth('tanggal', $month)
                                     ->where('status_kehadiran', 'terlambat')
                                     ->count();

            // 2. LOGIKA ALFA TERBARU (Terhubung ke Master Libur)
            // Hitung berapa hari pegawai ini dijadwalkan kerja
            $hariKerjaWajib = \App\Models\Schedule::where('user_id', $request->user_id)
                                        ->whereYear('tanggal', $year)
                                        ->whereMonth('tanggal', $month)
                                        ->count();

            // Hitung berapa hari libur di bulan itu dari tabel Holidays (Master Libur)
            $jumlahLibur = \App\Models\Holiday::whereYear('tanggal', $year)
                                        ->whereMonth('tanggal', $month)
                                        ->count();

            // Hari kerja efektif = jadwal assign dikurangi hari libur global
            $hariKerjaEfektif = $hariKerjaWajib - $jumlahLibur;
            if($hariKerjaEfektif < 0) {
                $hariKerjaEfektif = 0;
            }

            // Hitung berapa kali dia absen secara sah
            $kehadiranSah = Attendance::where('user_id', $request->user_id)
                                      ->whereYear('tanggal', $year)
                                      ->whereMonth('tanggal', $month)
                                      ->whereIn('status_kehadiran', ['hadir', 'terlambat', 'izin', 'cuti'])
                                      ->count();

            // Alfa = Hari Kerja Efektif - Kehadiran Sah.
            $jumlahAlfa = $hariKerjaEfektif - $kehadiranSah;
            if ($jumlahAlfa < 0) {
                $jumlahAlfa = 0;
            }

            // 3. Rumus Total Potongan
            $potonganTelat = $jumlahTelat * $dendaPerTelat;
            $potonganAlfa = $jumlahAlfa * $dendaPerAlfa;

            // 4. Hitung Total Gaji Bersih
            $totalPemasukan = $request->gaji_pokok + $request->tunjangan + $request->bonus;
            $totalBersih = $totalPemasukan - ($potonganTelat + $potonganAlfa);

            if ($totalBersih < 0) {
                $totalBersih = 0; 
            }

            // 5. Simpan ke database
            Payroll::updateOrCreate(
                ['user_id' => $request->user_id, 'periode' => $request->periode],
                [
                    'gaji_pokok' => $request->gaji_pokok,
                    'tunjangan' => $request->tunjangan,
                    'bonus' => $request->bonus,
                    'jumlah_telat' => $jumlahTelat,
                    'potongan_telat' => $potonganTelat,
                    'jumlah_alfa' => $jumlahAlfa,
                    'potongan_alfa' => $potonganAlfa,
                    'total_bersih' => $totalBersih,
                    'status' => 'draft'
                ]
            );

            return response()->json(['status' => 'success', 'message' => 'Gaji di-generate! Otomatis memotong alfa tanpa menghitung hari libur.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function markPaid($id)
    {
        Payroll::findOrFail($id)->update(['status' => 'dibayar']);
        return response()->json(['status' => 'success', 'message' => 'Status gaji diubah menjadi Dibayar!']);
    }

    public function destroy($id)
    {
        Payroll::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Data payroll berhasil dihapus!']);
    }

    public function printSlip($id)
    {
        $payroll = Payroll::with(['user.employee.position', 'user.employee.division'])->findOrFail($id);
        return view('admin.payroll.print', compact('payroll'));
    }
}