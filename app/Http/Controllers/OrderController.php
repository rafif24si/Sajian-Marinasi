<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of customer orders with filters.
     */
    public function index(Request $request)
    {
        $status = $request->get('payment_status');
        $method = $request->get('payment_method');
        $search = $request->get('search');
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $query = Order::with(['items.product'])->latest('order_date')->latest('id');

        if ($status && $status !== 'all') {
            $query->where('payment_status', $status);
        }
        if ($method && $method !== 'all') {
            $query->where('payment_method', $method);
        }
        if ($month && $month !== 'all') {
            $query->whereMonth('order_date', $month);
        }
        if ($year && $year !== 'all') {
            $query->whereYear('order_date', $year);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Calculate summary counts
        $baseCountQuery = Order::query();
        if ($month && $month !== 'all') {
            $baseCountQuery->whereMonth('order_date', $month);
        }
        if ($year && $year !== 'all') {
            $baseCountQuery->whereYear('order_date', $year);
        }

        $totalOrdersCount = (clone $baseCountQuery)->count();
        $paidOrdersCount = (clone $baseCountQuery)->where('payment_status', 'paid')->count();
        $unpaidOrdersCount = (clone $baseCountQuery)->where('payment_status', 'unpaid')->count();
        $totalPaidAmount = (clone $baseCountQuery)->where('payment_status', 'paid')->sum('total_amount');

        return view('orders.index', compact(
            'orders',
            'status',
            'method',
            'search',
            'month',
            'year',
            'totalOrdersCount',
            'paidOrdersCount',
            'unpaidOrdersCount',
            'totalPaidAmount'
        ));
    }

    /**
     * Show the form for creating a new order (Cashier / POS).
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('orders.create', compact('products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'order_date' => 'required|date',
            'payment_status' => 'required|in:unpaid,paid',
            'payment_method' => 'required|in:cash,transfer,qris',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $itemsInput = $request->input('items', []);

        // Pre-validate stock if order is directly PAID
        if ($request->input('payment_status') === 'paid') {
            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    return back()->withInput()->with('error', "Stok tidak mencukupi untuk \"{$product->name}\"! Stok saat ini: {$product->stock}, jumlah dipesan: {$item['quantity']}.");
                }
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $itemsInput) {
                // Generate unique invoice number
                $invoiceNumber = 'INV-'.date('Ymd').'-'.str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                while (Order::where('invoice_number', $invoiceNumber)->exists()) {
                    $invoiceNumber = 'INV-'.date('Ymd').'-'.str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }

                $order = Order::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $request->input('customer_name'),
                    'customer_phone' => $request->input('customer_phone'),
                    'order_date' => $request->input('order_date'),
                    'total_amount' => 0,
                    'payment_status' => $request->input('payment_status'),
                    'payment_method' => $request->input('payment_method'),
                    'notes' => $request->input('notes'),
                ]);

                $grandTotal = 0;
                $isPaid = $order->payment_status === 'paid';

                foreach ($itemsInput as $itemData) {
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                    $qty = (int) $itemData['quantity'];
                    $unitCost = $product->cost_price;
                    $unitPrice = $product->selling_price;
                    $subtotal = $qty * $unitPrice;
                    $grandTotal += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    // Deduct stock if paid immediately
                    if ($isPaid) {
                        if ($product->stock < $qty) {
                            throw new \RuntimeException("Stok untuk produk {$product->name} tidak mencukupi saat proses pembayaran.");
                        }

                        $product->decrement('stock', $qty);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => 'out',
                            'quantity' => $qty,
                            'reference' => $order->invoice_number,
                            'notes' => 'Penjualan Lunas - '.$order->customer_name,
                        ]);
                    }
                }

                $order->update(['total_amount' => $grandTotal]);

                return $order;
            });

            return redirect()->route('orders.show', $order->id)->with('success', 'Transaksi '.$order->invoice_number.' berhasil dibuat!');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal memproses transaksi: '.$e->getMessage());
        }
    }

    /**
     * Display the specified invoice / order detail.
     */
    public function show(Order $order)
    {
        $order->load(['items.product']);

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return redirect()->route('orders.show', $order->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        return redirect()->route('orders.show', $order->id);
    }

    /**
     * Mark an unpaid order as paid and deduct stock automatically.
     */
    public function markAsPaid(Request $request, Order $order)
    {
        if ($order->payment_status === 'paid') {
            return back()->with('info', 'Pesanan ini sudah berstatus Lunas.');
        }

        $order->load('items.product');

        // Check stock availability for all items before mutating
        foreach ($order->items as $item) {
            if (! $item->product || $item->product->stock < $item->quantity) {
                $pName = $item->product ? $item->product->name : 'Produk';
                $curStock = $item->product ? $item->product->stock : 0;

                return back()->with('error', "Gagal melunasi pesanan! Stok \"{$pName}\" tidak mencukupi (Tersedia: {$curStock}, Dibutuhkan: {$item->quantity}). Silakan lakukan Restock produk terlebih dahulu!");
            }
        }

        try {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    $product->decrement('stock', $item->quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $item->quantity,
                        'reference' => $order->invoice_number,
                        'notes' => 'Penjualan Lunas - '.$order->customer_name,
                    ]);
                }

                $order->update(['payment_status' => 'paid']);
            });

            return back()->with('success', "Pesanan {$order->invoice_number} berhasil ditandai LUNAS dan stok produk telah otomatis dipotong!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses pelunasan: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified order from storage (restore stock if paid).
     */
    public function destroy(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                $order->load('items.product');

                // If order was paid, restore stock
                if ($order->payment_status === 'paid') {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);

                            StockMovement::create([
                                'product_id' => $item->product_id,
                                'type' => 'in',
                                'quantity' => $item->quantity,
                                'reference' => $order->invoice_number,
                                'notes' => 'Restorasi stok dari penghapusan transaksi',
                            ]);
                        }
                    }
                }

                $order->delete();
            });

            return redirect()->route('orders.index')->with('success', "Pesanan {$order->invoice_number} berhasil dibatalkan dan dihapus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus pesanan: '.$e->getMessage());
        }
    }
}
