@extends('layouts.app')

@section('title', 'Laporan Keuangan - Sajian Marinasi')
@section('header_title', 'Laporan Omzet, HPP & Arus Kas')

@section('content')
<div class="space-y-5">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Rekapitulasi Finansial & Operasional</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Analisis pendapatan omzet lunas, HPP modal produk terjual, biaya belanja, dan arus kas bersih.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Date Range Filter Form -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center space-x-1.5">
                    <span class="text-xs text-zinc-500">Dari:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <div class="flex items-center space-x-1.5">
                    <span class="text-xs text-zinc-500">Sampai:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-hidden">
                </div>

                <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3 py-1.5 rounded-md border border-transparent transition-colors">
                    Filter Laporan
                </button>
            </form>

            <button type="button" onclick="window.print()" class="no-print bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-3 py-1.5 rounded-md border border-zinc-300 transition-colors flex items-center space-x-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Financial Key Metrics (5 Cards - High Density) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- 1. Total Omzet -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">1. Omzet Kotor (Bruto)</span>
            <div class="text-lg font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Total transaksi lunas</span>
        </div>

        <!-- 2. Total HPP -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">2. Total HPP (COGS)</span>
            <div class="text-lg font-semibold text-zinc-700 tabular-nums mt-1">
                Rp {{ number_format($totalHpp ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Modal bahan pokok terjual</span>
        </div>

        <!-- 3. Laba Kotor -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">3. Laba Kotor</span>
            <div class="text-lg font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($labaKotor ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Omzet - HPP</span>
        </div>

        <!-- 4. Pengeluaran Belanja -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium text-zinc-500 uppercase tracking-wider block">4. Total Pengeluaran</span>
            <div class="text-lg font-semibold text-rose-600 tabular-nums mt-1">
                Rp {{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Belanja & Operasional</span>
        </div>

        <!-- 5. Arus Kas Bersih -->
        <div class="bg-white p-3.5 rounded-lg border border-zinc-200/80 shadow-2xs">
            <span class="text-[10px] font-medium uppercase tracking-wider block {{ ($arusKasBersih ?? 0) >= 0 ? 'text-zinc-500' : 'text-rose-600' }}">5. Arus Kas Bersih</span>
            <div class="text-lg font-semibold mt-1 tabular-nums {{ ($arusKasBersih ?? 0) >= 0 ? 'text-zinc-900' : 'text-rose-600' }}">
                Rp {{ number_format($arusKasBersih ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-zinc-400 block mt-0.5">Omzet - Pengeluaran</span>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs space-y-3">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
            Distribusi Metode Pembayaran Pelanggan
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <!-- QRIS -->
            <div class="p-3 rounded-md bg-zinc-50 border border-zinc-200 flex items-center justify-between">
                <div>
                    <span class="font-medium text-zinc-900 block">QRIS</span>
                    <span class="text-zinc-500 text-[11px] tabular-nums">{{ $paymentMethods['qris']['count'] ?? 0 }} Transaksi</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold text-zinc-900 tabular-nums">Rp {{ number_format($paymentMethods['qris']['total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Transfer -->
            <div class="p-3 rounded-md bg-zinc-50 border border-zinc-200 flex items-center justify-between">
                <div>
                    <span class="font-medium text-zinc-900 block">Transfer Bank</span>
                    <span class="text-zinc-500 text-[11px] tabular-nums">{{ $paymentMethods['transfer']['count'] ?? 0 }} Transaksi</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold text-zinc-900 tabular-nums">Rp {{ number_format($paymentMethods['transfer']['total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Cash -->
            <div class="p-3 rounded-md bg-zinc-50 border border-zinc-200 flex items-center justify-between">
                <div>
                    <span class="font-medium text-zinc-900 block">Tunai (Cash)</span>
                    <span class="text-zinc-500 text-[11px] tabular-nums">{{ $paymentMethods['cash']['count'] ?? 0 }} Transaksi</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold text-zinc-900 tabular-nums">Rp {{ number_format($paymentMethods['cash']['total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Detailed Report Tables -->
    <div class="space-y-5">

        <!-- Table 1: Rincian Pemasukan (Paid Orders) -->
        <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                    Rincian Transaksi Pemasukan Lunas ({{ $paidOrders->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                        <tr>
                            <th class="py-2.5 px-4">No. Invoice</th>
                            <th class="py-2.5 px-4">Tanggal</th>
                            <th class="py-2.5 px-4">Customer</th>
                            <th class="py-2.5 px-4 text-center">Metode</th>
                            <th class="py-2.5 px-4 text-right">Omzet</th>
                            <th class="py-2.5 px-4 text-right">Estimasi HPP</th>
                            <th class="py-2.5 px-4 text-right">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($paidOrders as $po)
                            @php
                                $orderHpp = $po->items->sum(function($it) { return $it->quantity * $it->unit_cost; });
                                $orderProfit = $po->total_amount - $orderHpp;
                            @endphp
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="py-2.5 px-4 font-mono text-[11px] font-medium text-zinc-900">
                                    <a href="{{ route('orders.show', $po->id) }}" class="hover:underline">{{ $po->invoice_number }}</a>
                                </td>
                                <td class="py-2.5 px-4 text-zinc-600 tabular-nums">{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                                <td class="py-2.5 px-4 font-medium text-zinc-900">{{ $po->customer_name }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium uppercase border bg-zinc-100 text-zinc-700 border-zinc-200">
                                        {{ $po->payment_method }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right tabular-nums font-semibold text-zinc-900">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 text-right tabular-nums text-zinc-600">Rp {{ number_format($orderHpp, 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 text-right tabular-nums font-medium text-emerald-700">Rp {{ number_format($orderProfit, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-zinc-400">Tidak ada transaksi lunas pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table 2: Rincian Pengeluaran Belanja -->
        <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">
                    Rincian Pengeluaran Belanja & Operasional ({{ $expenses->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                        <tr>
                            <th class="py-2.5 px-4">Tanggal</th>
                            <th class="py-2.5 px-4">Kategori</th>
                            <th class="py-2.5 px-4">Keterangan</th>
                            <th class="py-2.5 px-4">Catatan</th>
                            <th class="py-2.5 px-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($expenses as $ex)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="py-2.5 px-4 text-zinc-600 tabular-nums">{{ \Carbon\Carbon::parse($ex->expense_date)->format('d/m/Y') }}</td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium uppercase border bg-zinc-100 text-zinc-700 border-zinc-200">
                                        {{ str_replace('_', ' ', $ex->category) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 font-medium text-zinc-900">{{ $ex->title }}</td>
                                <td class="py-2.5 px-4 text-zinc-500">{{ $ex->notes ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-right tabular-nums font-semibold text-rose-600">Rp {{ number_format($ex->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-zinc-400">Tidak ada catatan pengeluaran pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
