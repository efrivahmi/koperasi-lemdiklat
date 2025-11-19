<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentCardController extends Controller
{
    /**
     * Generate kartu siswa (printable HTML)
     */
    public function generate($id)
    {
        $student = Student::with('user')->findOrFail($id);

        return view('student-card', [
            'student' => $student,
            'school_config' => config('school'),
        ]);
    }

    /**
     * Generate multiple student cards
     */
    public function generateBatch(Request $request)
    {
        $studentIds = $request->input('student_ids', []);

        $students = Student::with('user')
            ->whereIn('id', $studentIds)
            ->get();

        return view('student-cards-batch', [
            'students' => $students,
            'school_config' => config('school'),
        ]);
    }

    /**
     * Download student data as JSON (for RFID registration)
     */
    public function exportForRfid($id)
    {
        $student = Student::with('user')->findOrFail($id);

        $data = [
            'id' => $student->id,
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'name' => $student->user->name,
            'kelas' => $student->kelas,
            'jenjang' => $student->jenjang,
            'rfid_uid' => $student->rfid_uid ?? 'BELUM_TERDAFTAR',
            'balance' => $student->balance,
        ];

        return response()->json($data);
    }
}
