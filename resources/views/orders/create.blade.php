@extends('layouts.app')

@section('title', 'Kasir / Order Baru - Sajian Marinasi')
@section('header_title', 'Kasir Penjualan & Order Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <!-- Top Header -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Formulir Transaksi Kasir</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Pilih produk marinasi, kuantitas, dan status pembayaran.</p>
        </div>
        <a href="{{ route('orders.index') }}" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors">
            &larr; Kembali
        </a>
    </div>

    @if($products->isEmpty())
        <!-- Empty State When No Products Exist -->
        <div class="bg-white rounded-lg border border-zinc-200/80 p-8 text-center space-y-3 shadow-2xs">
            <svg class="w-10 h-10 mx-auto text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-zinc-800">Katalog Produk Masih Kosong</h3>
                <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">
                    Anda belum mendaftarkan produk marinasi apapun. Harap tambahkan produk terlebih dahulu sebelum dapat membuat pesanan.
                </p>
            </div>
            <div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-2 rounded-md transition-colors">
                    <span>Ke Halaman Stok & Produk &rarr;</span>
                </a>
            </div>
        </div>
    @else
        <!-- Main Order Form with Alpine.js -->
        <div x-data="{
            productsList: @js($products),
            items: [
                { product_id: '{{ $products->first()->id }}', quantity: 1, unit_price: {{ $products->first()->selling_price }}, stock: {{ $products->first()->stock }}, subtotal: {{ $products->first()->selling_price }} }
            ],
            addItem() {
                const defaultProd = this.productsList.length > 0 ? this.productsList[0] : null;
                this.items.push({
                    product_id: defaultProd ? defaultProd.id : '',
                    quantity: 1,
                    unit_price: defaultProd ? defaultProd.selling_price : 0,
                    stock: defaultProd ? defaultProd.stock : 0,
                    subtotal: defaultProd ? defaultProd.selling_price : 0
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            updateItem(index) {
                const row = this.items[index];
                const selectedProd = this.productsList.find(p => p.id == row.product_id);
                if (selectedProd) {
                    row.unit_price = selectedProd.selling_price;
                    row.stock = selectedProd.stock;
                    row.subtotal = row.quantity * row.unit_price;
                } else {
                    row.unit_price = 0;
                    row.stock = 0;
                    row.subtotal = 0;
                }
            },
            get grandTotal() {
                return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            },
            get totalQty() {
                return this.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
            }
        }">
            <form action="{{ route('orders.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Left 2 Cols: Customer & Product Items -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- 1. Customer Information Card -->
                        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs space-y-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-2">
                                Data Pelanggan & Waktu Pesanan
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block font-medium text-zinc-700 mb-1">Nama Customer *</label>
                                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}" placeholder="Contoh: Ibu Rina / Warung Berkah" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium">
                                </div>

                                <div>
                                    <label class="block font-medium text-zinc-700 mb-1">No. WhatsApp / HP</label>
                                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Contoh: 081234567890" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block font-medium text-zinc-700 mb-1">Tanggal Transaksi *</label>
                                    <input type="date" name="order_date" required value="{{ old('order_date', date('Y-m-d')) }}" class="w-full p-2 rounded-md border border-zinc-300 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                                </div>

                                <div>
                                    <label class="block font-medium text-zinc-700 mb-1">Catatan Tambahan (Opsional)</label>
                                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Paket bumbu ekstra" class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Product Items Selector Card -->
                        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-2">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                                    Daftar Menu yang Dipesan
                                </h3>
                                <button type="button" @click="addItem()" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2.5 py-1 rounded-md border border-zinc-300 transition-colors flex items-center space-x-1">
                                    <span>+ Tambah Baris Menu</span>
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="p-3 rounded-md bg-zinc-50 border border-zinc-200 flex flex-col sm:flex-row items-center gap-2.5 text-xs">
                                        
                                        <!-- Product Select -->
                                        <div class="flex-1 w-full">
                                            <label class="block font-medium text-zinc-500 text-[10px] uppercase mb-0.5">Produk</label>
                                            <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="updateItem(index)" required class="w-full p-1.5 rounded-md border border-zinc-300 bg-white focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium text-zinc-800">
                                                <template x-for="p in productsList" :key="p.id">
                                                    <option :value="p.id" x-text="p.name + ' (Stok: ' + p.stock + ' pcs) - Rp ' + parseInt(p.selling_price).toLocaleString('id-ID')"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="w-20 shrink-0">
                                            <label class="block font-medium text-zinc-500 text-[10px] uppercase mb-0.5">Jumlah</label>
                                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" @input="updateItem(index)" min="1" required class="w-full p-1.5 rounded-md border border-zinc-300 bg-white font-semibold text-center tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="w-28 shrink-0 text-right">
                                            <span class="block font-medium text-zinc-500 text-[10px] uppercase mb-0.5">Subtotal</span>
                                            <div class="font-semibold text-xs text-zinc-900 mt-1 tabular-nums" x-text="'Rp ' + (item.subtotal || 0).toLocaleString('id-ID')"></div>
                                        </div>

                                        <!-- Delete Row -->
                                        <div class="shrink-0 pt-3 sm:pt-3">
                                            <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="p-1 text-zinc-400 hover:text-rose-600 disabled:opacity-20 disabled:cursor-not-allowed">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Right 1 Col: Payment & Checkout Summary -->
                    <div class="space-y-5">

                        <!-- Payment Options Card -->
                        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs space-y-3 text-xs">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700 border-b border-zinc-100 pb-2">
                                Status & Metode Bayar
                            </h3>

                            <div>
                                <label class="block font-medium text-zinc-700 mb-1.5">Status Pembayaran *</label>
                                <div class="space-y-2">
                                    <label class="flex items-start space-x-2.5 p-2.5 rounded-md border border-zinc-200 cursor-pointer hover:bg-zinc-50 transition-colors">
                                        <input type="radio" name="payment_status" value="paid" checked class="mt-0.5 text-zinc-900 focus:ring-zinc-900">
                                        <div>
                                            <span class="font-medium text-zinc-900 block">LUNAS (Paid)</span>
                                            <span class="text-[11px] text-zinc-400">Stok produk langsung dipotong saat transaksi disimpan.</span>
                                        </div>
                                    </label>

                                    <label class="flex items-start space-x-2.5 p-2.5 rounded-md border border-zinc-200 cursor-pointer hover:bg-zinc-50 transition-colors">
                                        <input type="radio" name="payment_status" value="unpaid" class="mt-0.5 text-zinc-900 focus:ring-zinc-900">
                                        <div>
                                            <span class="font-medium text-zinc-900 block">BELUM LUNAS (Pending)</span>
                                            <span class="text-[11px] text-zinc-400">Stok baru dipotong saat admin menekan tombol Lunaskan.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-zinc-700 mb-1">Metode Pembayaran *</label>
                                <select name="payment_method" required class="w-full p-2 rounded-md border border-zinc-300 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden font-medium">
                                    <option value="qris">QRIS (Gopay / ShopeePay / BCA)</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="cash">Tunai (Cash / Bayar di Tempat)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Grand Total & Submit Card -->
                        <div class="bg-zinc-950 text-white rounded-lg p-4 border border-zinc-800 shadow-2xs space-y-3">
                            <h3 class="text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                                Ringkasan Tagihan
                            </h3>

                            <div class="space-y-1.5 text-xs border-b border-zinc-800 pb-2.5">
                                <div class="flex justify-between text-zinc-300">
                                    <span>Variasi Produk:</span>
                                    <span class="font-medium tabular-nums" x-text="items.length + ' Item'"></span>
                                </div>
                                <div class="flex justify-between text-zinc-300">
                                    <span>Total Kuantitas:</span>
                                    <span class="font-medium tabular-nums" x-text="totalQty + ' pcs'"></span>
                                </div>
                            </div>

                            <div class="pt-0.5">
                                <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">Grand Total:</span>
                                <div class="text-2xl font-semibold text-white tabular-nums mt-0.5" x-text="'Rp ' + (grandTotal || 0).toLocaleString('id-ID')"></div>
                            </div>

                            <button type="submit" class="w-full py-2 px-3 bg-white hover:bg-zinc-100 text-zinc-900 font-medium text-xs rounded-md transition-colors flex items-center justify-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Transaksi</span>
                            </button>
                        </div>

                    </div>

                </div>

            </form>
        </div>
    @endif

</div>
@endsection
