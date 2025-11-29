<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockIn::with(['product', 'user', 'creator', 'updater']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $stock_ins = $query->oldest()->paginate(10);

        return Inertia::render('Admin/StockIns/Index', [
            'stock_ins' => $stock_ins,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with('category')->get();

        return Inertia::render('Admin/StockIns/Create', [
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        // Create stock in record
        StockIn::create($validated);

        // Update product stock only
        $product = Product::find($validated['product_id']);
        $product->increment('stock', $validated['quantity']);

        return redirect()->route('stock-ins.index')
            ->with('success', 'Barang masuk berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockIn $stockIn)
    {
        return Inertia::render('Admin/StockIns/Show', [
            'stockIn' => $stockIn->load(['product', 'user', 'creator', 'updater'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockIn $stockIn)
    {
        $products = Product::with('category')->get();

        return Inertia::render('Admin/StockIns/Edit', [
            'stockIn' => $stockIn->load(['product', 'user', 'creator', 'updater']),
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockIn $stockIn)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Revert old stock
        $product = Product::find($stockIn->product_id);
        $product->decrement('stock', $stockIn->quantity);

        // Update stock in
        $stockIn->update($validated);

        // Add new stock
        $newProduct = Product::find($validated['product_id']);
        $newProduct->increment('stock', $validated['quantity']);

        return redirect()->route('stock-ins.index')
            ->with('success', 'Data barang masuk berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockIn $stockIn)
    {
        // Revert stock
        $product = Product::find($stockIn->product_id);
        $product->decrement('stock', $stockIn->quantity);

        $stockIn->delete();

        return redirect()->route('stock-ins.index')
            ->with('success', 'Data barang masuk berhasil dihapus.');
    }
}
