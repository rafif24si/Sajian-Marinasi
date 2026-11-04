@extends('layouts.app')

@section('title', 'Katalog Produk & Stok - Sajian Marinasi')
@section('header_title', 'Stok & Katalog Produk Marinasi')

@section('content')
<div x-data="{ 
    addModalOpen: false, 
    editModalOpen: false, 
    restockModalOpen: false,
    editData: { id: '', name: '', sku: '', cost_price: 0, selling_price: 0, min_stock_alert: 5 },
    restockData: { id: '', name: '', current_stock: 0, actionUrl: '' },
    openEdit(prod) {
        this.editData = { ...prod };
        this.editModalOpen = true;
    },
    openRestock(prod) {
        this.restockData = {
            id: prod.id,
            name: prod.name,
            current_stock: prod.stock,
            actionUrl: '{{ url('products') }}/' + prod.id + '/add-stock'
        };
        this.restockModalOpen = true;
    }
}" class="space-y-6">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Katalog Produk & Inventaris</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Kelola HPP, harga jual, margin, batas peringatan stok, dan mutasi keluar masuk stok.</p>
        </div>
        <div>
            <button @click="addModalOpen = true" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-2 rounded-md border border-transparent transition-colors flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                Katalog Menu Marinasi ({{ $products->count() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                    <tr>
                        <th class="py-2.5 px-4">Nama Produk</th>
                        <th class="py-2.5 px-4">SKU</th>
                        <th class="py-2.5 px-4 text-right">HPP (Modal)</th>
                        <th class="py-2.5 px-4 text-right">Harga Jual</th>
                        <th class="py-2.5 px-4 text-right">Margin / Pcs</th>
                        <th class="py-2.5 px-4 text-center">Stok</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($products as $prod)
                        @php
                            $margin = $prod->selling_price - $prod->cost_price;
                            $marginPct = $prod->selling_price > 0 ? round(($margin / $prod->selling_price) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-zinc-900">{{ $prod->name }}</div>
                                <div class="text-[11px] text-zinc-400 mt-0.5">Min. Peringatan: <span class="tabular-nums">{{ $prod->min_stock_alert }}</span> pcs</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-zinc-600">
                                {{ $prod->sku }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-zinc-600">
                                Rp {{ number_format($prod->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-zinc-900">
                                Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums">
                                <span class="font-medium text-emerald-700">Rp {{ number_format($margin, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-zinc-400 block tabular-nums">({{ $marginPct }}%)</span>
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums">
                                <span class="font-semibold text-zinc-900">{{ $prod->stock }}</span>
                                <span class="text-[10px] text-zinc-400 block">pcs</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($prod->stock <= 0)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-rose-50 text-rose-700 border-rose-200">
                                        Habis
                                    </span>
                                @elseif($prod->stock <= $prod->min_stock_alert)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-amber-50 text-amber-700 border-amber-200">
                                        Menipis
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                                        Aman
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                <button type="button" @click="openRestock({{ $prod }})" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 transition-colors">
                                    ± Stok
                                </button>
                                <button type="button" @click="openEdit({{ $prod }})" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 transition-colors">
                                    Edit
                                </button>
                                <form action="{{ route('products.destroy', $prod->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $prod->name }}?')">
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
                            <td colspan="8" class="text-center py-10 text-zinc-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-xs font-medium text-zinc-600">Belum ada produk yang ditambahkan ke katalog.</p>
                                <p class="text-[11px] text-zinc-400 mt-0.5">Klik tombol "Tambah Produk" di atas untuk mendaftarkan menu marinasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Movement History Table -->
    <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                    10 Riwayat Mutasi Stok Terakhir
                </h3>
                <p class="text-[11px] text-zinc-400">Pencatatan otomatis arus stok masuk (produksi/restock) dan keluar (penjualan/koreksi).</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                    <tr>
                        <th class="py-2.5 px-4">Waktu</th>
                        <th class="py-2.5 px-4">Produk</th>
                        <th class="py-2.5 px-4 text-center">Tipe Mutasi</th>
                        <th class="py-2.5 px-4 text-center">Jumlah</th>
                        <th class="py-2.5 px-4">Referensi</th>
                        <th class="py-2.5 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($recentMovements as $mov)
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-2.5 px-4 text-zinc-500 tabular-nums">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-4 font-medium text-zinc-900">{{ $mov->product ? $mov->product->name : '-' }}</td>
                            <td class="py-2.5 px-4 text-center">
                                @if($mov->type === 'in')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                                        MASUK (IN)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-rose-50 text-rose-700 border-rose-200">
                                        KELUAR (OUT)
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-center font-semibold tabular-nums {{ $mov->type === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $mov->type === 'in' ? '+' : '-' }}{{ $mov->quantity }} pcs
                            </td>
                            <td class="py-2.5 px-4 font-mono text-[11px] text-zinc-700">{{ $mov->reference ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-zinc-500">{{ $mov->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-zinc-400">Belum ada riwayat mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Tambah Produk Baru -->
    <div x-cloak x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40">
        <div @click.away="addModalOpen = false" class="bg-white rounded-lg w-full max-w-md p-5 border border-zinc-200 shadow-md space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-zinc-200">
                <h3 class="text-sm font-semibold text-zinc-900">Tambah Produk Baru</h3>
                <button @click="addModalOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm">&times;</button>
            </div>

            <form action="{{ route('products.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Nama Produk *</label>
                    <input type="text" name="name" required placeholder="Contoh: Ayam Marinasi Bawang Putih 500gr" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">SKU / Kode *</label>
                        <input type="text" name="sku" required placeholder="SM-AYM-01" class="w-full p-2 rounded-md border border-zinc-300 font-mono uppercase focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Batas Min. Alert *</label>
                        <input type="number" name="min_stock_alert" value="5" min="0" required class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">HPP / Biaya Modal (Rp) *</label>
                        <input type="number" name="cost_price" required min="0" placeholder="20000" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" name="selling_price" required min="0" placeholder="35000" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums font-semibold focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Stok Awal (pcs) *</label>
                    <input type="number" name="stock" value="0" min="0" required class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    <span class="text-[11px] text-zinc-400 mt-0.5 block">Jika > 0, otomatis dicatat pada mutasi stok awal masuk.</span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-zinc-200">
                    <button type="button" @click="addModalOpen = false" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Produk -->
    <div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40">
        <div @click.away="editModalOpen = false" class="bg-white rounded-lg w-full max-w-md p-5 border border-zinc-200 shadow-md space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-zinc-200">
                <h3 class="text-sm font-semibold text-zinc-900">Edit Data Produk</h3>
                <button @click="editModalOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm">&times;</button>
            </div>

            <form :action="'{{ url('products') }}/' + editData.id" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Nama Produk *</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">SKU / Kode *</label>
                        <input type="text" name="sku" x-model="editData.sku" required class="w-full p-2 rounded-md border border-zinc-300 font-mono uppercase focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Batas Min. Alert *</label>
                        <input type="number" name="min_stock_alert" x-model="editData.min_stock_alert" min="0" required class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">HPP / Biaya Modal (Rp) *</label>
                        <input type="number" name="cost_price" x-model="editData.cost_price" required min="0" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" name="selling_price" x-model="editData.selling_price" required min="0" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums font-semibold focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-zinc-200">
                    <button type="button" @click="editModalOpen = false" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Update / Restock Stok -->
    <div x-cloak x-show="restockModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/40">
        <div @click.away="restockModalOpen = false" class="bg-white rounded-lg w-full max-w-sm p-5 border border-zinc-200 shadow-md space-y-4">
            <div class="flex items-center justify-between pb-2.5 border-b border-zinc-200">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-900">Mutasi / Restock Stok</h3>
                    <p class="text-[11px] text-zinc-500" x-text="restockData.name"></p>
                </div>
                <button @click="restockModalOpen = false" class="text-zinc-400 hover:text-zinc-600 text-sm">&times;</button>
            </div>

            <form :action="restockData.actionUrl" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="p-2.5 rounded-md bg-zinc-50 border border-zinc-200 flex items-center justify-between">
                    <span class="text-zinc-600 font-medium">Stok Saat Ini:</span>
                    <span class="text-xs font-semibold text-zinc-900 tabular-nums" x-text="restockData.current_stock + ' pcs'"></span>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Jenis Mutasi *</label>
                    <select name="type" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium">
                        <option value="in">MASUK (+ Tambah Hasil Produksi / Restock)</option>
                        <option value="out">KELUAR (- Koreksi Stok Rusak / Basi)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Jumlah Unit (pcs) *</label>
                    <input type="number" name="quantity" required min="1" placeholder="Contoh: 10" class="w-full p-2 rounded-md border border-zinc-300 font-semibold tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Referensi</label>
                    <input type="text" name="reference" placeholder="Contoh: Batch Pagi / Koreksi Expired" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan opsional..." class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-zinc-200">
                    <button type="button" @click="restockModalOpen = false" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">Batal</button>
                    <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-1.5 rounded-md border border-transparent transition-colors">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
