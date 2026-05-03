<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['employee.position', 'employee.division']);
        return view('pegawai.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Update Data Email di tabel users
            $user->update(['email' => $request->email]);

            // Update Data Foto di tabel employees
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($user->employee && $user->employee->foto) {
                    Storage::disk('public')->delete($user->employee->foto);
                }

                $fotoPath = $request->file('foto')->store('karyawan/foto', 'public');
                
                // Pastikan relasi employee ada
                if ($user->employee) {
                    $user->employee->update(['foto' => $fotoPath]);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Profil berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui profil: ' . $e->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // Pastikan ada input new_password_confirmation
        ]);

        $user = Auth::user();

        // Cek apakah password lama cocok
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Password lama yang Anda masukkan salah.']]
            ], 422);
        }

        try {
            $user->update(['password' => Hash::make($request->new_password)]);
            return response()->json(['status' => 'success', 'message' => 'Password berhasil diubah!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengubah password.'], 500);
        }
    }
}