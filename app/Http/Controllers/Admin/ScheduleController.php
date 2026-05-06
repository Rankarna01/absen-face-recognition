<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);

        $employees = User::where('role', 'pegawai')->orderBy('name')->get();
        $shifts = Shift::all();
        
        $schedules = Schedule::with(['user', 'shift'])
                             ->whereMonth('tanggal', $bulan)
                             ->whereYear('tanggal', $tahun)
                             ->get();

        // PERBAIKAN: Ambil SEMUA data libur agar saat kalender di-next/prev bulan, 
        // tanggal merahnya tetap muncul tanpa harus refresh halaman.
        $holidays = \App\Models\Holiday::all();

        return view('admin.schedule.index', compact('employees', 'shifts', 'schedules', 'periode', 'holidays'));
    }

    // Fungsi Simpan Master Shift
    public function storeShift(Request $request)
    {
        $request->validate([
            'nama_shift' => 'required',
            'warna' => 'required'
        ]);

        Shift::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Shift berhasil ditambahkan!']);
    }

    // Fungsi Assign Jadwal ke Pegawai (Bisa lebih dari 1 hari)
    public function assignSchedule(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_libur' => 'nullable',
            'keterangan_libur' => 'nullable|string',
            'shift_id' => 'required_without:is_libur', // Shift wajib jika bukan hari libur
        ]);

        try {
            $startDate = Carbon::parse($request->tanggal_mulai);
            $endDate = Carbon::parse($request->tanggal_selesai);
            
            // Tangkap nilai boolean is_libur dengan aman
            $isLibur = $request->has('is_libur') && ($request->is_libur == 'true' || $request->is_libur == 'on' || $request->is_libur == 1);

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                Schedule::updateOrCreate(
                    [
                        'user_id' => $request->user_id,
                        'tanggal' => $date->toDateString(),
                    ],
                    [
                        // Jika libur, kosongkan shift_id (null)
                        'shift_id' => $isLibur ? null : $request->shift_id,
                        'is_libur' => $isLibur,
                        'keterangan_libur' => $isLibur ? $request->keterangan_libur : null
                    ]
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil di-assign!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Fungsi Hapus Jadwal (Saat event di kalender diklik)
    public function destroy($id)
    {
        try {
            Schedule::findOrFail($id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus jadwal.'], 500);
        }
    }
}