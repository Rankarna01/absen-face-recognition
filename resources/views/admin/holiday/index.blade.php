@extends('layouts.admin')
@section('title', 'Master Hari Libur')
@section('header_title', 'Master Hari Libur Nasional / Toko')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Form Tambah -->
    <div class="md:col-span-1">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4"><i class="fa-solid fa-calendar-day text-red-500 mr-2"></i> Tambah Hari Libur</h3>
            <form id="formLibur" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" required class="w-full px-4 py-2 border border-gray-200 rounded-xl outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Nama Libur</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Tahun Baru / Toko Tutup" required class="w-full px-4 py-2 border border-gray-200 rounded-xl outline-none focus:border-red-500">
                </div>
                <button type="button" onclick="simpanLibur()" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl font-bold transition">Simpan Libur</button>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="md:col-span-2">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4">Daftar Hari Libur</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm">
                            <th class="p-3 rounded-tl-xl">Tanggal</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-center rounded-tr-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $libur)
                        <tr class="border-b border-gray-50">
                            <td class="p-3 font-semibold text-secondary">{{ \Carbon\Carbon::parse($libur->tanggal)->format('d F Y') }}</td>
                            <td class="p-3 text-gray-600">{{ $libur->keterangan }}</td>
                            <td class="p-3 text-center">
                                <button onclick="hapusLibur({{ $libur->id }})" class="text-red-500 hover:bg-red-50 px-3 py-1 rounded-lg"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center p-4 text-gray-400">Belum ada data libur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function simpanLibur() {
        $.ajax({
            url: '{{ route("admin.holiday.store") }}', type: 'POST', data: $('#formLibur').serialize(),
            success: (res) => { Toast.fire({ icon: 'success', title: res.message }); setTimeout(() => location.reload(), 1000); },
            error: (xhr) => { Toast.fire({ icon: 'error', title: xhr.responseJSON.message || 'Gagal menyimpan' }); }
        });
    }
    function hapusLibur(id) {
        if(confirm('Hapus tanggal libur ini?')) {
            $.ajax({
                url: `/admin/hari-libur/${id}`, type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: (res) => { Toast.fire({ icon: 'success', title: res.message }); setTimeout(() => location.reload(), 1000); }
            });
        }
    }
</script>
@endpush