<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Modul Stok Produk
Route::resource('products', ProductController::class);
Route::post('products/{product}/add-stock', [ProductController::class, 'addStock'])->name('products.add-stock');

// Modul Pengeluaran Belanja
Route::resource('expenses', ExpenseController::class);

// Modul Transaksi & Pembayaran Pelanggan
Route::resource('orders', OrderController::class);
Route::patch('orders/{order}/mark-paid', [OrderController::class, 'markAsPaid'])->name('orders.mark-paid');

// Modul Laporan Pemasukan & Omzet
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
