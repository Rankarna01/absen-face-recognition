<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        // Load relasi employee, division, dan position
        $karyawan = User::with(['employee.division', 'employee.position'])->where('role', 'pegawai')->latest()->get();
        
        // Passing data master divisi dan jabatan untuk select option di Modal
        $divisions = Division::all();
        $positions = Position::all();

        return view('admin.employee.index', compact('karyawan', 'divisions', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nip' => 'required|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'division_id' => 'required|exists:divisions,id',
            'position_id' => 'required|exists:positions,id',
            'password' => 'required|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'nip' => $request->nip,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pegawai'
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('karyawan/foto', 'public');
            }

            $user->employee()->create([
                'division_id' => $request->division_id,
                'position_id' => $request->position_id,
                'foto' => $fotoPath,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Karyawan berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $user = User::with('employee')->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'nip' => 'required|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'division_id' => 'required|exists:divisions,id',
            'position_id' => 'required|exists:positions,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'nip' => $request->nip,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $employeeData = [
                'division_id' => $request->division_id,
                'position_id' => $request->position_id,
            ];

            if ($request->hasFile('foto')) {
                if ($user->employee && $user->employee->foto) {
                    Storage::disk('public')->delete($user->employee->foto);
                }
                $employeeData['foto'] = $request->file('foto')->store('karyawan/foto', 'public');
            }

            $user->employee()->updateOrCreate(['user_id' => $user->id], $employeeData);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data Karyawan berhasil diupdate!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->employee && $user->employee->foto) {
                Storage::disk('public')->delete($user->employee->foto);
            }
            $user->delete();
            return response()->json(['status' => 'success', 'message' => 'Karyawan berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}