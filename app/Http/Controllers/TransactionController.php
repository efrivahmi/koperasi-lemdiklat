<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        try {
            // Load student relation dengan handling untuk NULL student_id
            $query = Transaction::with(['student' => function($q) {
                $q->with('user');
            }, 'creator', 'updater']);

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('nis', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($q2) use ($search) {
                          $q2->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }

            if ($request->has('transaction_method') && !empty($request->transaction_method)) {
                $query->where('transaction_method', $request->transaction_method);
            }

            $transactions = $query->oldest()->paginate(15);

            // Get statistics
            $stats = [
                'total_manual' => Transaction::where('transaction_method', 'manual')->count(),
                'total_rfid' => Transaction::where('transaction_method', 'rfid')->count(),
                'total_barcode' => Transaction::where('transaction_method', 'barcode')->count(),
                'total_voucher' => Transaction::where('transaction_method', 'voucher')->count(),
                'total_system' => Transaction::where('transaction_method', 'system')->count(),
                'total_amount_manual' => Transaction::where('transaction_method', 'manual')->whereIn('type', ['topup', 'redeem', 'return'])->sum('amount'),
                'total_amount_automated' => Transaction::whereIn('transaction_method', ['rfid', 'barcode', 'voucher'])->whereIn('type', ['topup', 'redeem', 'return', 'purchase'])->sum('amount'),
            ];

            \Log::info('Transaction index loaded', [
                'total' => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'filters' => $request->only(['search', 'type'])
            ]);

            return Inertia::render('Admin/Transactions/Index', [
                'transactions' => $transactions,
                'filters' => $request->only(['search', 'type', 'transaction_method']),
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Transaction index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty data instead of error
            return Inertia::render('Admin/Transactions/Index', [
                'transactions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'filters' => $request->only(['search', 'type', 'transaction_method']),
                'stats' => [
                    'total_manual' => 0,
                    'total_rfid' => 0,
                    'total_barcode' => 0,
                    'total_voucher' => 0,
                    'total_system' => 0,
                    'total_amount_manual' => 0,
                    'total_amount_automated' => 0,
                ],
                'error' => 'Gagal memuat data transaksi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show form for manual top-up
     */
    public function topupForm()
    {
        return Inertia::render('Admin/Transactions/Topup');
    }

    /**
     * Process manual top-up
     */
    public function topup(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1000|max:10000000',
            'description' => 'nullable|string|max:255',
        ], [
            'student_id.required' => 'Silakan pilih siswa terlebih dahulu',
            'student_id.exists' => 'Data siswa tidak ditemukan',
            'amount.required' => 'Jumlah top-up harus diisi',
            'amount.min' => 'Minimal top-up adalah Rp 1.000',
            'amount.max' => 'Maksimal top-up adalah Rp 10.000.000',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Lock student row to prevent concurrent updates
                $student = Student::where('id', $validated['student_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$student) {
                    throw new \Exception("Data siswa tidak ditemukan");
                }

                // Validate amount is positive integer
                if ($validated['amount'] <= 0) {
                    throw new \Exception("Jumlah top-up harus lebih dari 0");
                }

                // Check if balance will exceed reasonable limit (safety check)
                $maxBalance = 50000000; // 50 juta
                if (($student->balance + $validated['amount']) > $maxBalance) {
                    throw new \Exception("Saldo siswa akan melebihi batas maksimal (Rp " . number_format($maxBalance, 0, ',', '.') . ")");
                }

                $balanceBefore = $student->balance;

                // Add balance
                $student->increment('balance', $validated['amount']);
                $student->refresh();

                // Create transaction record
                Transaction::create([
                    'student_id' => $student->id,
                    'type' => 'topup',
                    'transaction_method' => 'manual', // Manual top-up by admin/kasir
                    'amount' => $validated['amount'],
                    'description' => $validated['description'] ?? 'Top-up saldo oleh ' . auth()->user()->name,
                    'ending_balance' => $student->balance,
                    'reference_type' => 'manual_topup',
                ]);

                \Log::info('Top-up successful', [
                    'student_id' => $student->id,
                    'student_name' => $student->user->name,
                    'amount' => $validated['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $student->balance,
                    'processed_by' => auth()->user()->name,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Top-up berhasil diproses',
                    'student' => $student->load('user'),
                    'balance_before' => $balanceBefore,
                    'balance_after' => $student->balance,
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Top-up failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'student_id' => $validated['student_id'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get student transactions history
     */
    public function studentHistory($studentId)
    {
        $student = Student::with('user')->findOrFail($studentId);

        $transactions = Transaction::where('student_id', $studentId)
            ->oldest()
            ->paginate(20);

        return Inertia::render('Admin/Transactions/StudentHistory', [
            'student' => $student,
            'transactions' => $transactions
        ]);
    }

    /**
     * Search student for topup
     */
    public function searchStudent(Request $request)
    {
        try {
            $search = $request->input('search', '');

            // Require at least 2 characters for search
            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'students' => [],
                    'message' => 'Minimal 2 karakter untuk pencarian'
                ]);
            }

            $query = Student::with('user')
                ->where(function($q) use ($search) {
                    $q->where('nis', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($q2) use ($search) {
                          $q2->where('name', 'like', '%' . $search . '%');
                      });
                });

            $students = $query->limit(10)->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);

        } catch (\Exception $e) {
            \Log::error('Student search failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'students' => [],
                'message' => 'Pencarian gagal'
            ], 500);
        }
    }
}
