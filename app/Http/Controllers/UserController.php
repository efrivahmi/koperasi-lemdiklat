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
                ['key' => 'products.show',   'label' => 'Lihat Detail Produk'],
                ['key' => 'products.create', 'label' => 'Tambah Produk Baru'],
                ['key' => 'products.edit',   'label' => 'Edit Produk'],
                ['key' => 'products.delete', 'label' => 'Hapus Produk'],
                ['key' => 'products.stock',  'label' => 'Sesuaikan Stok (Opname)'],
                ['key' => 'products.barcode','label' => 'Cetak Barcode'],
            ],
            'Master Data: Kategori' => [
                ['key' => 'categories.view',   'label' => 'Lihat Kategori'],
                ['key' => 'categories.show',   'label' => 'Lihat Detail Kategori'],
                ['key' => 'categories.create', 'label' => 'Tambah Kategori'],
                ['key' => 'categories.edit',   'label' => 'Edit Kategori'],
                ['key' => 'categories.delete', 'label' => 'Hapus Kategori'],
            ],
            'Data Siswa & Guru' => [
                ['key' => 'students.view',   'label' => 'Lihat Data Siswa'],
                ['key' => 'students.show',   'label' => 'Lihat Detail Siswa'],
                ['key' => 'students.cards',  'label' => 'Cetak Kartu Siswa (Batch)'],
                ['key' => 'students.create', 'label' => 'Tambah Siswa'],
                ['key' => 'students.edit',   'label' => 'Edit Siswa'],
                ['key' => 'students.delete', 'label' => 'Hapus Siswa'],
                ['key' => 'teachers.view',   'label' => 'Lihat Data Guru'],
                ['key' => 'teachers.show',   'label' => 'Lihat Detail Guru'],
                ['key' => 'teachers.create', 'label' => 'Tambah Guru'],
                ['key' => 'teachers.edit',   'label' => 'Edit Guru'],
                ['key' => 'teachers.delete', 'label' => 'Hapus Guru'],
            ],
            'Inventori (Stok Masuk)' => [
                ['key' => 'stock_ins.view',   'label' => 'Lihat Riwayat Stok Masuk'],
                ['key' => 'stock_ins.show',   'label' => 'Lihat Detail Stok Masuk'],
                ['key' => 'stock_ins.create', 'label' => 'Catat Stok Masuk'],
                ['key' => 'stock_ins.edit',   'label' => 'Edit Stok Masuk'],
                ['key' => 'stock_ins.delete', 'label' => 'Hapus Data Stok Masuk'],
            ],
            'Voucher' => [
                ['key' => 'vouchers.view',    'label' => 'Lihat Daftar Voucher'],
                ['key' => 'vouchers.show',    'label' => 'Lihat Detail Voucher'],
                ['key' => 'vouchers.create',  'label' => 'Generate Voucher'],
                ['key' => 'vouchers.edit',    'label' => 'Edit Voucher'],
                ['key' => 'vouchers.print',   'label' => 'Cetak Voucher'],
                ['key' => 'vouchers.redeem',  'label' => 'Redeem Voucher'],
                ['key' => 'vouchers.delete',  'label' => 'Hapus Voucher'],
            ],
            'Tabungan (Savings)' => [
                ['key' => 'savings.view',     'label' => 'Lihat Daftar Tabungan'],
                ['key' => 'savings.show',     'label' => 'Lihat Detail Tabungan'],
                ['key' => 'savings.manage',   'label' => 'Kelola Tabungan (Setor/Tarik)'],
            ],
            'Transaksi (Kasir)' => [
                ['key' => 'pos.access',           'label' => 'Akses Mesin Kasir (POS)'],
                ['key' => 'transactions.history', 'label' => 'Lihat Riwayat Transaksi'],
                ['key' => 'transactions.topup',   'label' => 'Top-up Saldo Manual'],
                ['key' => 'transactions.void',    'label' => 'Batalkan/Hapus Transaksi'],
            ],
            'Pengeluaran (Expenses)' => [
                ['key' => 'expenses.view',    'label' => 'Lihat Data Pengeluaran'],
                ['key' => 'expenses.show',    'label' => 'Lihat Detail Pengeluaran'],
                ['key' => 'expenses.create',  'label' => 'Catat Pengeluaran'],
                ['key' => 'expenses.edit',    'label' => 'Edit Pengeluaran'],
                ['key' => 'expenses.delete',  'label' => 'Hapus Pengeluaran'],
            ],
            'Laporan & Keuangan' => [
                ['key' => 'reports.sales',                    'label' => 'Laporan Penjualan'],
                ['key' => 'reports.inventory',                'label' => 'Laporan Inventori'],
                ['key' => 'reports.stock_adjustments',        'label' => 'Laporan Penyesuaian Stok'],
                ['key' => 'reports.stock_adjustments.edit',   'label' => 'Edit Penyesuaian Stok'],
                ['key' => 'reports.stock_adjustments.delete', 'label' => 'Hapus Penyesuaian Stok'],
                ['key' => 'reports.financial',                'label' => 'Laporan Keuangan'],
                ['key' => 'reports.student_transactions',     'label' => 'Laporan Transaksi Siswa'],
                ['key' => 'reports.print',                    'label' => 'Cetak Thermal Laporan'],
                ['key' => 'reports.export',                   'label' => 'Export Laporan (Excel)'],
            ],
            'Manajemen User' => [
                ['key' => 'users.view',   'label' => 'Lihat Daftar User'],
                ['key' => 'users.show',   'label' => 'Lihat Detail User'],
                ['key' => 'users.create', 'label' => 'Tambah User'],
                ['key' => 'users.edit',   'label' => 'Edit User'],
                ['key' => 'users.delete', 'label' => 'Hapus User'],
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
        if (in_array($validated['role'], ['admin', 'kasir']) && !empty($validated['permissions'])) {
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
        if ($request->has('permissions') && in_array($validated['role'], ['admin', 'kasir'])) {
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