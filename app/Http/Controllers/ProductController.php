<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of products and stock movements.
     */
    public function index()
    {
        $products = Product::withCount('orderItems')->latest()->get();
        $recentMovements = StockMovement::with('product')->latest()->take(10)->get();

        return view('products.index', compact('products', 'recentMovements'));
    }

    /**
     * Show the form for creating a new resource (redirect to index modal).
     */
    public function create()
    {
        return redirect()->route('products.index');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            if ($product->stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $product->stock,
                    'reference' => 'Stok Awal',
                    'notes' => 'Inisialisasi produk baru',
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan ke katalog!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return redirect()->route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return redirect()->route('products.index');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,'.$product->id,
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data produk '.$product->name.' berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Produk "'.$product->name.'" tidak dapat dihapus karena sudah memiliki riwayat transaksi pelanggan!');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus dari sistem.');
    }

    /**
     * Add or subtract stock manually.
     */
    public function addStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['type'] === 'out' && $product->stock < $validated['quantity']) {
            return redirect()->route('products.index')->with('error', 'Gagal! Jumlah pengurangan stok ('.$validated['quantity'].') melebihi sisa stok saat ini ('.$product->stock.').');
        }

        DB::transaction(function () use ($product, $validated) {
            if ($validated['type'] === 'in') {
                $product->increment('stock', $validated['quantity']);
            } else {
                $product->decrement('stock', $validated['quantity']);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reference' => $validated['reference'] ?: ($validated['type'] === 'in' ? 'Produksi / Restock' : 'Koreksi Stok / Rusak'),
                'notes' => $validated['notes'],
            ]);
        });

        $actionText = $validated['type'] === 'in' ? 'penambahan (+'.$validated['quantity'].')' : 'pengurangan (-'.$validated['quantity'].')';

        return redirect()->route('products.index')->with('success', 'Mutasi stok '.$actionText.' untuk "'.$product->name.'" berhasil dicatat!');
    }
}
