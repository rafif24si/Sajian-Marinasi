<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display main dashboard with real-time charts and analytics.
     */
    public function index(Request $request)
    {
        $month = (string) $request->get('month', date('m'));
        $year = (string) $request->get('year', date('Y'));

        // Normalize month with 2 digits
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        // 1. Total Omzet (Pesanan PAID)
        $totalOmzet = (float) Order::where('payment_status', 'paid')
            ->whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->sum('total_amount');

        // 2. Total Pengeluaran
        $totalPengeluaran = (float) Expense::whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        // 3. Laba Bersih Kas (Omzet - Pengeluaran)
        $labaBersih = $totalOmzet - $totalPengeluaran;

        // 4. Statistik Pesanan
        $totalOrders = Order::whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->count();

        $paidOrdersCount = Order::where('payment_status', 'paid')
            ->whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->count();

        $unpaidOrdersCount = Order::where('payment_status', 'unpaid')
            ->whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->count();

        // 5. Indikator Stok Menipis
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock_alert')
            ->orderBy('stock')
            ->get();

        // 6. Top 5 Produk Terlaris
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', function ($q) use ($month, $year) {
                $q->where('payment_status', 'paid')
                    ->whereMonth('order_date', $month)
                    ->whereYear('order_date', $year);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 7. 5 Transaksi Terbaru
        $recentOrders = Order::with('items.product')
            ->latest('order_date')
            ->latest('id')
            ->take(5)
            ->get();

        // 8. Data Tren Arus Kas Harian (Chart.js Bar/Line Chart)
        $daysInMonth = Carbon::createFromDate((int) $year, (int) $month, 1)->daysInMonth;
        $chartLabels = [];
        $chartOmzet = [];
        $chartExpense = [];

        $monthOrders = Order::where('payment_status', 'paid')
            ->whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->order_date)->format('Y-m-d');
            });

        $monthExpenses = Expense::whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->get()
            ->groupBy(function ($exp) {
                return Carbon::parse($exp->expense_date)->format('Y-m-d');
            });

        $hasCashflowActivity = false;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $chartLabels[] = (string) $day;

            $dayOmzet = (float) ($monthOrders->get($dateStr)?->sum('total_amount') ?? 0);
            $dayExpense = (float) ($monthExpenses->get($dateStr)?->sum('amount') ?? 0);

            if ($dayOmzet > 0 || $dayExpense > 0) {
                $hasCashflowActivity = true;
            }

            $chartOmzet[] = $dayOmzet;
            $chartExpense[] = $dayExpense;
        }

        return view('dashboard', compact(
            'totalOmzet',
            'totalPengeluaran',
            'labaBersih',
            'totalOrders',
            'paidOrdersCount',
            'unpaidOrdersCount',
            'lowStockProducts',
            'topProducts',
            'recentOrders',
            'chartLabels',
            'chartOmzet',
            'chartExpense',
            'hasCashflowActivity',
            'month',
            'year'
        ));
    }
}
