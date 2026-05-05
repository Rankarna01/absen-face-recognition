<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        // Gunakan firstOrCreate agar jika database masih kosong, otomatis dibuatkan 1 baris default
        $setting = Setting::firstOrCreate(['id' => 1], [
            'app_name' => 'Family Market',
            'default_jam_masuk' => '08:00',
            'default_jam_pulang' => '17:00',
            'toleransi_keterlambatan' => 15,
            'nominal_potongan_telat' => 10000, // Default denda telat 10rb
            'nominal_potongan_alfa' => 50000,  // Default denda tidak hadir 50rb
            'office_latitude' => '3.595196',
            'office_longitude' => '98.672226',
            'office_radius' => 50
        ]);

        return view('admin.setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::findOrFail(1);

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'default_jam_masuk' => 'required',
            'default_jam_pulang' => 'required',
            'toleransi_keterlambatan' => 'required|numeric|min:0',
            'nominal_potongan_telat' => 'required|numeric|min:0',
            'nominal_potongan_alfa' => 'required|numeric|min:0',
            'office_latitude' => 'required',
            'office_longitude' => 'required',
            'office_radius' => 'required|numeric|min:10',
        ]);

        try {
            $data = $request->except('app_logo');

            // Handle Upload Logo Baru
            if ($request->hasFile('app_logo')) {
                if ($setting->app_logo) {
                    Storage::disk('public')->delete($setting->app_logo);
                }
                $data['app_logo'] = $request->file('app_logo')->store('pengaturan/logo', 'public');
            }

            $setting->update($data);

            return response()->json(['status' => 'success', 'message' => 'Pengaturan sistem berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui pengaturan: ' . $e->getMessage()], 500);
        }
    }
}