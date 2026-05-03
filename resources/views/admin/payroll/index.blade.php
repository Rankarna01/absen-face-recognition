@extends('layouts.admin')
@section('title', 'Payroll & Penggajian')
@section('header_title', 'Payroll & Slip Gaji')

@section('content')
<div x-data="payrollManager()" class="space-y-6">

    <!-- Header & Filter -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h3 class="font-bold text-xl text-secondary">Data Penggajian</h3>
            <p class="text-sm text-gray-500 mt-1">Generate gaji, hitung potongan otomatis, dan cetak slip.</p>
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <!-- Filter Form -->
            <form action="{{ route('admin.payroll.index') }}" method="GET" class="flex gap-2">
                <input type="month" name="periode" value="{{ $periode }}" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none bg-white text-sm">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Filter</button>
            </form>
            <!-- Tombol Modal Generate -->
            <button @click="openModal()" class="bg-primary hover:bg-primary_hover text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-lg shadow-primary/30 flex items-center gap-2">
                <i class="fa-solid fa-calculator"></i> Generate Gaji
            </button>
        </div>
    </div>

    <!-- Tabel Payroll -->
    <div class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-600">
                        <th class="px-6 py-4 font-semibold">Pegawai</th>
                        <th class="px-6 py-4 font-semibold">Periode</th>
                        <th class="px-6 py-4 font-semibold text-right">Total Bersih</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payrolls as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-secondary">{{ $item->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->user->employee->position->nama_jabatan ?? 'Pegawai' }}</p>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-600">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $item->periode)->translatedFormat('F Y') }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-green-600">
                            Rp {{ number_format($item->total_bersih, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($item->status == 'dibayar')
                                <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-bold uppercase"><i class="fa-solid fa-check mr-1"></i> Dibayar</span>
                            @else
                                <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-xs font-bold uppercase"><i class="fa-regular fa-clock mr-1"></i> Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @if($item->status == 'draft')
                                <button @click="markAsPaid({{ $item->id }})" class="bg-blue-100 hover:bg-blue-500 text-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Tandai Sudah Dibayar">
                                    <i class="fa-solid fa-hand-holding-dollar"></i> Bayar
                                </button>
                                @endif
                                
                                <a href="{{ route('admin.payroll.print', $item->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-600 text-gray-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Cetak Slip">
                                    <i class="fa-solid fa-print"></i> Slip
                                </a>

                                <button @click="deletePayroll({{ $item->id }})" class="bg-red-100 hover:bg-red-500 text-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 text-gray-300 block"></i>
                            Belum ada data gaji untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Generate Payroll -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="isModalOpen" x-transition.opacity @click="closeModal()" class="fixed inset-0 bg-secondary/50 backdrop-blur-sm"></div>
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-base w-full max-w-lg rounded-2xl shadow-xl z-10 overflow-hidden relative">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg"><i class="fa-solid fa-calculator text-primary mr-2"></i> Generate Gaji</h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form @submit.prevent="submitGenerate">
                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 text-blue-700 p-3 rounded-xl text-xs border border-blue-100 flex gap-2">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <p>Potongan keterlambatan (Rp 50.000/hari) akan <b>dihitung otomatis</b> oleh sistem berdasarkan data absensi di bulan yang dipilih.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pegawai</label>
                            <select x-model="form.user_id" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none bg-white">
                                <option value="">-- Pegawai --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->nip }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode Bulan</label>
                            <!-- Default ke bulan yang difilter -->
                            <input type="month" x-model="form.periode" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none bg-white">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok (Rp)</label>
                            <input type="number" x-model="form.gaji_pokok" required min="0" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan (Rp)</label>
                            <input type="number" x-model="form.tunjangan" required min="0" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bonus (Rp)</label>
                            <input type="number" x-model="form.bonus" required min="0" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary_hover rounded-xl transition shadow-lg flex items-center gap-2">
                        <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                        <span>Hitung & Generate</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('payrollManager', () => ({
            isModalOpen: false,
            isLoading: false,
            form: { 
                user_id: '', 
                periode: '{{ $periode }}', // Default ngambil dari filter atas
                gaji_pokok: 3500000, // Pre-filled default Gaji (bisa diedit admin)
                tunjangan: 1200000, 
                bonus: 0 
            },

            openModal() { this.isModalOpen = true; },
            closeModal() { this.isModalOpen = false; },

            submitGenerate() {
                this.isLoading = true;
                $.ajax({
                    url: '/admin/payroll/generate',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', ...this.form },
                    success: (res) => {
                        this.isLoading = false;
                        this.closeModal();
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan' });
                    }
                });
            },

            markAsPaid(id) {
                Swal.fire({
                    title: 'Tandai Sudah Dibayar?',
                    text: "Pastikan gaji sudah ditransfer ke pegawai.",
                    icon: 'question',
                    showCancelButton: true, confirmButtonColor: '#22C55E', confirmButtonText: 'Ya, Tandai Dibayar!'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/admin/payroll/${id}/bayar`, type: 'POST', data: { _token: '{{ csrf_token() }}' },
                            success: (r) => { Toast.fire({ icon: 'success', title: r.message }); setTimeout(() => location.reload(), 1000); }
                        });
                    }
                });
            },

            deletePayroll(id) {
                Swal.fire({
                    title: 'Hapus Data Gaji?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus!'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/admin/payroll/${id}`, type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: (r) => { Toast.fire({ icon: 'success', title: r.message }); setTimeout(() => location.reload(), 1000); }
                        });
                    }
                });
            }
        }));
    });
</script>
@endpush