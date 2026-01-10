<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'creator', 'updater']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nip', 'like', '%' . $search . '%')
                  ->orWhere('nuptk', 'like', '%' . $search . '%')
                  ->orWhere('jabatan', 'like', '%' . $search . '%')
                  ->orWhere('mata_pelajaran', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
        }

        $teachers = $query->oldest()->paginate(10);

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Teachers/Create');
    }

    /**
     * Generate unique RFID UID in hexadecimal format (8 digits)
     * Format: A1B2C3D4 (compatible with standard RFID readers)
     */
    private function generateRfidUid()
    {
        do {
            // Generate 8-digit hexadecimal (4 bytes = 32 bits)
            $rfidUid = strtoupper(bin2hex(random_bytes(4)));
        } while (
            Teacher::where('rfid_uid', $rfidUid)->exists() ||
            \App\Models\Student::where('rfid_uid', $rfidUid)->exists()
        );

        return $rfidUid;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'nip' => 'required|string|unique:teachers,nip',
            'nuptk' => 'nullable|string|unique:teachers,nuptk',
            'jabatan' => 'nullable|string|max:100',
            'mata_pelajaran' => 'nullable|string|max:100',
            'jenjang' => 'nullable|string|in:SMA Taruna Nusantara Indonesia,SMK Taruna Nusantara Jaya',
            'alamat_lengkap' => 'nullable|string',
            'balance' => 'nullable|numeric|min:0',
            'rfid_uid' => 'nullable|string|unique:teachers,rfid_uid',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'auto_generate_rfid' => 'nullable|boolean',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
        ]);

        // Handle photo upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('teachers', 'public');
        }

        // Auto-generate RFID UID if requested or if not provided
        $rfidUid = $validated['rfid_uid'] ?? null;
        if ($request->input('auto_generate_rfid', false) || empty($rfidUid)) {
            $rfidUid = $this->generateRfidUid();
        }

        // Create teacher profile
        Teacher::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'nip' => $validated['nip'],
            'nuptk' => $validated['nuptk'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'mata_pelajaran' => $validated['mata_pelajaran'] ?? null,
            'jenjang' => $validated['jenjang'] ?? null,
            'alamat_lengkap' => $validated['alamat_lengkap'] ?? null,
            'balance' => $validated['balance'] ?? 0,
            'rfid_uid' => $rfidUid,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('teachers.index')
            ->with('success', 'Guru berhasil ditambahkan dengan RFID UID: ' . $rfidUid);
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/Show', [
            'teacher' => $teacher->load('user')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher->load('user')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teacher->user_id,
            'password' => 'nullable|string|min:8',
            'nip' => 'required|string|unique:teachers,nip,' . $teacher->id,
            'nuptk' => 'nullable|string|unique:teachers,nuptk,' . $teacher->id,
            'jabatan' => 'nullable|string|max:100',
            'mata_pelajaran' => 'nullable|string|max:100',
            'jenjang' => 'nullable|string|in:SMA Taruna Nusantara Indonesia,SMK Taruna Nusantara Jaya',
            'alamat_lengkap' => 'nullable|string',
            'balance' => 'nullable|numeric|min:0',
            'rfid_uid' => 'nullable|string|unique:teachers,rfid_uid,' . $teacher->id,
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

        $teacher->user->update($userData);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($teacher->foto) {
                Storage::disk('public')->delete($teacher->foto);
            }
            $validated['foto'] = $request->file('foto')->store('teachers', 'public');
        } else {
            $validated['foto'] = $teacher->foto;
        }

        // Update teacher profile
        $teacher->update([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
            'nuptk' => $validated['nuptk'] ?? $teacher->nuptk,
            'jabatan' => $validated['jabatan'] ?? $teacher->jabatan,
            'mata_pelajaran' => $validated['mata_pelajaran'] ?? $teacher->mata_pelajaran,
            'jenjang' => $validated['jenjang'] ?? $teacher->jenjang,
            'alamat_lengkap' => $validated['alamat_lengkap'] ?? $teacher->alamat_lengkap,
            'balance' => $validated['balance'] ?? $teacher->balance,
            'rfid_uid' => $validated['rfid_uid'] ?? $teacher->rfid_uid,
            'foto' => $validated['foto'],
        ]);

        return redirect()->route('teachers.index')
            ->with('success', 'Guru berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        // Delete photo if exists
        if ($teacher->foto) {
            Storage::disk('public')->delete($teacher->foto);
        }

        // Delete teacher (user will be deleted via cascade)
        $userId = $teacher->user_id;
        $teacher->delete();
        User::find($userId)->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Guru berhasil dihapus.');
    }

    /**
     * Show the RFID registration form
     */
    public function rfidRegister(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/RfidRegister', [
            'teacher' => $teacher->load('user')
        ]);
    }

    /**
     * Register RFID card to teacher
     */
    public function rfidStore(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'rfid_uid' => 'required|string|unique:teachers,rfid_uid,' . $teacher->id,
        ]);

        $teacher->update([
            'rfid_uid' => $validated['rfid_uid'],
        ]);

        return redirect()->route('teachers.index')
            ->with('success', 'Kartu RFID berhasil didaftarkan.');
    }

    /**
     * Generate new RFID UID (API endpoint for frontend)
     */
    public function generateRfidApi()
    {
        $rfidUid = $this->generateRfidUid();

        return response()->json([
            'rfid_uid' => $rfidUid,
            'message' => 'RFID UID berhasil digenerate'
        ]);
    }
}
