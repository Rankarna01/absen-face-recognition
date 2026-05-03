<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default filter ke bulan ini
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);

        // Ambil semua pegawai
        $users = User::where('role', 'pegawai')->with('employee.position')->orderBy('name')->get();

        $laporan = [];
        foreach ($users as $user) {
            // Ambil absensi user di bulan terpilih
            $absensi = Attendance::where('user_id', $user->id)
                                 ->whereYear('tanggal', $year)
                                 ->whereMonth('tanggal', $month)
                                 ->get();

            $laporan[] = [
                'user' => $user,
                'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
                'izin_cuti' => $absensi->whereIn('status_kehadiran', ['izin', 'cuti'])->count(),
                'alfa' => $absensi->where('status_kehadiran', 'alfa')->count(),
                'total_hari' => $absensi->count()
            ];
        }

        // Untuk Tab Keterlambatan, kita urutkan dari yang paling sering telat
        $laporanKeterlambatan = collect($laporan)->sortByDesc('terlambat')->values()->all();

        return view('admin.report.index', compact('laporan', 'laporanKeterlambatan', 'periode'));
    }

    // Export Laporan Absensi ke Excel (CSV)
    public function exportExcel(Request $request)
    {
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        $year = substr($periode, 0, 4);
        $month = substr($periode, 5, 2);
        
        $jenis = $request->jenis ?? 'absensi'; // 'absensi' atau 'keterlambatan'

        $users = User::where('role', 'pegawai')->with('employee.position')->get();
        $fileName = 'Rekap_' . ucfirst($jenis) . '_' . $periode . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($users, $year, $month, $jenis) {
            $file = fopen('php://output', 'w');
            
            if ($jenis == 'absensi') {
                fputcsv($file, ['NIP', 'Nama Pegawai', 'Jabatan', 'Hadir', 'Terlambat', 'Izin/Cuti', 'Alfa', 'Total Data']);
            } else {
                fputcsv($file, ['NIP', 'Nama Pegawai', 'Jabatan', 'Total Keterlambatan (Hari)']);
            }

            // Kumpulkan data agar bisa diurutkan untuk laporan keterlambatan
            $dataExport = [];
            foreach ($users as $user) {
                $absensi = Attendance::where('user_id', $user->id)->whereYear('tanggal', $year)->whereMonth('tanggal', $month)->get();
                $terlambat = $absensi->where('status_kehadiran', 'terlambat')->count();
                
                $dataExport[] = [
                    'nip' => $user->nip,
                    'nama' => $user->name,
                    'jabatan' => $user->employee->position->nama_jabatan ?? '-',
                    'hadir' => $absensi->where('status_kehadiran', 'hadir')->count(),
                    'terlambat' => $terlambat,
                    'izin_cuti' => $absensi->whereIn('status_kehadiran', ['izin', 'cuti'])->count(),
                    'alfa' => $absensi->where('status_kehadiran', 'alfa')->count(),
                    'total' => $absensi->count()
                ];
            }

            if ($jenis == 'keterlambatan') {
                // Urutkan dari telat terbanyak
                usort($dataExport, function($a, $b) { return $b['terlambat'] <=> $a['terlambat']; });
            }

            foreach ($dataExport as $row) {
                if ($jenis == 'absensi') {
                    fputcsv($file, [$row['nip'], $row['nama'], $row['jabatan'], $row['hadir'], $row['terlambat'], $row['izin_cuti'], $row['alfa'], $row['total']]);
                } else {
                    fputcsv($file, [$row['nip'], $row['nama'], $row['jabatan'], $row['terlambat']]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}