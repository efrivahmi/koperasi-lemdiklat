<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockAdjustmentController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:deduction,addition',
            'purpose' => 'required|in:sale,internal_use,personal_use,damage,expired,return_to_supplier,other',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->type === 'deduction' && $request->quantity > $product->stock) {
            return back()->withErrors([
                'quantity' => 'Jumlah pemotongan tidak boleh lebih dari stok tersedia.'
            ]);
        }

        try {
            DB::beginTransaction();

            $quantityBefore = $product->stock;

            if ($request->type === 'deduction') {
                $newStock = $quantityBefore - $request->quantity;
            } else {
                $newStock = $quantityBefore + $request->quantity;
            }

            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'quantity_before' => $quantityBefore,
                'quantity_adjusted' => $request->quantity,
                'quantity_after' => $newStock,
                'type' => $request->type,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
                'adjusted_by' => auth()->id(),
            ]);

            $product->update([
                'stock' => $newStock,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            Log::info('Stock adjustment created', [
                'adjustment_id' => $adjustment->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'stock_before' => $quantityBefore,
                'stock_after' => $newStock,
                'adjusted_by' => auth()->user()->name,
            ]);

            return redirect()->route('products.index')->with('success',
                'Stok berhasil ' . ($request->type === 'deduction' ? 'dipotong' : 'ditambah') . '.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create stock adjustment', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyesuaikan stok.'
            ]);
        }
    }

    public function history(Product $product)
    {
        $adjustments = $product->stockAdjustments()
            ->with('adjustedBy')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($adjustments);
    }
}
