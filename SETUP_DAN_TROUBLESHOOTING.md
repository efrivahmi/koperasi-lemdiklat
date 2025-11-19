# 🔧 Setup & Troubleshooting
## Sistem Koperasi Lemdiklat Taruna Nusantara Indonesia

---

## 📋 Daftar Isi
1. [Setup Database](#setup-database)
2. [Cara Menjalankan Migrasi](#cara-menjalankan-migrasi)
3. [Troubleshooting Error Umum](#troubleshooting-error-umum)
4. [Cara Testing Fitur](#cara-testing-fitur)

---

## 1. 💾 Setup Database

### **Masalah yang Sering Terjadi:**
```
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it
```

### **Solusi:**

#### **A. Check Status MySQL di Herd:**
1. Buka aplikasi **Laravel Herd**
2. Klik menu **Services**
3. Pastikan **MySQL** statusnya **Running** (warna hijau)
4. Jika **Stopped** (merah), klik tombol **Start**

#### **B. Jika MySQL tidak mau start:**

**Windows (Herd):**
```bash
# 1. Stop semua service MySQL yang running
taskkill /F /IM mysqld.exe

# 2. Restart Herd
# Tutup aplikasi Herd → Buka lagi

# 3. Start MySQL dari Herd GUI
# Atau via command:
cd "C:\Program Files\Herd\resources\mysql\bin"
.\mysqld.exe --standalone
```

#### **C. Verifikasi Koneksi Database:**
```bash
cd "c:\Users\Administrator\Desktop\aplikasi koperasi\koperasi-lemdiklat"

# Test koneksi dengan Tinker
php artisan tinker

# Di dalam Tinker, jalankan:
DB::connection()->getPdo();

# Jika berhasil, akan muncul object PDO
# Jika error, berarti database belum connect
```

#### **D. Check File `.env`:**
Pastikan konfigurasi database benar:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi_lemdiklat
DB_USERNAME=root
DB_PASSWORD=
```

**Catatan untuk Herd:**
- Default password Herd adalah **KOSONG** (empty string)
- Jika password ada, biasanya: `root` atau `password`

#### **E. Buat Database Jika Belum Ada:**
```bash
# Masuk ke MySQL CLI
mysql -u root -p
# (tekan Enter jika password kosong)

# Buat database
CREATE DATABASE koperasi_lemdiklat;

# Lihat daftar database
SHOW DATABASES;

# Keluar
exit;
```

---

## 2. 🚀 Cara Menjalankan Migrasi

### **Langkah Lengkap:**

#### **Step 1: Pastikan Database Connected**
```bash
cd "c:\Users\Administrator\Desktop\aplikasi koperasi\koperasi-lemdiklat"

# Test koneksi
php artisan db:show

# Output yang diharapkan:
# MySQL 8.x.x ...
# Database: koperasi_lemdiklat
# Connection: mysql
```

#### **Step 2: Jalankan Migrasi Baru**
```bash
# Migrasi terbaru (field RFID, NISN, Jenjang, Alamat)
php artisan migrate

# Jika ada error "table already exists", gunakan:
php artisan migrate:fresh --seed
# ⚠️ WARNING: Ini akan HAPUS SEMUA DATA!
```

#### **Step 3: Jalankan Seeder (Opsional)**
```bash
# Jika ingin data dummy untuk testing
php artisan db:seed --class=ComprehensiveSeeder

# Atau seeder tertentu:
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=ProductSeeder
```

#### **Step 4: Verifikasi Tabel Baru**
```bash
php artisan tinker

# Check struktur tabel students
DB::select("DESCRIBE students");

# Pastikan ada kolom:
# - rfid_uid
# - nisn
# - jenjang
# - alamat_lengkap

# Check data siswa
DB::table('students')->count();
```

---

## 3. ⚠️ Troubleshooting Error Umum

### **Error 1: "Maximum execution time of 30 seconds exceeded"**

**Penyebab:** Session table terlalu banyak data / corrupt

**Solusi:**
```bash
# Truncate session table
php artisan tinker
DB::table('sessions')->truncate();
exit;

# Atau langsung via MySQL
mysql -u root -p
use koperasi_lemdiklat;
TRUNCATE TABLE sessions;
exit;

# Clear cache
php artisan optimize:clear
```

---

### **Error 2: "Column 'class' not found"**

**Penyebab:** Database menggunakan kolom `kelas` (bahasa Indonesia), bukan `class`

**Lokasi Error:** Report controllers & Vue components

**Solusi:** Sudah diperbaiki di:
- [ReportController.php:217](app/Http/Controllers/ReportController.php#L217)
- [Sales.vue:112](resources/js/Pages/Admin/Reports/Sales.vue#L112)
- [StudentTransactions.vue:154](resources/js/Pages/Admin/Reports/StudentTransactions.vue#L154)

Jika masih ada, cari dan ganti:
```bash
# Cari semua penggunaan 'class' di kode
grep -r "student\.class" resources/js/
grep -r "->class" app/Http/Controllers/

# Ganti dengan 'kelas'
```

---

### **Error 3: "Data truncated for column 'type' at row 1"**

**Penyebab:** Enum value salah di transactions table

**Solusi:** Sudah diperbaiki di:
- [TransactionController.php:73](app/Http/Controllers/TransactionController.php#L73) - `'credit'` → `'top-up'`
- [VoucherController.php:187](app/Http/Controllers/VoucherController.php#L187) - `'credit'` → `'top-up'`
- [PosController.php:199](app/Http/Controllers/PosController.php#L199) - `'debit'` → `'pembelian'`

**Enum Values yang Benar:**
- `'top-up'` - untuk topup saldo
- `'pembelian'` - untuk belanja
- `'pengembalian'` - untuk refund

---

### **Error 4: Report Pages Blank/White Screen**

**Penyebab:**
1. JavaScript error karena null value
2. Vue component crash saat render
3. Database tidak connect

**Cara Debug:**
```bash
# 1. Buka browser DevTools (F12)
# 2. Lihat tab Console - ada error JavaScript?
# 3. Lihat tab Network - API berhasil?

# 4. Check Laravel logs
tail -50 storage/logs/laravel.log

# 5. Clear semua cache
php artisan optimize:clear
php artisan view:clear
npm run build
```

**Solusi:** Sudah ditambahkan null checks di:
- [Sales.vue:111-113](resources/js/Pages/Admin/Reports/Sales.vue#L111-L113)
- [StudentTransactions.vue:147-195](resources/js/Pages/Admin/Reports/StudentTransactions.vue#L147-L195)

---

### **Error 5: "Undefined property: category"**

**Penyebab:** Controller tidak load relationship

**Solusi:**
```php
// SEBELUM:
$products = Product::all();

// SESUDAH:
$products = Product::with('category')->get();
```

Sudah diperbaiki di:
- [StockInController.php:40](app/Http/Controllers/StockInController.php#L40)
- [StockInController.php:89](app/Http/Controllers/StockInController.php#L89)

---

## 4. ✅ Cara Testing Fitur

### **A. Test Manual Topup**

**Persiapan:**
```bash
# 1. Pastikan ada siswa
php artisan tinker
DB::table('students')->count(); # Harus > 0

# 2. Lihat saldo siswa pertama
DB::table('students')->first();
```

**Testing:**
1. Login sebagai **Admin**
2. Buka: `/admin/transactions/topup`
3. Ketik NIS atau nama siswa
4. Pilih siswa dari dropdown
5. Input nominal: **50000**
6. Klik **"Top-up Sekarang"**

**Expected Result:**
- Success notification muncul
- Saldo siswa bertambah Rp 50.000
- Check database:
  ```sql
  SELECT * FROM transactions ORDER BY created_at DESC LIMIT 1;
  -- type harus 'top-up'
  -- amount harus 50000
  ```

---

### **B. Test Redeem Voucher**

**Persiapan:**
```bash
# 1. Buat voucher
# Login admin → /admin/vouchers/create
# Nominal: 10000
# Qty: 1
# Generate

# 2. Copy kode voucher (format: VCR-XXXXXXXX)
```

**Testing:**
1. Buka: `/admin/vouchers-redeem`
2. Cari siswa
3. Paste kode voucher
4. Klik **"Redeem Voucher"**

**Expected Result:**
- Success notification
- Saldo siswa +10.000
- Voucher status jadi `is_used = 1`

---

### **C. Test POS - Pembayaran Tunai**

**Testing:**
1. Login sebagai **Kasir**
2. Buka: `/kasir/pos`
3. Klik produk (contoh: Pulpen - Rp 5.000)
4. Klik lagi → Qty jadi 2
5. Klik **"Checkout"**
6. Pilih metode: **"Tunai"**
7. Input uang diterima: **20000**
8. Kembalian otomatis hitung: Rp 10.000
9. Klik **"Proses Pembayaran"**

**Expected Result:**
- Transaksi berhasil
- Stok produk berkurang 2
- Sale tercatat dengan `student_id = NULL`
- **TIDAK ada** record di `transactions` table

---

### **D. Test POS - Pembayaran Saldo (RFID)**

**Persiapan:**
```bash
# 1. Pastikan siswa punya saldo minimal Rp 10.000
# 2. Registrasi RFID:
# - Login admin
# - /admin/students/{id}/edit
# - Input RFID UID manual (contoh: E4A25B3F)
# - Save
```

**Testing (Manual tanpa RFID Reader):**
1. Login sebagai **Kasir**
2. Buka: `/kasir/pos`
3. **Manual input RFID:**
   - Buka DevTools Console (F12)
   - Jalankan:
     ```javascript
     document.getElementById('hidden-scanner-input').value = 'E4A25B3F';
     document.getElementById('hidden-scanner-input').dispatchEvent(new KeyboardEvent('keyup', {key: 'Enter'}));
     ```
4. Data siswa muncul + metode auto "Saldo"
5. Klik produk
6. Checkout → Klik **"Proses Pembayaran"**

**Expected Result:**
- Saldo siswa terpotong
- Sale tercatat dengan `student_id = {id siswa}`
- **ADA** record di `transactions` table (type: 'pembelian')

---

### **E. Test Generate Kartu Siswa**

**Persiapan:**
```bash
# Jalankan migration dulu
php artisan migrate

# Isi data siswa lengkap via Edit:
# - NISN
# - Jenjang (SMA/SMK)
# - Alamat Lengkap
```

**Testing:**
1. Login sebagai **Admin**
2. Buka: `/admin/students`
3. Klik tombol **"Generate Kartu"** pada baris siswa
4. Atau langsung ke: `/admin/students/{id}/generate-card`

**Expected Result:**
- Halaman kartu siswa muncul (ukuran ID card)
- Data lengkap ter-display (Nama, NIS, NISN, Kelas, Alamat)
- RFID UID tampil (jika sudah terdaftar)
- Tombol **"Print Kartu"** muncul
- Saat print, kartu auto-resize ke ukuran 85.6mm x 53.98mm

---

### **F. Test Report Pages**

**Testing:**
1. **Inventory Report:**
   - Buka: `/admin/reports/inventory`
   - Filter kategori, status stok
   - Klik **"Tampilkan"**
   - Export Excel

2. **Sales Report:**
   - Buka: `/admin/reports/sales`
   - Pilih range tanggal
   - Filter metode pembayaran
   - Klik **"Tampilkan"**

3. **Student Transactions:**
   - Buka: `/admin/reports/student-transactions`
   - Filter kelas, siswa
   - Pilih range tanggal
   - Klik **"Tampilkan"**

4. **Financial Report:**
   - Buka: `/admin/reports/financial`
   - Pilih range tanggal
   - Lihat summary (Revenue, COGS, Profit)

**Expected Result:**
- **SEMUA halaman tampil** (tidak blank)
- Tabel data terlihat (atau EmptyState jika kosong)
- Export Excel berfungsi
- Pagination bekerja

---

## 🔑 Checklist Final

Sebelum deploy/production:

- [ ] Database MySQL running dan connected
- [ ] Semua migrasi berhasil (`php artisan migrate:status`)
- [ ] Seeder berjalan (jika perlu data dummy)
- [ ] Cache di-clear (`php artisan optimize:clear`)
- [ ] Assets di-build (`npm run build`)
- [ ] Test login Admin, Kasir, Student
- [ ] Test semua fitur transaksi (topup, voucher, POS)
- [ ] Test semua report pages (tidak ada yang blank)
- [ ] Test generate kartu siswa
- [ ] Backup database (`mysqldump -u root koperasi_lemdiklat > backup.sql`)

---

## 📞 Support

Jika masih ada error:

1. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **Check browser console:**
   - F12 → Tab Console
   - Screenshot error jika ada

3. **Check database:**
   ```bash
   php artisan db:show
   php artisan migrate:status
   ```

4. **Full reset (HAPUS SEMUA DATA):**
   ```bash
   php artisan migrate:fresh --seed
   php artisan optimize:clear
   npm run build
   ```

---

**Dokumentasi dibuat:** 2025-11-14
**Sistem:** Koperasi Lemdiklat Taruna Nusantara Indonesia
**Tech Stack:** Laravel 12 + Vue 3 + Inertia.js + MySQL + Herd
