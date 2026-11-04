<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reporting and revenue analytics page.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        // 1. Paid Orders in Date Range
        $paidOrders = Order::with(['items.product'])
            ->where('payment_status', 'paid')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date')
            ->get();

        // 2. Expenses in Date Range
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date')
            ->get();

        // 3. Financial Metrics
        $totalOmzet = (float) $paidOrders->sum('total_amount');

        // Total HPP (Cost of Goods Sold)
        $totalHpp = (float) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('payment_status', 'paid')
                ->whereBetween('order_date', [$startDate, $endDate]);
        })->sum(DB::raw('quantity * unit_cost'));

        $labaKotor = $totalOmzet - $totalHpp;
        $totalPengeluaran = (float) $expenses->sum('amount');
        $arusKasBersih = $totalOmzet - $totalPengeluaran;

        // 4. Payment Method Distribution
        $paymentMethods = [
            'qris' => [
                'count' => $paidOrders->where('payment_method', 'qris')->count(),
                'total' => (float) $paidOrders->where('payment_method', 'qris')->sum('total_amount'),
            ],
            'transfer' => [
                'count' => $paidOrders->where('payment_method', 'transfer')->count(),
                'total' => (float) $paidOrders->where('payment_method', 'transfer')->sum('total_amount'),
            ],
            'cash' => [
                'count' => $paidOrders->where('payment_method', 'cash')->count(),
                'total' => (float) $paidOrders->where('payment_method', 'cash')->sum('total_amount'),
            ],
        ];

        // 5. Daily Aggregation for Charts/Trend
        $groupedPaidOrders = $paidOrders->groupBy(function ($order) {
            return Carbon::parse($order->order_date)->format('Y-m-d');
        });
        $groupedExpenses = $expenses->groupBy(function ($exp) {
            return Carbon::parse($exp->expense_date)->format('Y-m-d');
        });

        $dailyData = [];
        $current = strtotime($startDate);
        $last = strtotime($endDate);

        if ($current && $last && $current <= $last) {
            while ($current <= $last) {
                $d = date('Y-m-d', $current);
                $dailyOmzet = (float) ($groupedPaidOrders->get($d)?->sum('total_amount') ?? 0);
                $dailyExpense = (float) ($groupedExpenses->get($d)?->sum('amount') ?? 0);

                $dailyData[$d] = [
                    'date' => date('d M', $current),
                    'omzet' => $dailyOmzet,
                    'expense' => $dailyExpense,
                ];
                $current = strtotime('+1 day', $current);
            }
        }

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'paidOrders',
            'expenses',
            'totalOmzet',
            'totalHpp',
            'labaKotor',
            'totalPengeluaran',
            'arusKasBersih',
            'paymentMethods',
            'dailyData'
        ));
    }
}
