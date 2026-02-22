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
            'client_name' => 'nullable|string|max:255',
        ]);

        if ($request->purpose === 'other' && empty($request->notes)) {
             return back()->withErrors(['notes' => 'Catatan wajib diisi untuk alasan Lainnya.']);
        }

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
                'client_name' => $request->client_name,
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
    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:deduction,addition',
            'purpose' => 'required|in:sale,internal_use,personal_use,damage,expired,return_to_supplier,other',
            'notes' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
        ]);

        if ($request->purpose === 'other' && empty($request->notes)) {
             return back()->withErrors(['notes' => 'Catatan wajib diisi untuk alasan Lainnya.']);
        }

        try {
            DB::beginTransaction();

            $product = $stockAdjustment->product;

            // 1. Revert old adjustment
            if ($stockAdjustment->type === 'deduction') {
                $product->stock += $stockAdjustment->quantity_adjusted;
            } else {
                $product->stock -= $stockAdjustment->quantity_adjusted;
            }

            // 2. Apply new adjustment
            // Check stock availability if deduction
            if ($request->type === 'deduction') {
                 // Note: We use the reverted stock to check
                 if ($request->quantity > $product->stock) {
                    DB::rollBack();
                    return back()->withErrors(['quantity' => 'Jumlah pemotongan melebihi stok tersedia.']);
                 }
                 $product->stock -= $request->quantity;
            } else {
                $product->stock += $request->quantity;
            }

            $product->save();

            // 3. Update Adjustment Record
            $stockAdjustment->update([
                'quantity_before' => $stockAdjustment->quantity_before, // Technically 'before' refers to the state at THAT time, so we keep it? 
                // Actually, if we edit, the 'quantity_before' is historical context. 
                // However, to keep it consistent with the *result*, maybe we shouldn't change quantity_before unless we're strictly re-playing history.
                // For simplicity and audit, we just update the adjusted amount and type. 
                // The 'quantity_after' needs to be updated relative to the 'quantity_before' stored? 
                // No, that makes numbers inconsistent if other tx happened in between.
                // Let's just update the *Impact* and the *Current Stock*. 
                // BUT, 'quantity_after' in the record is supposed to be the stock AFTER that specific transaction.
                // If we edit it later, that historical 'after' value might be misleading if we don't propagate changes to all subsequent transactions (which is too complex).
                // DECISION: We will update 'quantity_adjusted', 'type', 'purpose', 'notes'. 
                // We will NOT try to shift 'quantity_after' of subsequent records. 
                // We will just update this record's 'quantity_after' based on its own 'quantity_before' + new change?
                // Or better, just update the Product stock and trust the logs.
                // Let's update quantity_after based on the stored quantity_before for consistency of THIS record.
                 'quantity_adjusted' => $request->quantity,
                 'quantity_after' => ($request->type === 'addition') 
                        ? $stockAdjustment->quantity_before + $request->quantity 
                        : $stockAdjustment->quantity_before - $request->quantity,
                'type' => $request->type,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
                'client_name' => $request->client_name,
                'adjusted_by' => auth()->id(), // Update who modified it last
            ]);

            DB::commit();

            return back()->with('success', 'Penyesuaian stok berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock adjustment', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal memperbarui penyesuaian stok.']);
        }
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        try {
            DB::beginTransaction();

            $product = $stockAdjustment->product;

            // Prevent deletion if product has never had an incoming stock (StockIn)
            if (!$product->stockIns()->exists()) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Penyesuaian stok tidak dapat dihapus karena produk ini belum pernah menerima stok masuk (Stock In) pertama.']);
            }

            // Revert stock change
            if ($stockAdjustment->type === 'deduction') {
                $product->stock += $stockAdjustment->quantity_adjusted;
            } else {
                $product->stock -= $stockAdjustment->quantity_adjusted;
            }
            $product->save();

            $stockAdjustment->delete();

            DB::commit();

            return back()->with('success', 'Penyesuaian stok berhasil dihapus dan stok dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete stock adjustment', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal menghapus penyesuaian stok.']);
        }
    }
}
