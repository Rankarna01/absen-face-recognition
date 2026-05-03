<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $divisions = Division::withCount('positions')->latest()->get();
        $positions = Position::with('division')->latest()->get();
        return view('admin.department.index', compact('divisions', 'positions'));
    }

    // --- FUNGSI DIVISI ---
    public function storeDivision(Request $request)
    {
        $request->validate(['nama_divisi' => 'required|string|max:255']);
        Division::create(['nama_divisi' => $request->nama_divisi]);
        return response()->json(['status' => 'success', 'message' => 'Divisi berhasil ditambahkan!']);
    }

    public function destroyDivision($id)
    {
        Division::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Divisi & jabatannya berhasil dihapus!']);
    }

    // --- FUNGSI JABATAN ---
    public function storePosition(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'nama_jabatan' => 'required|string|max:255'
        ]);
        Position::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Jabatan berhasil ditambahkan!']);
    }

    public function editPosition($id)
    {
        return response()->json(Position::findOrFail($id));
    }

    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'nama_jabatan' => 'required|string|max:255'
        ]);
        Position::findOrFail($id)->update($request->all());
        return response()->json(['status' => 'success', 'message' => 'Jabatan berhasil diupdate!']);
    }

    public function destroyPosition($id)
    {
        Position::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Jabatan berhasil dihapus!']);
    }
}