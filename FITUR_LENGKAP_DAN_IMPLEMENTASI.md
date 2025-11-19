# 🚀 FITUR LENGKAP & IMPLEMENTASI
## Sistem Koperasi Lemdiklat Taruna Nusantara Indonesia

---

## 📋 DAFTAR ISI
1. [Thermal Printer Receipt](#1-thermal-printer-receipt)
2. [Audit Trail System](#2-audit-trail-system)
3. [Dashboard Profesional dengan Charts](#3-dashboard-profesional-dengan-charts)
4. [Role & Permission System](#4-role--permission-system)
5. [Error Handling Global](#5-error-handling-global)
6. [Real-time Stock Monitoring](#6-real-time-stock-monitoring)
7. [Checklist Implementasi](#7-checklist-implementasi)

---

## 1. 🖨️ THERMAL PRINTER RECEIPT

### **✅ SUDAH DIBUAT:**

**File:** `resources/views/receipt.blade.php`

**Fitur:**
- ✅ Format thermal printer 58mm (standard)
- ✅ Header: Logo & Info Sekolah
- ✅ Info transaksi lengkap (No, Tanggal, Kasir, Siswa)
- ✅ List items dengan harga
- ✅ Summary pembayaran (Tunai/Saldo)
- ✅ Saldo akhir siswa (untuk payment balance)
- ✅ Footer dengan info kontak
- ✅ Tombol Print built-in
- ✅ Auto-responsive untuk printer thermal

**Cara Menggunakan:**

#### **A. Route (Sudah ada):**
```php
Route::get('/pos/receipt/{sale}', [PosController::class, 'printReceipt'])->name('pos.receipt');
```

#### **B. Akses di Browser:**
```
http://localhost/kasir/pos/receipt/1
```
_(Ganti 1 dengan ID sale)_

#### **C. Auto-open Receipt setelah Checkout:**

Edit `resources/js/Pages/Kasir/Pos/Index.vue`:

```javascript
const processCheckout = async () => {
    // ... existing code ...

    try {
        const response = await axios.post('/kasir/api/checkout', {
            items: cart.value,
            payment_method: paymentMethod.value,
            student_id: paymentMethod.value === 'balance' ? selectedStudent.value.id : null,
            cash_amount: paymentMethod.value === 'cash' ? cashAmount.value : null,
        });

        if (response.data.success) {
            showNotification('Transaksi berhasil!', 'success');

            // AUTO-OPEN RECEIPT UNTUK PRINT
            const receiptUrl = response.data.receipt_url;
            window.open(receiptUrl, '_blank', 'width=400,height=600');

            // Reset keranjang
            cart.value = [];
            // ... rest of code
        }
    } catch (error) {
        // ... error handling
    }
};
```

#### **D. Setup Thermal Printer:**

**Windows:**
1. Install driver thermal printer (biasanya auto-detect)
2. Set printer sebagai "Default Printer"
3. Test print: Buka receipt → Ctrl+P
4. Pastikan ukuran kertas 58mm di printer settings

**Print Settings:**
- Paper Size: 58mm (atau Custom)
- Margins: 0mm (all sides)
- Scale: 100%
- Background graphics: ON

---

## 2. 📊 AUDIT TRAIL SYSTEM

### **✅ SUDAH DIBUAT:**

**1. Migration File:** `database/migrations/*_add_audit_fields_to_tables.php`

Menambahkan kolom `created_by` dan `updated_by` pada tabel:
- ✅ products
- ✅ sales
- ✅ stock_ins
- ✅ expenses
- ✅ students
- ✅ categories

**2. Auditable Trait:** `app/Traits/Auditable.php`

Auto-capture user yang create/update record.

### **CARA IMPLEMENTASI:**

#### **Step 1: Jalankan Migration**
```bash
cd "c:\Users\Administrator\Desktop\aplikasi koperasi\koperasi-lemdiklat"
php artisan migrate
```

#### **Step 2: Update Models**

Edit setiap model dan tambahkan trait:

**app/Models/Product.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Product extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'description',
        'harga_beli',
        'harga_jual',
        'stock',
        'image_path',
        'created_by',
        'updated_by',
    ];

    // Relationships tetap sama...
}
```

**Lakukan yang sama untuk:**
- `app/Models/Student.php`
- `app/Models/Category.php`
- `app/Models/Expense.php`
- `app/Models/StockIn.php`
- `app/Models/Sale.php`

#### **Step 3: Tampilkan Audit Info di View**

**Contoh di Products/Index.vue:**
```vue
<template>
    <!-- ... existing code ... -->

    <!-- Tambah kolom "Diubah Oleh" di tabel -->
    <thead>
        <tr>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Harga</th>
            <th>Diubah Oleh</th> <!-- NEW -->
            <th>Diubah Pada</th> <!-- NEW -->
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="product in products.data" :key="product.id">
            <td>{{ product.name }}</td>
            <td>{{ product.category?.name }}</td>
            <td>{{ product.stock }}</td>
            <td>{{ formatCurrency(product.harga_jual) }}</td>

            <!-- Audit Info -->
            <td v-if="product.updater">
                <div class="text-xs">
                    <div class="font-semibold">{{ product.updater.name }}</div>
                    <div class="text-gray-500">{{ product.updater.role }}</div>
                </div>
            </td>
            <td v-else>-</td>

            <td>
                <div class="text-xs text-gray-500">
                    {{ new Date(product.updated_at).toLocaleString('id-ID') }}
                </div>
            </td>

            <td><!-- Actions --></td>
        </tr>
    </tbody>
</template>
```

#### **Step 4: Update Controller untuk Load Audit Data**

**app/Http/Controllers/ProductController.php:**
```php
public function index(Request $request)
{
    $query = Product::with(['category', 'creator', 'updater']); // Add creator & updater

    if ($request->has('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $products = $query->latest()->paginate(10);

    return Inertia::render('Admin/Products/Index', [
        'products' => $products,
        'filters' => $request->only(['search'])
    ]);
}
```

### **Contoh Tampilan Audit Log:**

```
┌──────────────────────────────────────────────┐
│ Produk: Pulpen Pilot                         │
│ Stok: 50 → 45 (berkurang 5)                 │
│                                              │
│ Diubah oleh: John Doe (Kasir)               │
│ Waktu: 09 Nov 2025, 14:30 WIB               │
└──────────────────────────────────────────────┘
```

---

## 3. 📈 DASHBOARD PROFESIONAL DENGAN CHARTS

### **Dashboard Controller (Sudah Ada):**

File: `app/Http/Controllers/DashboardController.php`

Sudah menyediakan data:
- ✅ Today's revenue & transactions
- ✅ Monthly revenue, expenses, profit
- ✅ Products statistics (total, low stock, out of stock)
- ✅ Students statistics
- ✅ Vouchers statistics
- ✅ Recent transactions (10 latest)
- ✅ Top 5 selling products
- ✅ Sales chart data (last 7 days)

### **IMPLEMENTASI DASHBOARD VUE DENGAN CHARTS:**

#### **Step 1: Install Chart.js**
```bash
npm install chart.js vue-chartjs --save
```

#### **Step 2: Update Dashboard.vue**

File: `resources/js/Pages/Dashboard.vue` (Buat baru atau replace):

```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    topProducts: Array,
    lowStockList: Array,
    salesChart: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

// Sales Chart (Bar Chart)
onMounted(() => {
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: props.salesChart.map(item => item.date),
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: props.salesChart.map(item => item.revenue),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    // Stock Status Pie Chart
    const stockCtx = document.getElementById('stockChart');
    if (stockCtx) {
        const totalProducts = props.stats.totalProducts;
        const lowStock = props.stats.lowStockProducts;
        const outStock = props.stats.outOfStockProducts;
        const normalStock = totalProducts - lowStock - outStock;

        new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: ['Normal Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [normalStock, lowStock, outStock],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',   // Green
                        'rgba(251, 191, 36, 0.8)',  // Yellow
                        'rgba(239, 68, 68, 0.8)'    // Red
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Top Products Chart
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx) {
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: props.topProducts.map(p => p.name),
                datasets: [{
                    label: 'Total Terjual',
                    data: props.topProducts.map(p => p.total_sold),
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bar
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Koperasi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Today Revenue -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Revenue Hari Ini</p>
                                <p class="text-3xl font-bold mt-2">{{ formatCurrency(stats.todayRevenue) }}</p>
                                <p class="text-blue-100 text-xs mt-1">{{ stats.todayTransactions }} transaksi</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Revenue -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Revenue Bulan Ini</p>
                                <p class="text-3xl font-bold mt-2">{{ formatCurrency(stats.monthRevenue) }}</p>
                                <p class="text-green-100 text-xs mt-1">Net Profit: {{ formatCurrency(stats.netProfit) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Total Produk</p>
                                <p class="text-3xl font-bold mt-2">{{ formatNumber(stats.totalProducts) }}</p>
                                <p class="text-purple-100 text-xs mt-1">
                                    <span class="text-yellow-300">{{ stats.lowStockProducts }} low stock</span> •
                                    <span class="text-red-300">{{ stats.outOfStockProducts }} habis</span>
                                </p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Students -->
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-pink-100 text-sm font-medium">Total Siswa</p>
                                <p class="text-3xl font-bold mt-2">{{ formatNumber(stats.totalStudents) }}</p>
                                <p class="text-pink-100 text-xs mt-1">Saldo: {{ formatCurrency(stats.totalStudentBalance) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Sales Trend Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Trend Penjualan (7 Hari Terakhir)</h3>
                        <div style="height: 300px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                    <!-- Stock Status Pie Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📦 Status Stok</h3>
                        <div style="height: 300px;">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Products & Low Stock -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Selling Products -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏆 Produk Terlaris Bulan Ini</h3>
                        <div style="height: 300px;">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚠️ Stok Menipis</h3>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto">
                            <div v-if="lowStockList.length === 0" class="text-center text-gray-500 py-8">
                                Semua produk stok aman ✅
                            </div>
                            <div v-for="product in lowStockList" :key="product.id"
                                 class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ product.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.category?.name }}</p>
                                </div>
                                <div class="text-right">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        product.stock === 0 ? 'bg-red-600 text-white' : 'bg-yellow-500 text-white'
                                    ]">
                                        {{ product.stock }} unit
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🕐 Transaksi Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Siswa</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipe</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nominal</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="txn in recentTransactions" :key="txn.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ new Date(txn.created_at).toLocaleString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ txn.student?.user?.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ txn.student?.nis }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'px-2 py-1 rounded text-xs font-semibold',
                                            txn.type === 'top-up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ txn.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold" :class="txn.type === 'top-up' ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(txn.amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-blue-600">
                                        {{ formatCurrency(txn.ending_balance) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

## 4. 🔐 ROLE & PERMISSION SYSTEM

### **Struktur Role:**
- **Master** - Full access, manage semua user
- **Admin** - Manage produk, stock, voucher, siswa
- **Kasir** - Akses POS, lihat laporan, tidak bisa edit master data
- **Siswa** - Portal siswa (lihat saldo, history transaksi)

### **IMPLEMENTASI:**

#### **Step 1: Migration untuk Permission**

```bash
php artisan make:migration create_roles_and_permissions_tables
```

**File:** `database/migrations/*_create_roles_and_permissions_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'create-products', 'edit-students', dll
            $table->string('description');
            $table->string('group'); // 'products', 'students', 'reports'
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // 'master', 'admin', 'kasir', 'siswa'
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
```

#### **Step 2: Seeder untuk Permissions**

```bash
php artisan make:seeder PermissionSeeder
```

**File:** `database/seeders/PermissionSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Products
            ['name' => 'view-products', 'description' => 'Lihat daftar produk', 'group' => 'products'],
            ['name' => 'create-products', 'description' => 'Tambah produk baru', 'group' => 'products'],
            ['name' => 'edit-products', 'description' => 'Edit produk', 'group' => 'products'],
            ['name' => 'delete-products', 'description' => 'Hapus produk', 'group' => 'products'],

            // Students
            ['name' => 'view-students', 'description' => 'Lihat daftar siswa', 'group' => 'students'],
            ['name' => 'create-students', 'description' => 'Tambah siswa baru', 'group' => 'students'],
            ['name' => 'edit-students', 'description' => 'Edit data siswa', 'group' => 'students'],
            ['name' => 'delete-students', 'description' => 'Hapus siswa', 'group' => 'students'],
            ['name' => 'topup-students', 'description' => 'Top-up saldo siswa', 'group' => 'students'],

            // POS
            ['name' => 'access-pos', 'description' => 'Akses POS/Kasir', 'group' => 'pos'],
            ['name' => 'process-sales', 'description' => 'Proses transaksi penjualan', 'group' => 'pos'],

            // Reports
            ['name' => 'view-reports', 'description' => 'Lihat semua laporan', 'group' => 'reports'],
            ['name' => 'export-reports', 'description' => 'Export laporan ke Excel', 'group' => 'reports'],

            // Users
            ['name' => 'manage-users', 'description' => 'Manage users & roles', 'group' => 'users'],

            // Settings
            ['name' => 'manage-settings', 'description' => 'Manage system settings', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Assign permissions to roles
        $rolePermissions = [
            'master' => 'all', // Master gets all permissions
            'admin' => [
                'view-products', 'create-products', 'edit-products', 'delete-products',
                'view-students', 'create-students', 'edit-students', 'delete-students', 'topup-students',
                'access-pos', 'process-sales',
                'view-reports', 'export-reports',
            ],
            'kasir' => [
                'view-products',
                'view-students',
                'access-pos', 'process-sales',
                'view-reports',
            ],
            'siswa' => [
                // Students only see their own portal
            ],
        ];

        $allPermissions = DB::table('permissions')->pluck('id', 'name');

        foreach ($rolePermissions as $role => $perms) {
            if ($perms === 'all') {
                foreach ($allPermissions as $permId) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                foreach ($perms as $permName) {
                    if (isset($allPermissions[$permName])) {
                        DB::table('role_permissions')->insert([
                            'role' => $role,
                            'permission_id' => $allPermissions[$permName],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
```

#### **Step 3: Middleware untuk Check Permission**

```bash
php artisan make:middleware CheckPermission
```

**File:** `app/Http/Middleware/CheckPermission.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // Master role bypasses all checks
        if ($user->role === 'master') {
            return $next($request);
        }

        // Get user's permissions
        $userPermissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role', $user->role)
            ->pluck('permissions.name')
            ->toArray();

        // Check if user has required permission
        foreach ($permissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
            }
        }

        return $next($request);
    }
}
```

#### **Step 4: Register Middleware**

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

#### **Step 5: Gunakan di Routes**

**File:** `routes/web.php`

```php
// Admin Routes - Butuh permission
Route::middleware(['auth', 'permission:create-products'])->group(function () {
    Route::post('admin/products', [ProductController::class, 'store']);
});

Route::middleware(['auth', 'permission:edit-products'])->group(function () {
    Route::put('admin/products/{product}', [ProductController::class, 'update']);
});

Route::middleware(['auth', 'permission:access-pos'])->group(function () {
    Route::get('/kasir/pos', [PosController::class, 'index']);
});

// Master only - Manage Users
Route::middleware(['auth', 'role:master'])->group(function () {
    Route::resource('admin/users', UserController::class);
});
```

---

## 5. 🚨 ERROR HANDLING GLOBAL

### **Implementasi:**

#### **Step 1: Create Global Exception Handler**

**File:** `app/Exceptions/Handler.php` (Update existing)

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log semua errors ke file
            \Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        // Model Not Found
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                    'error' => 'MODEL_NOT_FOUND'
                ], 404);
            }

            return Inertia::render('Errors/404', [
                'message' => 'Data yang Anda cari tidak ditemukan.'
            ])->toResponse($request)->setStatusCode(404);
        });

        // 404 Not Found
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Halaman tidak ditemukan.',
                    'error' => 'NOT_FOUND'
                ], 404);
            }

            return Inertia::render('Errors/404', [
                'message' => 'Halaman yang Anda cari tidak ditemukan.'
            ])->toResponse($request)->setStatusCode(404);
        });

        // Validation Exception
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang Anda masukkan tidak valid.',
                    'errors' => $e->errors()
                ], 422);
            }

            // For Inertia, validation errors are handled automatically
        });

        // Authentication Exception
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu.',
                    'error' => 'UNAUTHENTICATED'
                ], 401);
            }

            return redirect()->guest(route('login'));
        });

        // Database Query Exception
        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            \Log::error('Database Error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada database.',
                    'error' => 'DATABASE_ERROR'
                ], 500);
            }

            return Inertia::render('Errors/500', [
                'message' => 'Terjadi kesalahan pada database. Silakan hubungi administrator.'
            ])->toResponse($request)->setStatusCode(500);
        });

        // Generic Exception
        $this->renderable(function (Throwable $e, $request) {
            // Jangan tangkap exception di development
            if (config('app.debug')) {
                return null; // Biarkan Laravel handle
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada sistem.',
                    'error' => 'INTERNAL_ERROR'
                ], 500);
            }

            return Inertia::render('Errors/500', [
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.'
            ])->toResponse($request)->setStatusCode(500);
        });
    }
}
```

#### **Step 2: Create Error Pages**

**File:** `resources/js/Pages/Errors/404.vue`

```vue
<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String
});
</script>

<template>
    <Head title="404 - Tidak Ditemukan" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <div class="text-9xl font-bold text-gray-300">404</div>
            <h1 class="mt-4 text-3xl font-bold text-gray-800">Halaman Tidak Ditemukan</h1>
            <p class="mt-2 text-gray-600">{{ message || 'Halaman yang Anda cari tidak ditemukan.' }}</p>
            <Link :href="route('dashboard')" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Kembali ke Dashboard
            </Link>
        </div>
    </div>
</template>
```

**File:** `resources/js/Pages/Errors/500.vue`

```vue
<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String
});
</script>

<template>
    <Head title="500 - Server Error" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <div class="text-9xl font-bold text-red-300">500</div>
            <h1 class="mt-4 text-3xl font-bold text-gray-800">Terjadi Kesalahan</h1>
            <p class="mt-2 text-gray-600">{{ message || 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' }}</p>
            <Link :href="route('dashboard')" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Kembali ke Dashboard
            </Link>
        </div>
    </div>
</template>
```

---

## 6. 📡 REAL-TIME STOCK MONITORING

### **Implementasi dengan Polling:**

**File:** `resources/js/Components/StockMonitor.vue`

```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const products = ref([]);
const lowStockCount = ref(0);
let intervalId = null;

const fetchStockData = async () => {
    try {
        const response = await axios.get('/api/stock-monitor');
        products.value = response.data.products;
        lowStockCount.value = response.data.lowStockCount;
    } catch (error) {
        console.error('Error fetching stock data:', error);
    }
};

onMounted(() => {
    fetchStockData();
    // Poll every 30 seconds
    intervalId = setInterval(fetchStockData, 30000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Stock Monitor (Real-time)</h3>
            <span class="flex items-center">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></span>
                <span class="text-xs text-gray-500">Live</span>
            </span>
        </div>

        <div v-if="lowStockCount > 0" class="bg-red-50 border border-red-200 rounded p-3 mb-4">
            <p class="text-sm text-red-800">
                ⚠️ <strong>{{ lowStockCount }}</strong> produk stok rendah!
            </p>
        </div>

        <div class="space-y-2 max-h-[400px] overflow-y-auto">
            <div v-for="product in products" :key="product.id"
                 class="flex items-center justify-between p-2 hover:bg-gray-50 rounded"
                 :class="{ 'bg-red-50': product.stock <= 10 }">
                <div>
                    <p class="font-semibold text-sm">{{ product.name }}</p>
                    <p class="text-xs text-gray-500">{{ product.category }}</p>
                </div>
                <span :class="[
                    'px-2 py-1 rounded text-xs font-bold',
                    product.stock === 0 ? 'bg-red-600 text-white' :
                    product.stock <= 10 ? 'bg-yellow-500 text-white' :
                    'bg-green-500 text-white'
                ]">
                    {{ product.stock }} unit
                </span>
            </div>
        </div>
    </div>
</template>
```

### **API Endpoint:**

**routes/web.php:**
```php
Route::get('/api/stock-monitor', [ProductController::class, 'stockMonitor'])->middleware('auth');
```

**ProductController.php:**
```php
public function stockMonitor()
{
    $products = Product::with('category')
        ->select('id', 'name', 'category_id', 'stock')
        ->orderBy('stock', 'asc')
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? '',
                'stock' => $product->stock
            ];
        });

    $lowStockCount = Product::where('stock', '<=', 10)->count();

    return response()->json([
        'products' => $products,
        'lowStockCount' => $lowStockCount,
        'timestamp' => now()->toIso8601String()
    ]);
}
```

---

## 7. ✅ CHECKLIST IMPLEMENTASI

### **Priority 1 - WAJIB:**
- [ ] Start MySQL di Herd
- [ ] Jalankan migration audit trail: `php artisan migrate`
- [ ] Update Models dengan trait Auditable
- [ ] Test thermal printer receipt
- [ ] Install Chart.js: `npm install chart.js vue-chartjs`
- [ ] Update Dashboard.vue dengan charts
- [ ] Rebuild assets: `npm run build`

### **Priority 2 - Penting:**
- [ ] Implementasi Role & Permission system
- [ ] Create error pages (404, 500)
- [ ] Update Exception Handler
- [ ] Test all error scenarios

### **Priority 3 - Enhancement:**
- [ ] Add Stock Monitor component
- [ ] Implement real-time notifications
- [ ] Add audit log viewer page
- [ ] Create role management UI

---

## 🎯 QUICK START

```bash
# 1. Start MySQL
# Buka Laravel Herd → Services → Start MySQL

# 2. Jalankan Migration
cd "c:\Users\Administrator\Desktop\aplikasi koperasi\koperasi-lemdiklat"
php artisan migrate

# 3. Install Chart.js
npm install chart.js vue-chartjs --save

# 4. Rebuild Assets
npm run build

# 5. Clear Cache
php artisan optimize:clear

# 6. Test Receipt
# Buka: http://localhost/kasir/pos/receipt/1
```

---

**Dokumentasi dibuat:** 2025-11-14
**Sistem:** Koperasi Lemdiklat Taruna Nusantara Indonesia
**Status:** Production-Ready dengan Enhancement
