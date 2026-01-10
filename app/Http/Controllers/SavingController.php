<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SavingController extends Controller
{
    /**
     * Display a listing of savings transactions
     */
    public function index(Request $request)
    {
        $query = Saving::with(['saver', 'processedBy', 'creator', 'updater']);

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by saver type (student/teacher)
        if ($request->has('saver_type') && $request->saver_type !== 'all') {
            if ($request->saver_type === 'student') {
                $query->where('saver_type', 'App\Models\Student');
            } elseif ($request->saver_type === 'teacher') {
                $query->where('saver_type', 'App\Models\Teacher');
            }
        }

        // Search by saver name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHasMorph('saver', [Student::class, Teacher::class], function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%')
                  ->orWhere('nip', 'like', '%' . $search . '%');
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $savings = $query->latest('transaction_date')->paginate(15);

        // Calculate totals
        $totalDeposits = Saving::where('type', 'deposit')->sum('amount');
        $totalWithdrawals = Saving::where('type', 'withdrawal')->sum('amount');
        $netSavings = $totalDeposits - $totalWithdrawals;

        return Inertia::render('Admin/Savings/Index', [
            'savings' => $savings,
            'filters' => $request->only(['search', 'type', 'saver_type', 'date_from', 'date_to']),
            'stats' => [
                'total_deposits' => $totalDeposits,
                'total_withdrawals' => $totalWithdrawals,
                'net_savings' => $netSavings,
            ]
        ]);
    }

    /**
     * Show form for creating a new savings transaction
     */
    public function create(Request $request)
    {
        // Load students and teachers for selection
        $students = Student::with('user')->orderBy('name')->get();
        $teachers = Teacher::with('user')->orderBy('name')->get();

        return Inertia::render('Admin/Savings/Create', [
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a new savings transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'saver_type' => 'required|string|in:student,teacher',
            'saver_id' => 'required|integer',
            'type' => 'required|string|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Get the saver (Student or Teacher)
            if ($validated['saver_type'] === 'student') {
                $saver = Student::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Student';
            } else {
                $saver = Teacher::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Teacher';
            }

            $currentBalance = $saver->balance ?? 0;
            $amount = $validated['amount'];

            // Calculate new balance
            if ($validated['type'] === 'deposit') {
                $newBalance = $currentBalance + $amount;
            } else {
                // Withdrawal
                if ($currentBalance < $amount) {
                    return back()->withErrors(['amount' => 'Saldo tidak mencukupi untuk penarikan']);
                }
                $newBalance = $currentBalance - $amount;
            }

            // Update saver balance
            $saver->update(['balance' => $newBalance]);

            // Create savings transaction record
            Saving::create([
                'saver_type' => $saverModel,
                'saver_id' => $saver->id,
                'type' => $validated['type'],
                'amount' => $amount,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => $validated['description'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            $message = $validated['type'] === 'deposit' ? 'Setoran' : 'Penarikan';
            return redirect()->route('savings.index')
                ->with('success', $message . ' tabungan berhasil dicatat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show savings history for a specific saver
     */
    public function show(Request $request, $saverType, $saverId)
    {
        // Determine saver model
        if ($saverType === 'student') {
            $saver = Student::with('user')->findOrFail($saverId);
            $saverModel = 'App\Models\Student';
        } else {
            $saver = Teacher::with('user')->findOrFail($saverId);
            $saverModel = 'App\Models\Teacher';
        }

        // Get savings history
        $savings = Saving::where('saver_type', $saverModel)
            ->where('saver_id', $saverId)
            ->with(['processedBy', 'creator', 'updater'])
            ->latest('transaction_date')
            ->paginate(20);

        // Calculate totals for this saver
        $totalDeposits = Saving::where('saver_type', $saverModel)
            ->where('saver_id', $saverId)
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawals = Saving::where('saver_type', $saverModel)
            ->where('saver_id', $saverId)
            ->where('type', 'withdrawal')
            ->sum('amount');

        return Inertia::render('Admin/Savings/Show', [
            'saver' => $saver,
            'saver_type' => $saverType,
            'savings' => $savings,
            'stats' => [
                'total_deposits' => $totalDeposits,
                'total_withdrawals' => $totalWithdrawals,
                'current_balance' => $saver->balance ?? 0,
            ]
        ]);
    }

    /**
     * Quick deposit (API endpoint)
     */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'saver_type' => 'required|string|in:student,teacher',
            'saver_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get the saver
            if ($validated['saver_type'] === 'student') {
                $saver = Student::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Student';
            } else {
                $saver = Teacher::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Teacher';
            }

            $currentBalance = $saver->balance ?? 0;
            $newBalance = $currentBalance + $validated['amount'];

            // Update balance
            $saver->update(['balance' => $newBalance]);

            // Create transaction record
            Saving::create([
                'saver_type' => $saverModel,
                'saver_id' => $saver->id,
                'type' => 'deposit',
                'amount' => $validated['amount'],
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => $validated['description'] ?? 'Setoran tabungan',
                'transaction_date' => now(),
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran berhasil',
                'balance' => $newBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick withdrawal (API endpoint)
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'saver_type' => 'required|string|in:student,teacher',
            'saver_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get the saver
            if ($validated['saver_type'] === 'student') {
                $saver = Student::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Student';
            } else {
                $saver = Teacher::lockForUpdate()->findOrFail($validated['saver_id']);
                $saverModel = 'App\Models\Teacher';
            }

            $currentBalance = $saver->balance ?? 0;

            // Check if sufficient balance
            if ($currentBalance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi'
                ], 422);
            }

            $newBalance = $currentBalance - $validated['amount'];

            // Update balance
            $saver->update(['balance' => $newBalance]);

            // Create transaction record
            Saving::create([
                'saver_type' => $saverModel,
                'saver_id' => $saver->id,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => $validated['description'] ?? 'Penarikan tabungan',
                'transaction_date' => now(),
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil',
                'balance' => $newBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
