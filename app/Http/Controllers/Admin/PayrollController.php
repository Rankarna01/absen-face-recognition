<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        // Set default filter periode ke bulan ini
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        
        $payrolls = Payroll::with(['user.employee.position'])
                           ->where('periode', $periode)
                           ->latest()
                           ->get();
                           
        $employees = User::where('role', 'pegawai')->orderBy('name')->get();

        return view('admin.payroll.index', compact('payrolls', 'employees', 'periode'));
    }

    // Fungsi Generate Gaji (Otomatis hitung telat)
    public function generate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'periode' => 'required|date_format:Y-m', // Format input type="month"
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
        ]);

        try {
            // 1. Ekstrak Bulan dan Tahun dari Periode
            $year = substr($request->periode, 0, 4);
            $month = substr($request->periode, 5, 2);

            // 2. Hitung jumlah telat dari tabel absensi di bulan tersebut
            $jumlahTelat = Attendance::where('user_id', $request->user_id)
                                     ->whereYear('tanggal', $year)
                                     ->whereMonth('tanggal', $month)
                                     ->where('status_kehadiran', 'terlambat')
                                     ->count();

            // 3. Rumus Denda Telat (Misal: Rp 50.000 per hari telat)
            $dendaPerTelat = 50000;
            $potonganTelat = $jumlahTelat * $dendaPerTelat;

            // 4. Hitung Total Gaji Bersih
            $totalBersih = ($request->gaji_pokok + $request->tunjangan + $request->bonus) - $potonganTelat;

            // 5. Simpan ke database (Update jika periode & user sama, Create jika belum ada)
            Payroll::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'periode' => $request->periode
                ],
                [
                    'gaji_pokok' => $request->gaji_pokok,
                    'tunjangan' => $request->tunjangan,
                    'bonus' => $request->bonus,
                    'jumlah_telat' => $jumlahTelat,
                    'potongan_telat' => $potonganTelat,
                    'total_bersih' => $totalBersih,
                    'status' => 'draft' // Default status
                ]
            );

            return response()->json(['status' => 'success', 'message' => 'Gaji berhasil di-generate dan dihitung!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Fungsi Ubah Status jadi Dibayar
    public function markPaid($id)
    {
        Payroll::findOrFail($id)->update(['status' => 'dibayar']);
        return response()->json(['status' => 'success', 'message' => 'Status gaji diubah menjadi Dibayar!']);
    }

    // Fungsi Hapus
    public function destroy($id)
    {
        Payroll::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Data payroll berhasil dihapus!']);
    }

    // Halaman Print PDF/Slip
    public function printSlip($id)
    {
        $payroll = Payroll::with(['user.employee.position', 'user.employee.division'])->findOrFail($id);
        return view('admin.payroll.print', compact('payroll'));
    }
}