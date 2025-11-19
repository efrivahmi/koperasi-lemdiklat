<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nis', 'like', '%' . $search . '%')
                  ->orWhere('kelas', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
        }

        $students = $query->latest()->paginate(10);

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Students/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nis' => 'required|string|unique:students,nis',
            'kelas' => 'required|string|max:50',
            'balance' => 'nullable|numeric|min:0',
            'rfid_uid' => 'nullable|string|unique:students,rfid_uid',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'siswa',
        ]);

        // Handle photo upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('students', 'public');
        }

        // Create student profile
        Student::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'nis' => $validated['nis'],
            'kelas' => $validated['kelas'],
            'balance' => $validated['balance'] ?? 0,
            'rfid_uid' => $validated['rfid_uid'] ?? null,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return Inertia::render('Admin/Students/Show', [
            'student' => $student->load('user')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student->load('user')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user_id,
            'password' => 'nullable|string|min:8',
            'nis' => 'required|string|unique:students,nis,' . $student->id,
            'kelas' => 'required|string|max:50',
            'balance' => 'nullable|numeric|min:0',
            'rfid_uid' => 'nullable|string|unique:students,rfid_uid,' . $student->id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update user account
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $student->user->update($userData);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($student->foto) {
                Storage::disk('public')->delete($student->foto);
            }
            $validated['foto'] = $request->file('foto')->store('students', 'public');
        } else {
            $validated['foto'] = $student->foto;
        }

        // Update student profile
        $student->update([
            'nis' => $validated['nis'],
            'kelas' => $validated['kelas'],
            'balance' => $validated['balance'] ?? $student->balance,
            'rfid_uid' => $validated['rfid_uid'] ?? $student->rfid_uid,
            'foto' => $validated['foto'],
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        // Delete photo if exists
        if ($student->foto) {
            Storage::disk('public')->delete($student->foto);
        }

        // Delete student (user will be deleted via cascade)
        $userId = $student->user_id;
        $student->delete();
        User::find($userId)->delete();

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }

    /**
     * Show the RFID registration form
     */
    public function rfidRegister(Student $student)
    {
        return Inertia::render('Admin/Students/RfidRegister', [
            'student' => $student->load('user')
        ]);
    }

    /**
     * Register RFID card to student
     */
    public function rfidStore(Request $request, Student $student)
    {
        $validated = $request->validate([
            'rfid_uid' => 'required|string|unique:students,rfid_uid,' . $student->id,
        ]);

        $student->update([
            'rfid_uid' => $validated['rfid_uid'],
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Kartu RFID berhasil didaftarkan.');
    }
}
