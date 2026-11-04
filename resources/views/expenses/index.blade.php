@extends('layouts.app')

@section('title', 'Pengeluaran Belanja - Sajian Marinasi')
@section('header_title', 'Pengeluaran Belanja & Operasional')

@section('content')
<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    previewImgOpen: false,
    previewImgUrl: '',
    editData: { id: '', expense_date: '', category: 'bahan_baku', title: '', amount: 0, notes: '', actionUrl: '' },
    openEdit(exp) {
        this.editData = {
            id: exp.id,
            expense_date: exp.expense_date,
            category: exp.category,
            title: exp.title,
            amount: exp.amount,
            notes: exp.notes || '',
            actionUrl: '{{ url('expenses') }}/' + exp.id
        };
        this.editModalOpen = true;
    },
    openReceipt(url) {
        this.previewImgUrl = url;
        this.previewImgOpen = true;
    }
}" class="space-y-5">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Catatan Arus Pengeluaran Kas</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Dokumentasi pembelian bahan baku ayam/bumbu, kemasan packaging, dan operasional.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-2">
                @php
                    $bulanList = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    $selectedMonth = str_pad(request('month', $month ?? date('m')), 2, '0', STR_PAD_LEFT);
                    $selectedYear = request('year', $year ?? date('Y'));
                    $selectedCategory = request('category', $category ?? 'all');
                @endphp

                <select name="category" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 focus:outline-hidden">
                    <option value="all" {{ $selectedCategory == 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="bahan_baku" {{ $selectedCategory == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                    <option value="kemasan" {{ $selectedCategory == 'kemasan' ? 'selected' : '' }}>Kemasan</option>
                    <option value="operasional" {{ $selectedCategory == 'operasional' ? 'selected' : '' }}>Operasional</option>
                    <option value="lainnya" {{ $selectedCategory == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>

                <select name="month" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 focus:outline-hidden">
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>

                <select name="year" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 focus:outline-hidden">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2.5 py-1.5 rounded-md border border-zinc-300 transition-colors">
                    Filter
                </button>
            </form>

            <button @click="addModalOpen = true" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Belanja</span>
            </button>
        </div>
    </div>

    <!-- Category Breakdown 5 Cards (High-Density) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Total -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Total Pengeluaran</span>
            <div class="text-lg font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">{{ $bulanList[$selectedMonth] }} {{ $selectedYear }}</span>
        </div>

        <!-- Bahan Baku -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Bahan Baku</span>
            <div class="text-base font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($categoryTotals['bahan_baku'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Ayam, bebek & bumbu</span>
        </div>

        <!-- Kemasan -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Kemasan</span>
            <div class="text-base font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($categoryTotals['kemasan'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Vacuum pack, box, label</span>
        </div>

        <!-- Operasional -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Operasional</span>
            <div class="text-base font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($categoryTotals['operasional'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Gas LPG, listrik, es batu</span>
        </div>

        <!-- Lainnya -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Lainnya</span>
            <div class="text-base font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($categoryTotals['lainnya'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Biaya tak terduga</span>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                Daftar Transaksi Pengeluaran ({{ $expenses->count() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                    <tr>
                        <th class="py-2.5 px-4">Tanggal</th>
                        <th class="py-2.5 px-4">Kategori</th>
                        <th class="py-2.5 px-4">Keterangan Pengeluaran</th>
                        <th class="py-2.5 px-4 text-right">Nominal (Rp)</th>
                        <th class="py-2.5 px-4 text-center">Nota</th>
                        <th class="py-2.5 px-4">Catatan</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-3 px-4 tabular-nums text-zinc-600">
                                {{ \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                @if($exp->category === 'bahan_baku')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-amber-50 text-amber-700 border-amber-200">
                                        Bahan Baku
                                    </span>
                                @elseif($exp->category === 'kemasan')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-zinc-100 text-zinc-700 border-zinc-200">
                                        Kemasan
                                    </span>
                                @elseif($exp->category === 'operasional')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-zinc-100 text-zinc-700 border-zinc-200">
                                        Operasional
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-zinc-100 text-zinc-600 border-zinc-200">
                                        Lainnya
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-zinc-900">
                                {{ $exp->title }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-rose-600">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($exp->receipt_image)
                                    <button type="button" @click="openReceipt('{{ asset('storage/' . $exp->receipt_image) }}')" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 transition-colors">
                                        Lihat
                                    </button>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-zinc-500 max-w-xs truncate">
                                {{ $exp->notes ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                <button type="button" @click="openEdit({{ $exp }})" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 transition-colors">
                                    Edit
                                </button>
                                <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus catatan pengeluaran {{ $exp->title }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-white hover:bg-rose-50 text-rose-600 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 hover:border-rose-200 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-zinc-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                <p class="text-xs font-medium text-zinc-600">Belum ada data pengeluaran belanja pada periode ini.</p>
                                <p class="text-[11px] text-zinc-400 mt-0.5">Klik tombol "Catat Belanja" di atas untuk menambahkan pengeluaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Tambah Pengeluaran Baru -->
    <div x-cloak x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40">
        <div @click.away="addModalOpen = false" class="bg-white rounded-lg w-full max-w-md p-5 border border-zinc-200 shadow-md space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-zinc-200">
                <h3 class="text-sm font-semibold text-zinc-900">Catat Pengeluaran Baru</h3>
                <button @click="addModalOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm">&times;</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Tanggal *</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Kategori *</label>
                        <select name="category" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium">
                            <option value="bahan_baku">Bahan Baku (Ayam, Bumbu)</option>
                            <option value="kemasan">Kemasan (Box, Plastik, Label)</option>
                            <option value="operasional">Operasional (Gas LPG, Es)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Keterangan / Judul *</label>
                    <input type="text" name="title" required placeholder="Contoh: Belanja Ayam Segar 30 Ekor" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Nominal Biaya (Rp) *</label>
                    <input type="number" name="amount" required min="0" placeholder="450000" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums font-semibold text-rose-600 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Foto Bukti / Nota (Opsional)</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full p-1.5 rounded-md border border-zinc-300 text-zinc-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-zinc-100 file:text-zinc-700 file:text-xs">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Nama supplier, rincian item..." class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-zinc-200">
                    <button type="button" @click="addModalOpen = false" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Pengeluaran -->
    <div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40">
        <div @click.away="editModalOpen = false" class="bg-white rounded-lg w-full max-w-md p-5 border border-zinc-200 shadow-md space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-zinc-200">
                <h3 class="text-sm font-semibold text-zinc-900">Edit Pengeluaran</h3>
                <button @click="editModalOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm">&times;</button>
            </div>

            <form :action="editData.actionUrl" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Tanggal *</label>
                        <input type="date" name="expense_date" x-model="editData.expense_date" required class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Kategori *</label>
                        <select name="category" x-model="editData.category" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium">
                            <option value="bahan_baku">Bahan Baku</option>
                            <option value="kemasan">Kemasan</option>
                            <option value="operasional">Operasional</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Keterangan / Judul *</label>
                    <input type="text" name="title" x-model="editData.title" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Nominal (Rp) *</label>
                    <input type="number" name="amount" x-model="editData.amount" required min="0" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums font-semibold text-rose-600 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Ganti Nota (Opsional)</label>
                    <input type="file" name="receipt_image" accept="image/*" class="w-full p-1.5 rounded-md border border-zinc-300 text-zinc-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-zinc-100 file:text-zinc-700 file:text-xs">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Catatan</label>
                    <textarea name="notes" x-model="editData.notes" rows="2" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-zinc-200">
                    <button type="button" @click="editModalOpen = false" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Preview Nota Belanja -->
    <div x-cloak x-show="previewImgOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60">
        <div @click.away="previewImgOpen = false" class="bg-white rounded-lg w-full max-w-lg p-4 border border-zinc-200 shadow-md space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-zinc-200">
                <h4 class="text-xs font-semibold text-zinc-900">Bukti Nota Pembelian</h4>
                <button @click="previewImgOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm font-semibold">&times;</button>
            </div>
            <div class="max-h-[70vh] overflow-auto flex items-center justify-center p-2 bg-zinc-50 rounded-md">
                <img :src="previewImgUrl" alt="Nota Belanja" class="max-h-[65vh] w-auto rounded border border-zinc-200">
            </div>
        </div>
    </div>

</div>
@endsection
