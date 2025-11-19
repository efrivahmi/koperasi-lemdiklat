# 🔧 TROUBLESHOOTING GUIDE - KOPERASI SYSTEM

## 🚨 MASALAH UTAMA: Database MySQL Tidak Berjalan

**Error yang muncul:**
```
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it
```

**Artinya:** MySQL server tidak running atau tidak bisa diakses di port 3306.

---

## ✅ SOLUSI LANGKAH DEMI LANGKAH

### **Opsi 1: Menggunakan XAMPP MySQL**

1. **Buka XAMPP Control Panel**
   - Cari aplikasi "XAMPP Control Panel" di Start Menu
   - Jalankan sebagai Administrator (klik kanan → Run as Administrator)

2. **Start MySQL Service**
   - Klik tombol "Start" di sebelah "MySQL"
   - Tunggu hingga statusnya berubah menjadi hijau
   - Port harus menunjukkan: 3306

3. **Verify MySQL Running**
   ```bash
   # Di terminal/CMD, jalankan:
   netstat -an | findstr :3306

   # Jika MySQL running, akan muncul:
   # TCP    127.0.0.1:3306    0.0.0.0:0    LISTENING
   ```

4. **Test Koneksi Database**
   ```bash
   php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database Connected!' . PHP_EOL;"
   ```

---

### **Opsi 2: Menggunakan Laravel Herd MySQL**

Jika Anda menggunakan Laravel Herd (yang sudah terinstall):

1. **Buka Herd dari System Tray**
   - Klik icon Herd di system tray (pojok kanan bawah)
   - Pastikan Herd sedang running

2. **Check Database Settings**
   - Herd biasanya sudah include MySQL
   - Default port: 3306
   - Default user: root
   - Default password: (kosong)

3. **Update .env jika perlu**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=koperasi_lemdiklat
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

### **Opsi 3: Install MySQL Standalone (Jika belum ada)**

1. Download MySQL Community Server: https://dev.mysql.com/downloads/mysql/
2. Install dengan default settings
3. Set root password (atau kosongkan)
4. Start MySQL service dari Services (Win+R → services.msc → MySQL80)

---

## 🧪 TESTING SETELAH MYSQL RUNNING

### **1. Clear All Caches**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **2. Test Database Connection**
```bash
php artisan db:diagnose
```

Output yang benar:
```
=== DATABASE DIAGNOSTIC REPORT ===

📊 TABLE COUNTS:
  users: 3
  students: 1
  categories: 6
  products: 6
  sales: 2
  transactions: 3
  vouchers: 2
  expenses: 0

🔗 RELATIONSHIP TESTS:
  ✓ Student->User relationship OK
  ✓ Product->Category relationship OK
  ✓ Sale->Student relationship OK
  ✓ Transaction->Student relationship OK

🔢 ENUM VALUE CHECKS:
  Voucher status values: available
  Transaction type values: topup, debit
  Sale payment methods: balance

🔍 ORPHANED RECORDS CHECK:
  ✓ No orphaned products
  ✓ No orphaned students
  ✓ No invalid balance sales

=== DIAGNOSTIC COMPLETE ===
```

### **3. Test Login**
```bash
# Start Laravel server
php artisan serve

# Buka browser:
http://localhost:8000/login

# Atau jika pakai Herd:
https://koperasi-lemdiklat.test/login
```

---

## 📋 DAFTAR LENGKAP HALAMAN ADMIN YANG HARUS BISA DIAKSES

### **✅ Login sebagai ADMIN** (`admin@koperasi.com` / `password`)

| No | Halaman | URL | Fungsi |
|----|---------|-----|--------|
| 1 | **Dashboard** | `/dashboard` | Dashboard utama dengan statistik |
| 2 | **Categories Index** | `/admin/categories` | Daftar semua kategori |
| 3 | **Categories Create** | `/admin/categories/create` | Tambah kategori baru |
| 4 | **Categories Edit** | `/admin/categories/{id}/edit` | Edit kategori |
| 5 | **Products Index** | `/admin/products` | Daftar semua produk |
| 6 | **Products Create** | `/admin/products/create` | Tambah produk baru |
| 7 | **Products Edit** | `/admin/products/{id}/edit` | Edit produk |
| 8 | **Students Index** | `/admin/students` | Daftar semua siswa |
| 9 | **Students Create** | `/admin/students/create` | Tambah siswa baru |
| 10 | **Students Edit** | `/admin/students/{id}/edit` | Edit siswa |
| 11 | **Students RFID Register** | `/admin/students/{id}/rfid-register` | Daftarkan kartu RFID |
| 12 | **Users Index** | `/admin/users` | Daftar semua user |
| 13 | **Users Create** | `/admin/users/create` | Tambah user baru |
| 14 | **Users Edit** | `/admin/users/{id}/edit` | Edit user |
| 15 | **Stock Ins Index** | `/admin/stock-ins` | Riwayat barang masuk |
| 16 | **Stock Ins Create** | `/admin/stock-ins/create` | Catat barang masuk |
| 17 | **Vouchers Index** | `/admin/vouchers` | Daftar semua voucher |
| 18 | **Vouchers Create** | `/admin/vouchers/create` | Generate voucher baru |
| 19 | **Vouchers Redeem** | `/admin/vouchers-redeem` | Redeem voucher |
| 20 | **Expenses Index** | `/admin/expenses` | Daftar pengeluaran |
| 21 | **Expenses Create** | `/admin/expenses/create` | Catat pengeluaran |
| 22 | **Expenses Edit** | `/admin/expenses/{id}/edit` | Edit pengeluaran |
| 23 | **Transactions Index** | `/admin/transactions` | Riwayat transaksi |
| 24 | **Transactions Topup** | `/admin/transactions/topup` | Top-up saldo siswa |
| 25 | **Transactions Student History** | `/admin/transactions/student/{id}` | Riwayat per siswa |
| 26 | **Reports Sales** | `/admin/reports/sales` | Laporan penjualan |
| 27 | **Reports Inventory** | `/admin/reports/inventory` | Laporan inventaris |
| 28 | **Reports Financial** | `/admin/reports/financial` | Laporan keuangan |
| 29 | **Reports Student Transactions** | `/admin/reports/student-transactions` | Laporan transaksi siswa |

### **✅ Login sebagai KASIR** (`kasir@koperasi.com` / `password`)

| No | Halaman | URL | Fungsi |
|----|---------|-----|--------|
| 1 | **Dashboard** | `/dashboard` | Dashboard (redirect ke POS) |
| 2 | **POS Interface** | `/kasir/pos` | Interface kasir dengan RFID/Barcode |

### **✅ Login sebagai SISWA** (`budi@student.com` / `password`)

| No | Halaman | URL | Fungsi |
|----|---------|-----|--------|
| 1 | **Student Dashboard** | `/student/dashboard` | Dashboard siswa dengan saldo |
| 2 | **Transactions** | `/student/transactions` | Riwayat semua transaksi |
| 3 | **Purchase History** | `/student/purchase-history` | Riwayat pembelian detail |

---

## 🐛 ERROR YANG MUNGKIN MUNCUL & SOLUSINYA

### **1. Error: Route not defined**
```bash
# Solusi:
php artisan route:clear
php artisan config:clear
```

### **2. Error: 403 Unauthorized**
**Penyebab:** Login dengan role yang salah (misal: siswa mencoba akses admin)

**Solusi:**
- Logout dan login dengan akun yang tepat
- Admin: `admin@koperasi.com`
- Kasir: `kasir@koperasi.com`
- Siswa: `budi@student.com`

### **3. Error: Column not found**
**Penyebab:** Migration belum dijalankan atau ada perubahan struktur database

**Solusi:**
```bash
# Check migration status
php artisan migrate:status

# Run pending migrations
php artisan migrate

# If needed, fresh migration (WARNING: deletes all data!)
php artisan migrate:fresh --seed
```

### **4. Error: Voucher expired_at column**
**Penyebab:** Migration sudah diperbaiki

**Solusi:**
```bash
# Migration sudah ada, pastikan sudah dijalankan:
php artisan migrate
```

### **5. Error: Transaction type enum mismatch**
**Penyebab:** Migration sudah diperbaiki

**Solusi:**
```bash
# Migration sudah ada:
php artisan migrate
```

### **6. Error: No data in tables**
**Penyebab:** Seeder belum dijalankan

**Solusi:**
```bash
# Run all seeders
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=DemoDataSeeder
php artisan db:seed --class=TransactionSeeder
```

---

## 🔍 DEBUGGING TOOLS

### **1. Check Routes**
```bash
php artisan route:list | findstr admin
php artisan route:list | findstr kasir
php artisan route:list | findstr student
```

### **2. Check Database**
```bash
php artisan db:diagnose
```

### **3. Check Logs**
```bash
# Location: storage/logs/laravel.log
# Buka file untuk melihat error detail
```

### **4. Laravel Tinker (Interactive Console)**
```bash
php artisan tinker

# Check users
>>> User::all();

# Check if admin exists
>>> User::where('email', 'admin@koperasi.com')->first();

# Check database connection
>>> DB::connection()->getPdo();
```

---

## 📝 CHECKLIST TROUBLESHOOTING

Gunakan checklist ini untuk memastikan semua sudah OK:

- [ ] MySQL service sudah running (XAMPP atau Herd)
- [ ] Port 3306 sudah listening
- [ ] Database `koperasi_lemdiklat` sudah dibuat
- [ ] `.env` file sudah configured dengan benar
- [ ] `php artisan migrate` sudah dijalankan
- [ ] `php artisan db:seed` sudah dijalankan
- [ ] `npm run build` sudah dijalankan
- [ ] Cache sudah di-clear (config, route, view)
- [ ] `php artisan serve` atau Herd sudah running
- [ ] Bisa login dengan 3 role berbeda (admin, kasir, siswa)
- [ ] Dashboard muncul tanpa error
- [ ] Halaman admin bisa diakses
- [ ] POS interface bisa diakses (kasir)
- [ ] Student portal bisa diakses (siswa)

---

## 🚀 QUICK FIX COMMAND (Run semua sekaligus)

```bash
# 1. Start XAMPP MySQL terlebih dahulu!

# 2. Jalankan command ini:
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan migrate && php artisan db:seed && npm run build

# 3. Start server:
php artisan serve
```

---

## 📞 BANTUAN LEBIH LANJUT

Jika masih ada error setelah mengikuti guide ini:

1. **Check Laravel Log:**
   - Buka: `storage/logs/laravel.log`
   - Lihat error terakhir di bagian bawah file

2. **Run Diagnostic:**
   ```bash
   php artisan db:diagnose
   ```

3. **Screenshot Error:**
   - Ambil screenshot full error message
   - Termasuk URL yang error
   - Termasuk role yang sedang login

4. **Check Browser Console:**
   - Buka Developer Tools (F12)
   - Lihat Console tab untuk JavaScript errors
   - Lihat Network tab untuk failed requests

---

**🏫 KOPERASI LEMDIKLAT TARUNA NUSANTARA INDONESIA**
*Troubleshooting Guide - Version 1.0*
