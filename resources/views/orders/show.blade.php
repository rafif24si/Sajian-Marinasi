@extends('layouts.app')

@section('title', 'Invoice ' . $order->invoice_number . ' - Sajian Marinasi')
@section('header_title', 'Detail Struk & Invoice')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print bg-white rounded-lg p-3.5 border border-zinc-200/80 shadow-2xs flex items-center justify-between">
        <a href="{{ route('orders.index') }}" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors flex items-center space-x-1">
            <span>&larr;</span>
            <span>Kembali ke Daftar</span>
        </a>

        <div class="flex items-center space-x-2">
            @if($order->payment_status === 'unpaid')
                <form action="{{ route('orders.mark-paid', $order->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan ini sebagai LUNAS? Tindakan ini akan memotong stok secara otomatis.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3 py-1.5 rounded-md transition-colors flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Tandai Lunas</span>
                    </button>
                </form>
            @endif

            <button type="button" onclick="window.print()" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3 py-1.5 rounded-md transition-colors flex items-center space-x-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Nota</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Card -->
    <div class="bg-white rounded-lg p-6 border border-zinc-200/80 shadow-2xs space-y-5">
        
        <!-- Header Nota -->
        <div class="flex items-start justify-between border-b border-zinc-200 pb-4">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Sajian Marinasi</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Makanan Siap Goreng & Bumbu Marinasi Higienis</p>
                <p class="text-[11px] text-zinc-400 mt-0.5">Order Delivery & Takeaway</p>
            </div>

            <div class="text-right">
                <span class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 block">Struk Pembelian</span>
                <span class="font-mono text-xs font-semibold text-zinc-900 block mt-0.5">{{ $order->invoice_number }}</span>
                <span class="text-xs text-zinc-500 tabular-nums block mt-0.5">{{ \Carbon\Carbon::parse($order->order_date)->format('d F Y') }}</span>
            </div>
        </div>

        <!-- Customer & Payment Box -->
        <div class="grid grid-cols-2 gap-4 p-3.5 rounded-md bg-zinc-50 border border-zinc-200 text-xs">
            <div>
                <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block mb-0.5">Pelanggan:</span>
                <div class="font-medium text-zinc-900">{{ $order->customer_name }}</div>
                <div class="text-zinc-500 tabular-nums mt-0.5">{{ $order->customer_phone ?? 'Tanpa Nomor HP' }}</div>
                @if($order->notes)
                    <div class="text-zinc-500 text-[11px] mt-1 italic">
                        "{{ $order->notes }}"
                    </div>
                @endif
            </div>

            <div class="text-right">
                <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block mb-0.5">Status Pembayaran:</span>
                <div>
                    @if($order->payment_status === 'paid')
                        <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                            ✓ Lunas (Paid)
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-amber-50 text-amber-700 border-amber-200">
                            Belum Lunas
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-zinc-500">
                    Metode: <span class="font-medium uppercase text-zinc-800">{{ $order->payment_method }}</span>
                </div>
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                    <tr>
                        <th class="py-2 px-3">No.</th>
                        <th class="py-2 px-3">Menu Produk</th>
                        <th class="py-2 px-3 text-right">Harga Satuan</th>
                        <th class="py-2 px-3 text-center">Qty</th>
                        <th class="py-2 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @foreach($order->items as $idx => $item)
                        <tr>
                            <td class="py-2.5 px-3 text-zinc-400 tabular-nums">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-medium text-zinc-900">{{ $item->product ? $item->product->name : 'Produk' }}</div>
                                <div class="text-[10px] text-zinc-400 font-mono">{{ $item->product ? $item->product->sku : '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-right tabular-nums text-zinc-600">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-center tabular-nums font-medium text-zinc-900">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-zinc-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Grand Total Calculation Box -->
        <div class="border-t border-zinc-200 pt-3 flex flex-col items-end space-y-1 text-xs">
            <div class="w-56 flex justify-between text-zinc-500">
                <span>Total Item:</span>
                <span class="font-medium text-zinc-800 tabular-nums">{{ $order->items->count() }} Variasi ({{ $order->items->sum('quantity') }} pcs)</span>
            </div>
            <div class="w-56 flex justify-between items-center text-sm border-t border-zinc-100 pt-1.5">
                <span class="font-semibold text-zinc-900">Total Tagihan:</span>
                <span class="font-semibold text-base text-zinc-900 tabular-nums">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="border-t border-zinc-200 pt-4 text-center text-[11px] text-zinc-400">
            Terima kasih atas pesanan Anda di Sajian Marinasi.
        </div>

    </div>

</div>
@endsection
