<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * DEFINISI HAK AKSES GRANULAR (Helper Function)
     * Fungsi ini hanya mereturn data Array, bukan View.
     */
    private function getGranularPermissions()
    {
        return [
            'Master Data: Produk' => [
                ['key' => 'products.view',   'label' => 'Lihat Daftar Produk'],
                ['key' => 'products.create', 'label' => 'Tambah Produk Baru'],
                ['key' => 'products.edit',   'label' => 'Edit Produk'],
                ['key' => 'products.delete', 'label' => 'Hapus Produk'],
                ['key' => 'products.stock',  'label' => 'Sesuaikan Stok (Opname)'],
                ['key' => 'products.barcode','label' => 'Cetak Barcode'],
            ],
            'Master Data: Kategori' => [
                ['key' => 'categories.view',   'label' => 'Lihat Kategori'],
                ['key' => 'categories.create', 'label' => 'Tambah/Edit Kategori'],
                ['key' => 'categories.delete', 'label' => 'Hapus Kategori'],
            ],
            'Data Siswa & Guru' => [
                ['key' => 'students.view',   'label' => 'Lihat Data Siswa'],
                ['key' => 'students.manage', 'label' => 'Kelola Siswa (Add/Edit/Del)'],
                ['key' => 'teachers.view',   'label' => 'Lihat Data Guru'],
                ['key' => 'teachers.manage', 'label' => 'Kelola Guru'],
            ],
            'Transaksi (Kasir)' => [
                ['key' => 'pos.access',           'label' => 'Akses Mesin Kasir (POS)'],
                ['key' => 'transactions.history', 'label' => 'Lihat Riwayat Transaksi'],
                ['key' => 'transactions.void',    'label' => 'Batalkan/Hapus Transaksi'],
            ],
            'Laporan & Keuangan' => [
                ['key' => 'reports.view',    'label' => 'Lihat Laporan Penjualan'],
                ['key' => 'reports.download','label' => 'Download Laporan (Excel/PDF)'],
            ],
            'Manajemen User' => [
                ['key' => 'users.view',   'label' => 'Lihat Daftar User'],
                ['key' => 'users.manage', 'label' => 'Tambah/Edit User Lain'],
            ]
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Hanya tampilkan Admin dan Kasir (exclude Siswa/Guru jika perlu)
        $query = User::with(['creator', 'updater'])
                     ->whereIn('role', ['admin', 'kasir']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->oldest()->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * (METHOD INI YANG SEBELUMNYA HILANG/SALAH NAMA)
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            // Kita panggil helper permissions di sini
            'availablePermissions' => $this->getGranularPermissions()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:admin,kasir',
            'permissions' => 'nullable|array', 
        ]);

        // Proses Permission: ubah dari ['key1', 'key2'] jadi ['key1'=>true, 'key2'=>true]
        $permissionsToSave = [];
        if ($validated['role'] === 'kasir' && !empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $perm) {
                $permissionsToSave[$perm] = true;
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => $permissionsToSave,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Prevent showing student accounts logic (jika diperlukan)
        if ($user->role === 'siswa') {
            return redirect()->route('users.index');
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load(['creator', 'updater']),
            'availablePermissions' => $this->getGranularPermissions(),
            'currentPermissions' => $user->permissions ? array_keys($user->permissions) : []
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if ($user->role === 'siswa') {
            return redirect()->route('users.index')->with('error', 'Edit siswa melalui menu Siswa.');
        }

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'availablePermissions' => $this->getGranularPermissions(),
            'currentPermissions' => $user->permissions ? array_keys($user->permissions) : []
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->role === 'siswa') abort(404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:admin,kasir',
            'permissions' => 'nullable|array',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Proses Permission Update
        $permissionsToSave = [];
        if ($request->has('permissions') && $validated['role'] === 'kasir') {
            foreach ($request->permissions as $perm) {
                $permissionsToSave[$perm] = true;
            }
        }
        $userData['permissions'] = $permissionsToSave;

        $user->update($userData);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        if ($user->role === 'siswa') {
             return back()->with('error', 'Akun siswa tidak dapat dihapus melalui halaman ini.');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}