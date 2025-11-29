<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Only show admin and staff users (not students)
        $query = User::whereIn('role', ['admin', 'kasir']);

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
     */
    public function create()
    {
        // Define available permissions for staff (kasir role)
        // Simplified: Permission per Module/Feature (includes all actions)
        $availablePermissions = [
            // Master Data Modules
            'module_categories' => 'Kelola Kategori Produk (Lihat, Tambah, Edit, Hapus)',
            'module_products' => 'Kelola Produk (Lihat, Tambah, Edit, Hapus, Generate Barcode, Cetak Barcode)',
            'module_students' => 'Kelola Data Siswa (Lihat, Tambah, Edit, Hapus, Cetak Kartu, RFID)',

            // Inventory & Finance Modules
            'module_stock_ins' => 'Kelola Stok Masuk (Lihat, Tambah, Edit, Hapus)',
            'module_vouchers' => 'Kelola Voucher (Lihat, Buat, Edit, Hapus, Redeem, Cetak)',
            'module_expenses' => 'Kelola Pengeluaran (Lihat, Tambah, Edit, Hapus)',
            'module_transactions' => 'Kelola Transaksi Siswa (Lihat, Top-up Saldo, Detail Transaksi)',

            // POS Module
            'module_pos' => 'Point of Sale / Kasir (Proses Penjualan, Void Transaksi, Cetak Struk)',

            // Reports Modules
            'module_reports_sales' => 'Laporan Penjualan (Lihat, Export Excel/PDF)',
            'module_reports_inventory' => 'Laporan Inventori (Lihat, Export Excel/PDF)',
            'module_reports_stock_adjustments' => 'Laporan Penyesuaian Stok (Lihat, Export Excel/PDF)',
            'module_reports_financial' => 'Laporan Keuangan (Lihat, Export Excel/PDF)',
            'module_reports_student_transactions' => 'Laporan Transaksi Siswa (Lihat, Export Excel/PDF)',

            // User Management Module
            'module_users' => 'Kelola Pengguna/Staf (Lihat, Tambah, Edit, Hapus, Kelola Hak Akses)',
        ];

        return Inertia::render('Admin/Users/Create', [
            'availablePermissions' => $availablePermissions
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
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,kasir',
            'permissions' => 'nullable|array',
        ]);

        // Handle permissions
        $permissions = [];
        if ($validated['role'] === 'kasir' && !empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permission) {
                $permissions[$permission] = true;
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => $permissions,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Prevent showing student accounts
        if ($user->role === 'siswa') {
            abort(404);
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Prevent editing student accounts
        if ($user->role === 'siswa') {
            abort(404);
        }

        // Define available permissions for staff (kasir role)
        // Simplified: Permission per Module/Feature (includes all actions)
        $availablePermissions = [
            // Master Data Modules
            'module_categories' => 'Kelola Kategori Produk (Lihat, Tambah, Edit, Hapus)',
            'module_products' => 'Kelola Produk (Lihat, Tambah, Edit, Hapus, Generate Barcode, Cetak Barcode)',
            'module_students' => 'Kelola Data Siswa (Lihat, Tambah, Edit, Hapus, Cetak Kartu, RFID)',

            // Inventory & Finance Modules
            'module_stock_ins' => 'Kelola Stok Masuk (Lihat, Tambah, Edit, Hapus)',
            'module_vouchers' => 'Kelola Voucher (Lihat, Buat, Edit, Hapus, Redeem, Cetak)',
            'module_expenses' => 'Kelola Pengeluaran (Lihat, Tambah, Edit, Hapus)',
            'module_transactions' => 'Kelola Transaksi Siswa (Lihat, Top-up Saldo, Detail Transaksi)',

            // POS Module
            'module_pos' => 'Point of Sale / Kasir (Proses Penjualan, Void Transaksi, Cetak Struk)',

            // Reports Modules
            'module_reports_sales' => 'Laporan Penjualan (Lihat, Export Excel/PDF)',
            'module_reports_inventory' => 'Laporan Inventori (Lihat, Export Excel/PDF)',
            'module_reports_stock_adjustments' => 'Laporan Penyesuaian Stok (Lihat, Export Excel/PDF)',
            'module_reports_financial' => 'Laporan Keuangan (Lihat, Export Excel/PDF)',
            'module_reports_student_transactions' => 'Laporan Transaksi Siswa (Lihat, Export Excel/PDF)',

            // User Management Module
            'module_users' => 'Kelola Pengguna/Staf (Lihat, Tambah, Edit, Hapus, Kelola Hak Akses)',
        ];

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'availablePermissions' => $availablePermissions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating student accounts
        if ($user->role === 'siswa') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,kasir',
            'permissions' => 'nullable|array',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Handle permissions
        $permissions = [];
        if ($validated['role'] === 'kasir' && !empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permission) {
                $permissions[$permission] = true;
            }
        }
        $updateData['permissions'] = $permissions;

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting student accounts
        if ($user->role === 'siswa') {
            return redirect()->route('users.index')
                ->with('error', 'Akun siswa tidak dapat dihapus melalui halaman ini. Silakan hapus melalui menu Manajemen Siswa.');
        }

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Check if user has related data
        try {
            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Gagal menghapus pengguna. Error: ' . $e->getMessage());
        }
    }
}
