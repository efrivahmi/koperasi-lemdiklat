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
        $query = Transaction::with('student.user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('nis', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate(15);

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'type'])
        ]);
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
            'amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $student = Student::find($validated['student_id']);

            // Add balance
            $student->increment('balance', $validated['amount']);
            $student->refresh();

            // Create transaction record
            Transaction::create([
                'student_id' => $student->id,
                'type' => 'top-up',
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? 'Top-up manual oleh admin',
                'ending_balance' => $student->balance,
                'reference_type' => 'manual_topup',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Top-up berhasil',
                'student' => $student->load('user'),
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
