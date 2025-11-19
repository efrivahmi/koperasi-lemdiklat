# 🚀 START HERE - PANDUAN MULAI CEPAT

## ⚡ QUICK START (5 Menit)

### **Langkah 1: Start MySQL** (PENTING!)

**Pilih salah satu:**

#### **A. Pakai XAMPP:**
1. Buka **XAMPP Control Panel**
2. Klik **"Start"** di sebelah **MySQL**
3. Tunggu hingga hijau ✅

#### **B. Pakai Herd:**
- Herd otomatis start MySQL
- Pastikan Herd icon hijau di system tray

---

### **Langkah 2: Verify MySQL Running**

Buka **Command Prompt** (CMD) atau **Terminal** di folder project:

```bash
cd "C:\Users\Administrator\Desktop\aplikasi koperasi\koperasi-lemdiklat"

# Test database connection:
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK!' . PHP_EOL;"
```

**✅ Jika berhasil:** Akan muncul "Database OK!"
**❌ Jika error:** MySQL belum running → Kembali ke Langkah 1

---

### **Langkah 3: Start Laravel Server**

```bash
# Start server:
php artisan serve
```

**Output yang benar:**
```
INFO  Server running on [http://127.0.0.1:8000].
```

---

### **Langkah 4: Buka Browser**

Klik atau copy paste URL ini ke browser:

```
http://127.0.0.1:8000/login
```

**Atau jika pakai Herd:**
```
https://koperasi-lemdiklat.test/login
```

---

### **Langkah 5: Login**

**3 Akun untuk Testing:**

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Admin** | admin@koperasi.com | password | Full access (semua fitur) |
| **Kasir** | kasir@koperasi.com | password | POS Interface |
| **Siswa** | budi@student.com | password | Student Portal |

---

## ✅ VERIFIKASI INSTALASI

Setelah login sebagai **Admin**, coba akses halaman-halaman ini:

1. **Dashboard:** `http://127.0.0.1:8000/dashboard`
   - ✅ Harus muncul statistik dengan charts
   - ✅ Tidak ada error

2. **Products:** `http://127.0.0.1:8000/admin/products`
   - ✅ Harus muncul 6 produk demo
   - ✅ Ada tombol "Tambah Produk"

3. **Students:** `http://127.0.0.1:8000/admin/students`
   - ✅ Harus muncul 1 siswa (Budi Santoso)

4. **Reports:** `http://127.0.0.1:8000/admin/reports/sales`
   - ✅ Harus muncul laporan penjualan
   - ✅ Ada tombol "Export Excel"

---

## 🐛 ADA ERROR?

### **Error: Database Connection Refused**

**Solusi:**
1. Pastikan MySQL running (cek XAMPP Control Panel)
2. Jalankan: `php artisan config:clear`
3. Test lagi

### **Error: Route Not Defined**

**Solusi:**
```bash
php artisan route:clear
php artisan config:clear
```

### **Error: Page Not Found (404)**

**Solusi:**
- Pastikan sudah login dengan role yang benar
- Admin harus pakai `admin@koperasi.com`
- Kasir harus pakai `kasir@koperasi.com`
- Siswa harus pakai `budi@student.com`

### **Error: Blank Page / White Screen**

**Solusi:**
```bash
# Build frontend assets:
npm run build

# Clear cache:
php artisan config:clear
php artisan view:clear
```

---

## 📚 DOKUMENTASI LENGKAP

Untuk troubleshooting lebih detail, baca:

📖 **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Panduan troubleshooting lengkap

---

## 🎯 FITUR UTAMA YANG BISA DICOBA

### **Sebagai ADMIN:**

1. **Tambah Produk Baru**
   - Menu: Products → Tambah Produk
   - Upload gambar, set harga, barcode

2. **Tambah Siswa Baru**
   - Menu: Students → Tambah Siswa
   - Isi NIS, Nama, Kelas, Saldo awal

3. **Daftarkan RFID ke Siswa**
   - Menu: Students → Pilih siswa → "Daftarkan RFID"
   - Scan kartu RFID

4. **Top-Up Saldo Siswa**
   - Menu: Transactions → Top-Up
   - Pilih siswa, masukkan nominal

5. **Generate Voucher**
   - Menu: Vouchers → Generate Voucher
   - Set nominal, expired date

6. **Lihat Laporan**
   - Menu: Laporan (dropdown)
   - Pilih: Sales, Inventory, Financial, atau Student Transactions
   - Klik "Export Excel" untuk download

### **Sebagai KASIR:**

1. **Transaksi Penjualan**
   - Otomatis redirect ke POS Interface
   - Scan barcode produk atau klik manual
   - Scan RFID siswa atau cari manual
   - Checkout (Cash atau Saldo)
   - Print receipt

### **Sebagai SISWA:**

1. **Cek Saldo**
   - Dashboard menunjukkan saldo besar di card purple

2. **Lihat Riwayat Transaksi**
   - Menu: Riwayat Transaksi
   - Filter by date, type

3. **Lihat Riwayat Pembelian**
   - Menu: Riwayat Pembelian
   - Detail items yang dibeli

---

## 🔧 MAINTENANCE COMMANDS

### **Reset Database (HAPUS SEMUA DATA!):**
```bash
# WARNING: Ini akan menghapus SEMUA data!
php artisan migrate:fresh --seed
```

### **Rebuild Frontend:**
```bash
npm run build
```

### **Clear All Caches:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### **Check Database:**
```bash
php artisan db:diagnose
```

---

## 📊 DATA DEMO YANG SUDAH ADA

### **Users (3):**
1. Admin - admin@koperasi.com
2. Kasir - kasir@koperasi.com
3. Siswa - budi@student.com

### **Students (1):**
- Budi Santoso (NIS: 2024001, Kelas: XII IPA 1)
- Saldo: Rp 226.000

### **Products (6):**
1. Indomie Goreng - Rp 3.500
2. Chitato - Rp 7.000
3. Teh Pucuk - Rp 4.500
4. Aqua 600ml - Rp 3.000
5. Pulpen Standard AE7 - Rp 2.500
6. Buku Tulis 38 Lembar - Rp 5.000

### **Categories (3):**
1. Makanan Ringan
2. Minuman
3. Alat Tulis

### **Transactions (3):**
1. Top-up: +Rp 100.000
2. Purchase: -Rp 14.500 (Indomie, Teh, Aqua)
3. Purchase: -Rp 9.500 (Chitato, Pulpen)

---

## ✨ TIPS & TRICKS

1. **Gunakan Browser Mode Incognito** untuk test multi-role (buka 3 window):
   - Window 1: Login Admin
   - Window 2: Login Kasir
   - Window 3: Login Siswa

2. **Shortcut Keyboard di POS:**
   - Focus otomatis ke barcode scanner input
   - Enter untuk add to cart

3. **Excel Export:**
   - Semua laporan bisa di-export ke Excel
   - File download otomatis dengan nama timestamped

4. **RFID Scanner:**
   - Gunakan HID keyboard emulator
   - Scan akan otomatis input UID

---

## 🏫 KOPERASI LEMDIKLAT TARUNA NUSANTARA INDONESIA

**Project:** Sistem Kasir Koperasi Sekolah dengan RFID & E-Commerce
**Version:** 1.0
**Laravel:** 12.x
**Vue.js:** 3.x
**Database:** MySQL

---

## 📞 NEED HELP?

Jika masih ada masalah:

1. ✅ Baca **TROUBLESHOOTING.md**
2. ✅ Jalankan `php artisan db:diagnose`
3. ✅ Check **storage/logs/laravel.log**
4. ✅ Screenshot error dan bagikan

**Semua sudah setup dengan baik!** Tinggal pastikan MySQL running dan enjoy! 🎉
