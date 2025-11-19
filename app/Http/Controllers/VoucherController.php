<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['student', 'usedBy']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('code', 'like', '%' . $search . '%');
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $vouchers = $query->latest()->paginate(10);

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Vouchers/Create');
    }

    /**
     * Store a newly created resource in storage (Generate vouchers)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1000',
            'quantity' => 'required|integer|min:1|max:100',
            'expired_at' => 'nullable|date|after:today',
        ]);

        $vouchers = [];

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = 'VCR-' . strtoupper(Str::random(8));

            $vouchers[] = [
                'code' => $code,
                'nominal' => $validated['nominal'],
                'status' => 'available',
                'expired_at' => $validated['expired_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Voucher::insert($vouchers);

        return redirect()->route('vouchers.index')
            ->with('success', $validated['quantity'] . ' voucher berhasil di-generate.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        return Inertia::render('Admin/Vouchers/Show', [
            'voucher' => $voucher->load(['student', 'usedBy'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        return Inertia::render('Admin/Vouchers/Edit', [
            'voucher' => $voucher
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1000',
            'expired_at' => 'nullable|date',
            'status' => 'required|in:available,used,expired',
        ]);

        $voucher->update($validated);

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->status === 'used') {
            return redirect()->route('vouchers.index')
                ->with('error', 'Voucher yang sudah digunakan tidak bisa dihapus.');
        }

        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }

    /**
     * Show redeem voucher page
     */
    public function redeemForm()
    {
        return Inertia::render('Admin/Vouchers/Redeem');
    }

    /**
     * Redeem voucher for student
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'student_id' => 'required|exists:students,id',
        ]);

        try {
            DB::beginTransaction();

            // Find voucher
            $voucher = Voucher::where('code', $validated['code'])->first();

            if (!$voucher) {
                throw new \Exception('Kode voucher tidak ditemukan.');
            }

            if ($voucher->status !== 'available') {
                throw new \Exception('Voucher sudah digunakan atau expired.');
            }

            if ($voucher->expired_at && $voucher->expired_at < now()) {
                $voucher->update(['status' => 'expired']);
                throw new \Exception('Voucher sudah kadaluarsa.');
            }

            // Get student
            $student = Student::find($validated['student_id']);

            // Add balance to student
            $student->increment('balance', $voucher->nominal);
            $student->refresh();

            // Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'student_id' => $student->id,
                'used_by' => auth()->id(),
                'used_at' => now(),
            ]);

            // Create transaction record
            Transaction::create([
                'student_id' => $student->id,
                'type' => 'top-up',
                'amount' => $voucher->nominal,
                'description' => 'Top-up via voucher: ' . $voucher->code,
                'ending_balance' => $student->balance,
                'reference_id' => $voucher->id,
                'reference_type' => 'voucher',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil di-redeem',
                'student' => $student->load('user'),
                'voucher' => $voucher,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Search student for redeem
     */
    public function searchStudent(Request $request)
    {
        $query = Student::with('user');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nis', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
        }

        $students = $query->limit(10)->get();

        return response()->json(['students' => $students]);
    }
}
