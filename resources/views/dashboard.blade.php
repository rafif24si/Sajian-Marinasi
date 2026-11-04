@extends('layouts.app')

@section('title', 'Dashboard - Sajian Marinasi')
@section('header_title', 'Ringkasan Bisnis & Analitik')

@section('content')
<div class="space-y-5">

    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-zinc-900">Performa Keuangan & Inventaris</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Ringkasan real-time pergerakan omzet lunas, pengeluaran modal, dan stok produk.</p>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
            @php
                $bulanList = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                $selectedMonth = str_pad(request('month', $month ?? date('m')), 2, '0', STR_PAD_LEFT);
                $selectedYear = request('year', $year ?? date('Y'));
            @endphp

            <div class="flex items-center space-x-1.5">
                <span class="text-xs text-zinc-500">Bulan:</span>
                <select name="month" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 focus:outline-hidden">
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-1.5">
                <span class="text-xs text-zinc-500">Tahun:</span>
                <select name="year" class="bg-white border border-zinc-300 text-zinc-800 text-xs rounded-md px-2.5 py-1.5 focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 focus:outline-hidden">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium px-3 py-1.5 rounded-md border border-transparent transition-colors">
                Terapkan
            </button>
            <a href="{{ route('dashboard') }}" class="bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-medium px-2.5 py-1.5 rounded-md border border-zinc-300 transition-colors">
                Reset
            </a>
        </form>
    </div>

    <!-- 4 Summary Metric Cards (Clean SaaS High-Density) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Omzet -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Omzet</span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200 tabular-nums">
                    {{ $paidOrdersCount ?? 0 }} Lunas
                </span>
            </div>
            <div class="text-2xl font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-zinc-400 mt-1">Pemasukan dari order berstatus paid</div>
        </div>

        <!-- 2. Total Pengeluaran -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Pengeluaran</span>
                <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-rose-50 text-rose-700 border-rose-200">
                    Kas Keluar
                </span>
            </div>
            <div class="text-2xl font-semibold text-zinc-900 tabular-nums mt-1">
                Rp {{ number_format($totalPengeluaran ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-zinc-400 mt-1">Bahan baku, kemasan & operasional</div>
        </div>

        <!-- 3. Laba Bersih Kas -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Laba Bersih Kas</span>
                @if(($labaBersih ?? 0) >= 0)
                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">
                        Surplus
                    </span>
                @else
                    <span class="px-2 py-0.5 text-xs font-medium rounded-md border bg-rose-50 text-rose-700 border-rose-200">
                        Defisit
                    </span>
                @endif
            </div>
            <div class="text-2xl font-semibold {{ ($labaBersih ?? 0) >= 0 ? 'text-zinc-900' : 'text-rose-600' }} tabular-nums mt-1">
                Rp {{ number_format($labaBersih ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-zinc-400 mt-1">Omzet lunas dikurangi pengeluaran</div>
        </div>

        <!-- 4. Total Pesanan -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Pesanan</span>
                <span class="text-[11px] text-zinc-400 tabular-nums">{{ $bulanList[$selectedMonth] }}</span>
            </div>
            <div class="text-2xl font-semibold text-zinc-900 tabular-nums mt-1">
                {{ $totalOrders ?? 0 }}
            </div>
            <div class="text-[11px] text-zinc-500 mt-1 flex items-center space-x-1.5">
                <span class="text-emerald-700 font-medium tabular-nums">{{ $paidOrdersCount ?? 0 }} lunas</span>
                <span>•</span>
                <span class="text-amber-700 font-medium tabular-nums">{{ $unpaidOrdersCount ?? 0 }} belum lunas</span>
            </div>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Chart 1: Arus Kas Harian (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs">
            <div class="flex items-center justify-between mb-3 border-b border-zinc-100 pb-2.5">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Tren Arus Kas Harian</h3>
                    <p class="text-[11px] text-zinc-400">Pemasukan pesanan lunas vs belanja operasional harian</p>
                </div>
                <span class="text-xs font-medium text-zinc-500 tabular-nums">
                    {{ $bulanList[$selectedMonth] }} {{ $selectedYear }}
                </span>
            </div>
            <div class="h-64">
                <canvas id="cashflowTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Top 5 Produk Terlaris (1 col) -->
        <div class="bg-white rounded-lg p-4 border border-zinc-200/80 shadow-2xs flex flex-col">
            <div class="flex items-center justify-between mb-3 border-b border-zinc-100 pb-2.5">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Top 5 Produk Terlaris</h3>
                    <p class="text-[11px] text-zinc-400">Total kuantitas terjual pada pesanan lunas</p>
                </div>
                <span class="text-xs font-medium text-zinc-500 tabular-nums">Unit Terjual</span>
            </div>

            <div class="flex-1 flex flex-col justify-center">
                @if(isset($topProducts) && $topProducts->count() > 0)
                    <div class="h-64">
                        <canvas id="topProductsBarChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-center p-4">
                        <svg class="w-8 h-8 text-zinc-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="text-xs font-medium text-zinc-600">Belum ada data penjualan lunas pada periode ini.</p>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Produk terlaris akan otomatis dihitung saat ada transaksi lunas.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Two Tables: Peringatan Stok & 5 Transaksi Terkini -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Table 1: Peringatan Stok Menipis -->
        <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">Peringatan Stok Menipis</h3>
                    <p class="text-[11px] text-zinc-400">Produk dengan stok di bawah atau sama dengan batas minimum</p>
                </div>
                <span class="px-2 py-0.5 text-xs font-medium rounded-md border {{ ($lowStockProducts ?? collect())->count() > 0 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }} tabular-nums">
                    {{ ($lowStockProducts ?? collect())->count() }} Produk
                </span>
            </div>

            @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 border-b border-zinc-200 text-zinc-500 uppercase tracking-wider font-medium text-[11px]">
                            <tr>
                                <th class="py-2.5 px-3">Produk</th>
                                <th class="py-2.5 px-3">SKU</th>
                                <th class="py-2.5 px-3 text-center">Sisa Stok</th>
                                <th class="py-2.5 px-3 text-center">Batas Min</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($lowStockProducts as $prod)
                                <tr class="hover:bg-zinc-50/60 transition-colors">
                                    <td class="py-2.5 px-3 font-medium text-zinc-900">{{ $prod->name }}</td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-zinc-500">{{ $prod->sku }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border tabular-nums {{ $prod->stock <= 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                            {{ $prod->stock }} pcs
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-zinc-500 tabular-nums">{{ $prod->min_stock_alert }} pcs</td>
                                    <td class="py-2.5 px-3 text-right">
                                        <a href="{{ route('products.index') }}" class="text-xs font-medium text-zinc-700 hover:text-zinc-900 underline">
                                            Restock
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center">
                    <svg class="w-6 h-6 mx-auto mb-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-xs font-medium text-zinc-800">Semua stok produk berada di batas aman.</p>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Tidak ada produk marinasi yang memerlukan restock mendesak.</p>
                </div>
            @endif
        </div>

        <!-- Table 2: 5 Transaksi Terbaru -->
        <div class="bg-white rounded-lg border border-zinc-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-700">5 Transaksi Terbaru</h3>
                    <p class="text-[11px] text-zinc-400">Aktivitas pesanan pelanggan yang baru dicatat</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-medium text-zinc-700 hover:text-zinc-900">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if(isset($recentOrders) && $recentOrders->count() > 0)
                <div class="divide-y divide-zinc-200">
                    @foreach($recentOrders as $ro)
                        <div class="px-4 py-2.5 flex items-center justify-between hover:bg-zinc-50/60 transition-colors">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('orders.show', $ro->id) }}" class="font-mono text-xs font-medium text-zinc-900 hover:underline">
                                        {{ $ro->invoice_number }}
                                    </a>
                                    @if($ro->payment_status === 'paid')
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-md border bg-emerald-50 text-emerald-700 border-emerald-200">Lunas</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-md border bg-amber-50 text-amber-700 border-amber-200">Belum Lunas</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-zinc-500 mt-0.5">
                                    {{ $ro->customer_name }} • <span class="tabular-nums">{{ \Carbon\Carbon::parse($ro->order_date)->format('d/m/Y') }}</span> • <span class="uppercase font-medium text-zinc-600">{{ $ro->payment_method }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium text-zinc-900 tabular-nums">Rp {{ number_format($ro->total_amount, 0, ',', '.') }}</div>
                                <a href="{{ route('orders.show', $ro->id) }}" class="text-[11px] text-zinc-500 hover:text-zinc-800">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-zinc-400">
                    <p class="text-xs font-medium text-zinc-600">Belum ada transaksi pesanan.</p>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Transaksi yang dibuat melalui form kasir akan tampil di sini.</p>
                </div>
            @endif
        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chart Arus Kas Harian
    const trendCtx = document.getElementById('cashflowTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [
                    {
                        label: 'Omzet Lunas',
                        data: @json($chartOmzet ?? []),
                        backgroundColor: '#10b981',
                        borderRadius: 3,
                        barPercentage: 0.55
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($chartExpense ?? []),
                        backgroundColor: '#f43f5e',
                        borderRadius: 3,
                        barPercentage: 0.55
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 10, font: { family: 'Inter', size: 11, weight: '500' }, color: '#52525b' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#71717a' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f4f4f5' },
                        ticks: {
                            font: { family: 'Inter', size: 10 },
                            color: '#71717a',
                            callback: function(val) {
                                if (val >= 1000000) return (val / 1000000) + 'M';
                                if (val >= 1000) return (val / 1000) + 'k';
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Chart Top 5 Produk Terlaris (Horizontal Bar)
    const topCtx = document.getElementById('topProductsBarChart');
    if (topCtx) {
        const productLabels = [];
        const productData = [];
        @foreach($topProducts as $item)
            productLabels.push(@json($item->product ? $item->product->name : 'Produk #'.$item->product_id));
            productData.push({{ (int)$item->total_qty }});
        @endforeach

        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Unit Terjual',
                    data: productData,
                    backgroundColor: '#18181b',
                    borderRadius: 3,
                    barPercentage: 0.6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Terjual: ' + context.raw + ' pcs';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f4f4f5' },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#71717a', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#27272a' }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
