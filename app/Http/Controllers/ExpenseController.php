<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['user', 'creator', 'updater']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('description', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $expenses = $query->oldest()->paginate(10);

        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => $expenses,
            'filters' => $request->only(['search', 'category'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Expenses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return Inertia::render('Admin/Expenses/Show', [
            'expense' => $expense->load('user')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        return Inertia::render('Admin/Expenses/Edit', [
            'expense' => $expense
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Data pengeluaran berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
