<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Student;
use App\Models\Teacher;
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

            $transactions = $query->latest()->paginate(15);

            // Get statistics
            $stats = [
                'total_manual' => Transaction::where('transaction_method', 'manual')->count(),
                'total_rfid' => Transaction::where('transaction_method', 'rfid')->count(),
                'total_barcode' => Transaction::where('transaction_method', 'barcode')->count(),
                'total_voucher' => Transaction::where('transaction_method', 'voucher')->count(),
                'total_system' => Transaction::where('transaction_method', 'system')->count(),
                'summary_topup' => Transaction::where('type', 'topup')->sum('amount'),
                'summary_purchase' => Transaction::where('type', 'purchase')->sum('amount'),
                'summary_voucher' => Transaction::where('type', 'redeem')->sum('amount'),
            ];

            \Log::info('Transaction index loaded', [
                'total' => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'filters' => $request->only(['search', 'type'])
            ]);

            // Check for Kasir route
            if ($request->routeIs('kasir.*')) {
                return Inertia::render('Kasir/Transactions/Index', [
                    'transactions' => $transactions,
                    'filters' => $request->only(['search', 'type', 'transaction_method']),
                    'stats' => $stats
                ]);
            }

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
        $minTopup = config('business.balance.min_topup', 1000);
        $maxTopup = config('business.balance.max_topup', 10000000);

        $validated = $request->validate([
            'buyer_type' => 'nullable|in:student,teacher',
            'buyer_id' => 'nullable|required_with:buyer_type|integer',
            'student_id' => 'nullable|exists:students,id', // backward compatibility
            'amount' => "required|numeric|min:{$minTopup}|max:{$maxTopup}",
            'description' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Jumlah top-up harus diisi',
            'amount.min' => 'Minimal top-up adalah Rp ' . number_format($minTopup, 0, ',', '.'),
            'amount.max' => 'Maksimal top-up adalah Rp ' . number_format($maxTopup, 0, ',', '.'),
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $buyer = null;
                $buyerModel = null;

                // Support both new polymorphic and old student_id
                if (!empty($validated['buyer_type']) && !empty($validated['buyer_id'])) {
                    if ($validated['buyer_type'] === 'student') {
                        $buyer = Student::where('id', $validated['buyer_id'])->lockForUpdate()->first();
                        $buyerModel = 'App\Models\Student';
                    } else {
                        $buyer = Teacher::where('id', $validated['buyer_id'])->lockForUpdate()->first();
                        $buyerModel = 'App\Models\Teacher';
                    }
                } elseif (!empty($validated['student_id'])) {
                    // Backward compatibility
                    $buyer = Student::where('id', $validated['student_id'])->lockForUpdate()->first();
                    $buyerModel = 'App\Models\Student';
                }

                if (!$buyer) {
                    throw new \Exception("Data pembeli tidak ditemukan");
                }

                // Validate amount
                if ($validated['amount'] <= 0) {
                    throw new \Exception("Jumlah top-up harus lebih dari 0");
                }

                // Check max balance
                $maxBalance = config('business.balance.max_balance', 100000000);
                if (($buyer->balance + $validated['amount']) > $maxBalance) {
                    throw new \Exception("Saldo akan melebihi batas maksimal (Rp " . number_format($maxBalance, 0, ',', '.') . ")");
                }

                $balanceBefore = $buyer->balance;

                // Add balance
                $buyer->increment('balance', $validated['amount']);
                $buyer->refresh();

                // Create transaction record
                $transactionData = [
                    'buyer_type' => $buyerModel,
                    'buyer_id' => $buyer->id,
                    'type' => 'topup',
                    'transaction_method' => 'manual',
                    'amount' => $validated['amount'],
                    'description' => $validated['description'] ?? 'Top-up saldo oleh ' . auth()->user()->name,
                    'ending_balance' => $buyer->balance,
                    'reference_type' => 'manual_topup',
                ];

                // Keep student_id for backward compatibility
                if ($buyerModel === 'App\Models\Student') {
                    $transactionData['student_id'] = $buyer->id;
                }

                Transaction::create($transactionData);

                \Log::info('Top-up successful', [
                    'buyer_type' => $buyerModel,
                    'buyer_id' => $buyer->id,
                    'buyer_name' => $buyer->user->name ?? $buyer->name,
                    'amount' => $validated['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $buyer->balance,
                    'processed_by' => auth()->user()->name,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Top-up berhasil diproses',
                    'buyer' => $buyer->load('user'),
                    'balance_before' => $balanceBefore,
                    'balance_after' => $buyer->balance,
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
                'buyer_type' => $validated['buyer_type'] ?? null,
                'buyer_id' => $validated['buyer_id'] ?? null,
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
            ->latest()
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

    /**
     * Search teacher for topup
     */
    public function searchTeacher(Request $request)
    {
        try {
            $search = $request->input('search', '');

            // Require at least 2 characters for search
            if (strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'teachers' => [],
                    'message' => 'Minimal 2 karakter untuk pencarian'
                ]);
            }

            $query = Teacher::with('user')
                ->where(function($q) use ($search) {
                    $q->where('nip', 'like', '%' . $search . '%')
                      ->orWhere('nuptk', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($q2) use ($search) {
                          $q2->where('name', 'like', '%' . $search . '%');
                      });
                });

            $teachers = $query->limit(10)->get();

            return response()->json([
                'success' => true,
                'teachers' => $teachers
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher search failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'teachers' => [],
                'message' => 'Pencarian gagal'
            ], 500);
        }
    }
}
