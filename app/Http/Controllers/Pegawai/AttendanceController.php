<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('employee');
        
        // Cek apakah wajah sudah didaftarkan Admin
        if (!$user->employee || !$user->employee->face_descriptor) {
            return redirect()->route('pegawai.beranda')->with('error', 'Data wajah Anda belum didaftarkan oleh HRD. Silakan hubungi Admin.');
        }

        $today = Carbon::today()->toDateString();
        $absenHariIni = Attendance::where('user_id', $user->id)->whereDate('tanggal', $today)->first();

        // Jika sudah absen pulang, tidak perlu absen lagi
        if ($absenHariIni && $absenHariIni->jam_pulang != null) {
            return redirect()->route('pegawai.beranda')->with('success', 'Anda sudah menyelesaikan absensi untuk hari ini.');
        }

        // Tentukan jenis absen (Masuk / Pulang)
        $jenisAbsen = $absenHariIni ? 'pulang' : 'masuk';

        return view('pegawai.attendance.index', compact('user', 'absenHariIni', 'jenisAbsen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required' // Ini adalah foto base64 dari canvas
        ]);

        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $timeNow = $now->toTimeString();
        
        $setting = Setting::first();
        $absenHariIni = Attendance::where('user_id', $user->id)->whereDate('tanggal', $today)->first();

        try {
            // 1. Proses Gambar (Decode Base64 ke File PNG)
            $image_parts = explode(";base64,", $request->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            $fileName = 'absen_' . $user->id . '_' . time() . '.png';
            Storage::disk('public')->put('absensi/' . $fileName, $image_base64);

            // 2. Logic Absen Masuk
            if (!$absenHariIni) {
                // Hitung batas telat (Jam Masuk + Toleransi)
                $jamMasukLimit = Carbon::parse($setting->default_jam_masuk)->addMinutes($setting->toleransi_keterlambatan);
                
                // Tentukan status kehadiran
                $statusKehadiran = $now->greaterThan($jamMasukLimit) ? 'terlambat' : 'hadir';

                Attendance::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'jam_masuk' => $timeNow,
                    'status_kehadiran' => $statusKehadiran,
                    'foto_masuk' => 'absensi/' . $fileName
                ]);

                $msg = $statusKehadiran == 'terlambat' ? 'Absen masuk berhasil, namun Anda Terlambat.' : 'Absen masuk berhasil. Tepat Waktu!';
                return response()->json(['status' => 'success', 'message' => $msg]);
            } 
            
            // 3. Logic Absen Pulang
            else {
                $absenHariIni->update([
                    'jam_pulang' => $timeNow,
                    'foto_pulang' => 'absensi/' . $fileName
                ]);

                return response()->json(['status' => 'success', 'message' => 'Absen pulang berhasil. Hati-hati di jalan!']);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses absensi: ' . $e->getMessage()], 500);
        }
    }
}