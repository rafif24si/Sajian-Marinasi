<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses with filters.
     */
    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $category = $request->get('category');

        $query = Expense::query();

        if ($month && $month !== 'all') {
            $query->whereMonth('expense_date', $month);
        }
        if ($year && $year !== 'all') {
            $query->whereYear('expense_date', $year);
        }
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->get();

        $totalExpense = (float) $expenses->sum('amount');

        // Breakdown per category
        $baseQuery = Expense::query();
        if ($month && $month !== 'all') {
            $baseQuery->whereMonth('expense_date', $month);
        }
        if ($year && $year !== 'all') {
            $baseQuery->whereYear('expense_date', $year);
        }

        $categoryTotals = [
            'bahan_baku' => (float) (clone $baseQuery)->where('category', 'bahan_baku')->sum('amount'),
            'kemasan' => (float) (clone $baseQuery)->where('category', 'kemasan')->sum('amount'),
            'operasional' => (float) (clone $baseQuery)->where('category', 'operasional')->sum('amount'),
            'lainnya' => (float) (clone $baseQuery)->where('category', 'lainnya')->sum('amount'),
        ];

        return view('expenses.index', compact('expenses', 'totalExpense', 'categoryTotals', 'month', 'year', 'category'));
    }

    /**
     * Show the form for creating a new resource (redirect to modal on index).
     */
    public function create()
    {
        return redirect()->route('expenses.index');
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|in:bahan_baku,kemasan,operasional,lainnya',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('receipt_image')) {
            $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Catatan pengeluaran belanja berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return redirect()->route('expenses.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        return redirect()->route('expenses.index');
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|in:bahan_baku,kemasan,operasional,lainnya',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image && Storage::disk('public')->exists($expense->receipt_image)) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Data pengeluaran "'.$expense->title.'" berhasil diperbarui.');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        if ($expense->receipt_image && Storage::disk('public')->exists($expense->receipt_image)) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
