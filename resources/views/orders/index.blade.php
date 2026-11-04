@extends('layouts.app')

@section('title', 'Transaksi Penjualan - Sajian Marinasi')
@section('header_title', 'Transaksi Kasir & Pembayaran Pelanggan')

@section('content')
<div class="space-y-5">

    <!-- Top Action Bar -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Daftar Transaksi Pelanggan</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Kelola status pelunasan, cetak struk invoice, dan pemantauan omzet transaksi.</p>
        </div>

        <div>
            <a href="{{ route('orders.create') }}" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3.5 py-2 rounded-md border border-transparent transition-colors flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Pesanan Baru (Kasir)</span>
            </a>
        </div>
    </div>

    <!-- 4 Summary Metric Cards (High-Density) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Total Omzet Lunas</span>
            <div class="text-lg font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($totalPaidAmount ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Dari transaksi status PAID</span>
        </div>

        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">Total Pesanan</span>
            <div class="text-lg font-semibold text-zinc-900 tabular-nums mt-1">
                {{ $totalOrdersCount ?? 0 }} <span class="text-xs font-normal text-zinc-500">transaksi</span>
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Pada periode terpilih</span>
        </div>

        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-emerald-700 uppercase tracking-wider block">Pesanan Lunas</span>
            <div class="text-lg font-semibold text-emerald-700 tabular-nums mt-1">
                {{ $paidOrdersCount ?? 0 }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Stok otomatis terpotong</span>
        </div>

        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-amber-700 uppercase tracking-wider block">Belum Lunas</span>
            <div class="text-lg font-semibold text-amber-700 tabular-nums mt-1">
                {{ $unpaidOrdersCount ?? 0 }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Menunggu konfirmasi bayar</span>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white rounded-lg p-3.5 border border-zinc-200/80 shadow-2xs">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap items-center gap-2">
            @php
                $bulanList = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                $selectedMonth = str_pad(request('month', $month ?? date('m')), 2, '0', STR_PAD_LEFT);
                $selectedYear = request('year', $year ?? date('Y'));
                $selectedStatus = request('payment_status', $status ?? 'all');
                $selectedMethod = request('payment_method', $method ?? 'all');
            @endphp

            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice atau nama customer..." class="w-full bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-3 py-1.5 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
            </div>

            <select name="payment_status" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                <option value="all" {{ $selectedStatus == 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="paid" {{ $selectedStatus == 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                <option value="unpaid" {{ $selectedStatus == 'unpaid' ? 'selected' : '' }}>Belum Lunas (Unpaid)</option>
            </select>

            <select name="payment_method" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                <option value="all" {{ $selectedMethod == 'all' ? 'selected' : '' }}>Semua Metode</option>
                <option value="qris" {{ $selectedMethod == 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="transfer" {{ $selectedMethod == 'transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="cash" {{ $selectedMethod == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
            </select>

            <select name="month" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                @foreach($bulanList as $num => $nama)
                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="year" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                @for($y = date('Y') + 1; $y >= 2024; $y--)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3 py-1.5 rounded-md border border-transparent transition-colors">
                Cari & Filter
            </button>
            <a href="{{ route('orders.index') }}" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2.5 py-1.5 rounded-md border border-zinc-300 transition-colors">
                Reset
            </a>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                Daftar Pesanan Customer ({{ $orders->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                    <tr>
                        <th class="py-2.5 px-4">No. Invoice</th>
                        <th class="py-2.5 px-4">Customer</th>
                        <th class="py-2.5 px-4">Tanggal</th>
                        <th class="py-2.5 px-4">Rincian Item</th>
                        <th class="py-2.5 px-4 text-right">Total Tagihan</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                        <th class="py-2.5 px-4 text-center">Metode</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($orders as $ord)
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] font-medium text-zinc-900">
                                <a href="{{ route('orders.show', $ord->id) }}" class="hover:underline">
                                    {{ $ord->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-zinc-900">{{ $ord->customer_name }}</div>
                                <div class="text-[11px] text-zinc-400 tabular-nums">{{ $ord->customer_phone ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-zinc-600 tabular-nums">
                                {{ \Carbon\Carbon::parse($ord->order_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-zinc-700">
                                @foreach($ord->items as $item)
                                    <div class="text-[11px] leading-relaxed">
                                        {{ $item->product ? $item->product->name : 'Produk' }} <span class="font-medium tabular-nums">({{ $item->quantity }}x)</span>
                                    </div>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-zinc-900">
                                Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($ord->payment_status === 'paid')
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-amber-50 text-amber-700 border-amber-200">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-medium uppercase border bg-zinc-100 text-zinc-700 border-zinc-200">
                                    {{ $ord->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                @if($ord->payment_status === 'unpaid')
                                    <form action="{{ route('orders.mark-paid', $ord->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tandai pesanan {{ $ord->invoice_number }} sebagai LUNAS? Tindakan ini akan memotong stok secara otomatis.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-2 py-1 rounded-md transition-colors">
                                            Lunaskan
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('orders.show', $ord->id) }}" class="inline-block bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2 py-1 rounded-md border border-zinc-300 transition-colors">
                                    Invoice
                                </a>

                                <form action="{{ route('orders.destroy', $ord->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Batalkan & hapus pesanan {{ $ord->invoice_number }}? Stok yang sudah terpotong akan dikembalikan otomatis.')">
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
                                <svg class="w-8 h-8 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-xs font-medium text-zinc-600">Belum ada transaksi pesanan pada periode ini.</p>
                                <p class="text-[11px] text-zinc-400 mt-0.5">Gunakan tombol "Buat Pesanan Baru" di atas untuk mencatat order kasir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-4 py-3 border-t border-zinc-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
