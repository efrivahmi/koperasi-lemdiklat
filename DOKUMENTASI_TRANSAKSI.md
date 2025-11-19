# 📘 Dokumentasi Lengkap Sistem Transaksi & RFID
## Koperasi Lemdiklat Taruna Nusantara Indonesia

---

## 📋 Daftar Isi
1. [Skema Topup Manual](#skema-topup-manual)
2. [Skema Topup via Voucher](#skema-topup-via-voucher)
3. [Skema Transaksi POS/Kasir](#skema-transaksi-poskasir)
4. [Implementasi RFID Scanner](#implementasi-rfid-scanner)
5. [Implementasi Barcode Scanner](#implementasi-barcode-scanner)
6. [Database Schema](#database-schema)

---

## 1. 💵 Skema Topup Manual (Admin)

### **Alur Proses:**
```
[Admin] → Login → Menu Transactions → Top-up Manual
   ↓
Cari Siswa (NIS/Nama) → Pilih Siswa → Tampil Saldo Current
   ↓
Input Nominal (min Rp 1.000) → Tambah Keterangan (opsional)
   ↓
Klik "Top-up Sekarang" → Validasi
   ↓
Database Transaction:
  1. Student.balance += amount
  2. Transaction record dibuat (type: 'top-up')
  3. Ending balance tercatat
   ↓
Success → Saldo siswa terupdate → Notifikasi berhasil
```

### **Endpoint:**
- **Route:** `/admin/transactions/topup`
- **Controller:** `TransactionController@topup()`
- **Method:** `POST`

### **Request Payload:**
```json
{
  "student_id": 123,
  "amount": 50000,
  "description": "Top-up manual dari admin"
}
```

### **Response Success:**
```json
{
  "success": true,
  "message": "Top-up berhasil",
  "student": {
    "id": 123,
    "name": "John Doe",
    "balance": 150000
  }
}
```

### **Database Changes:**
```sql
-- 1. Update saldo siswa
UPDATE students SET balance = balance + 50000 WHERE id = 123;

-- 2. Catat transaksi
INSERT INTO transactions (student_id, type, amount, ending_balance, description, reference_type)
VALUES (123, 'top-up', 50000, 150000, 'Top-up manual oleh admin', 'manual_topup');
```

---

## 2. 🎫 Skema Topup via Voucher

### **Alur Proses:**
```
[Admin/Kasir] → Login → Menu Vouchers → Redeem Voucher
   ↓
Cari Siswa (NIS/Nama) → Pilih Siswa → Tampil Saldo Current
   ↓
Input Kode Voucher (VCR-XXXXXXXX) → Validasi Voucher
   ↓
Cek Status Voucher:
  - is_used = 0 (belum dipakai) ✓
  - is_used = 1 (sudah dipakai) ✗
   ↓
Database Transaction:
  1. Student.balance += voucher.nominal
  2. Voucher.is_used = 1
  3. Voucher.used_by = admin_user_id
  4. Voucher.used_at = NOW()
  5. Transaction record dibuat (type: 'top-up')
   ↓
Success → Voucher terpakai → Saldo siswa bertambah
```

### **Endpoint:**
- **Route:** `/admin/vouchers-redeem`
- **Controller:** `VoucherController@redeem()`
- **Method:** `POST`

### **Request Payload:**
```json
{
  "code": "VCR-ABC12345",
  "student_id": 123
}
```

### **Response Success:**
```json
{
  "success": true,
  "message": "Voucher berhasil di-redeem",
  "student": {
    "id": 123,
    "balance": 160000
  },
  "voucher": {
    "code": "VCR-ABC12345",
    "nominal": 10000
  }
}
```

### **Database Changes:**
```sql
-- 1. Update saldo siswa
UPDATE students SET balance = balance + 10000 WHERE id = 123;

-- 2. Tandai voucher sudah dipakai
UPDATE vouchers SET is_used = 1, used_by = 5, used_at = NOW() WHERE code = 'VCR-ABC12345';

-- 3. Catat transaksi
INSERT INTO transactions (student_id, type, amount, ending_balance, description, reference_type, reference_id)
VALUES (123, 'top-up', 10000, 160000, 'Top-up via voucher: VCR-ABC12345', 'voucher', 456);
```

---

## 3. 🛒 Skema Transaksi POS/Kasir

### **A. Pembayaran TUNAI (Cash)**

```
[Kasir] → Login ke POS → Scan/Klik Produk → Tambah ke Keranjang
   ↓
Klik "Checkout" → Pilih Metode: "Tunai"
   ↓
Input Jumlah Uang yang Diterima → Sistem hitung kembalian
   ↓
Validasi:
  - Uang >= Total belanja ✓
  - Stok produk cukup ✓
   ↓
Klik "Proses Pembayaran" → Database Transaction:
  1. Buat record di table 'sales' (student_id = NULL)
  2. Buat record di table 'sale_items' untuk setiap produk
  3. Kurangi stok produk (product.stock -= qty)
  4. TIDAK membuat record di 'transactions' (karena cash)
   ↓
Success → Print/Download Struk → Keranjang dikosongkan
```

**Karakteristik:**
- ✅ TIDAK perlu student_id
- ✅ Bisa digunakan siapa saja (umum)
- ✅ Tidak ada pencatatan di tabel transactions
- ✅ Hanya tercatat di sales & sale_items

### **B. Pembayaran SALDO (Balance/RFID)**

```
[Kasir] → Login ke POS → SCAN KARTU RFID SISWA DULU!
   ↓
RFID Scanner mengirim UID → API: GET /kasir/api/students/rfid/{uid}
   ↓
Sistem load data siswa → Tampil: Nama, NIS, Kelas, Saldo
Metode pembayaran auto-switch ke "Saldo"
   ↓
Scan/Klik Produk → Tambah ke Keranjang
   ↓
Klik "Checkout" → Metode sudah "Saldo" (tidak bisa diganti)
   ↓
Validasi:
  - Saldo siswa >= Total belanja ✓
  - Stok produk cukup ✓
   ↓
Klik "Proses Pembayaran" → Database Transaction:
  1. Buat record di table 'sales' (student_id = {id siswa})
  2. Buat record di table 'sale_items' untuk setiap produk
  3. Kurangi stok produk (product.stock -= qty)
  4. Kurangi saldo siswa (student.balance -= total)
  5. Buat record di table 'transactions' (type: 'pembelian')
   ↓
Success → Saldo siswa berkurang → Print/Download Struk
```

**Karakteristik:**
- ✅ HARUS ada student_id (via RFID scan)
- ✅ Hanya untuk siswa terdaftar
- ✅ Ada pencatatan di tabel transactions
- ✅ Saldo otomatis terpotong

### **Endpoint:**
- **Route:** `/kasir/api/checkout`
- **Controller:** `PosController@checkout()`
- **Method:** `POST`

### **Request Payload (Cash):**
```json
{
  "items": [
    {
      "product_id": 10,
      "name": "Pulpen Pilot",
      "price": 5000,
      "quantity": 2
    },
    {
      "product_id": 15,
      "name": "Buku Tulis",
      "price": 3000,
      "quantity": 3
    }
  ],
  "payment_method": "cash",
  "student_id": null,
  "cash_amount": 20000
}
```

### **Request Payload (Balance):**
```json
{
  "items": [
    {
      "product_id": 10,
      "name": "Pulpen Pilot",
      "price": 5000,
      "quantity": 2
    }
  ],
  "payment_method": "balance",
  "student_id": 123,
  "cash_amount": null
}
```

### **Database Changes (Balance):**
```sql
-- 1. Buat sale record
INSERT INTO sales (user_id, student_id, total, payment_method, cash_amount, change_amount)
VALUES (5, 123, 10000, 'balance', NULL, NULL);

-- 2. Buat sale items
INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal)
VALUES
  (789, 10, 2, 5000, 10000);

-- 3. Kurangi stok
UPDATE products SET stock = stock - 2 WHERE id = 10;

-- 4. Kurangi saldo siswa
UPDATE students SET balance = balance - 10000 WHERE id = 123;

-- 5. Catat transaksi
INSERT INTO transactions (student_id, type, amount, ending_balance, description, reference_type, reference_id)
VALUES (123, 'pembelian', 10000, 140000, 'Pembelian di koperasi - Invoice #789', 'sale', 789);
```

---

## 4. 📡 Implementasi RFID Scanner

### **Hardware yang Diperlukan:**
1. **RFID Reader** (USB/Serial)
   - Contoh: RC522, ACR122U, atau sejenisnya
   - Harga: Rp 100.000 - Rp 500.000

2. **RFID Card/Tag** untuk siswa
   - Jenis: MIFARE Classic 1K, NTAG213, atau ISO 14443A
   - Harga: Rp 2.000 - Rp 10.000 per kartu

### **Cara Kerja:**

#### **A. Registrasi RFID (Admin)**
```
[Admin] → Data Siswa → Klik Edit Siswa
   ↓
Klik tombol "Scan RFID untuk Registrasi"
   ↓
RFID Reader aktif → Tempelkan kartu RFID
   ↓
Reader mengirim UID (contoh: "E4:A2:5B:3F")
   ↓
JavaScript capture UID → Simpan ke field student.rfid_uid
   ↓
Update database: UPDATE students SET rfid_uid = 'E4:A2:5B:3F' WHERE id = 123
   ↓
Success → Kartu RFID terdaftar untuk siswa
```

**Kode Frontend (Vue.js):**
```javascript
// Di halaman Edit Student
const rfidInput = ref('');

const handleRfidScan = () => {
  // RFID reader akan mengirim UID sebagai keyboard input
  if (rfidInput.value.length >= 8) {
    form.rfid_uid = rfidInput.value;
    rfidInput.value = '';
    alert('RFID berhasil terdaftar!');
  }
};

// Hidden input untuk capture RFID
<input
  v-model="rfidInput"
  @input="handleRfidScan"
  style="position: absolute; left: -9999px;"
  id="rfid-scanner"
/>
```

#### **B. Penggunaan RFID di POS**
```
[Kasir di POS] → RFID Reader siap (auto-focus)
   ↓
Siswa tempelkan kartu RFID → Reader kirim UID
   ↓
JavaScript capture → AJAX Request:
GET /kasir/api/students/rfid/{uid}
   ↓
Backend cari siswa:
SELECT * FROM students WHERE rfid_uid = 'E4:A2:5B:3F'
   ↓
If found:
  - Load data siswa (nama, NIS, kelas, saldo)
  - Auto-switch payment method ke "balance"
  - Tampilkan info siswa di layar POS
Else:
  - Alert "Kartu RFID tidak terdaftar"
   ↓
Kasir lanjut scan produk → Checkout → Saldo terpotong otomatis
```

**Kode Backend (PosController):**
```php
public function getStudentByRfid($rfidUid)
{
    $student = Student::with('user')
        ->where('rfid_uid', $rfidUid)
        ->first();

    if (!$student) {
        return response()->json([
            'success' => false,
            'message' => 'Kartu RFID tidak terdaftar'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'student' => $student
    ]);
}
```

**Kode Frontend (POS Vue):**
```javascript
const rfidInput = ref('');

const handleRfidInput = async () => {
    if (!rfidInput.value || rfidInput.value.length < 8) return;

    try {
        const response = await axios.get(`/kasir/api/students/rfid/${rfidInput.value}`);

        if (response.data.success) {
            selectedStudent.value = response.data.student;
            paymentMethod.value = 'balance';
            showNotification(
                `Siswa: ${response.data.student.user.name} - Saldo: Rp ${formatCurrency(response.data.student.balance)}`,
                'success'
            );
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Kartu RFID tidak terdaftar', 'error');
    } finally {
        rfidInput.value = '';
    }
};

// Auto-focus hidden input untuk RFID scanner
onMounted(() => {
    const hiddenInput = document.getElementById('rfid-hidden-input');
    if (hiddenInput) {
        hiddenInput.focus();
    }
});
```

### **Setup RFID Reader:**

#### **Windows:**
1. Colokkan RFID Reader USB
2. Install driver (biasanya auto-detect sebagai HID Keyboard)
3. Test: Buka Notepad → Tap kartu RFID → UID muncul sebagai text
4. Jika muncul, berarti reader berfungsi sebagai keyboard emulator

#### **Konfigurasi Reader (Keyboard Emulation Mode):**
- Reader harus di-set ke mode "Keyboard Emulation"
- Setiap tap kartu akan mengirim UID + Enter (seperti mengetik)
- Format UID: hex string (contoh: E4A25B3F atau E4:A2:5B:3F)

---

## 5. 📷 Implementasi Barcode Scanner

### **Hardware yang Diperlukan:**
1. **Barcode Scanner** (USB/Wireless)
   - Contoh: Honeywell, Zebra, IWARE, atau genric USB scanner
   - Harga: Rp 300.000 - Rp 2.000.000

2. **Barcode Label** untuk produk
   - Format: EAN-13, Code-128, atau QR Code
   - Generator: Online gratis atau printer label

### **Cara Kerja:**

#### **A. Generate Barcode untuk Produk**
```
[Admin] → Data Produk → Tambah/Edit Produk
   ↓
Input SKU/Barcode manual ATAU Auto-generate
   ↓
Simpan ke field product.barcode (contoh: "8992761123456")
   ↓
Print barcode label → Tempelkan di produk fisik
```

**Auto-Generate Barcode (ProductController):**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required',
        'barcode' => 'nullable|unique:products',
        // ... fields lain
    ]);

    // Jika barcode kosong, auto-generate
    if (empty($validated['barcode'])) {
        $validated['barcode'] = 'PRD' . str_pad(Product::count() + 1, 10, '0', STR_PAD_LEFT);
        // Contoh output: PRD0000000001
    }

    $product = Product::create($validated);

    return redirect()->route('products.index');
}
```

#### **B. Penggunaan Barcode di POS**
```
[Kasir di POS] → Barcode Scanner siap
   ↓
Kasir scan barcode produk → Scanner kirim barcode
   ↓
JavaScript capture → AJAX Request:
GET /kasir/api/products/barcode/{barcode}
   ↓
Backend cari produk:
SELECT * FROM products WHERE barcode = '8992761123456'
   ↓
If found:
  - Load data produk (nama, harga, stok)
  - Auto-tambahkan ke keranjang
  - Alert "Produk ditambahkan"
Else:
  - Alert "Produk tidak ditemukan"
   ↓
Kasir lanjut scan produk lain → Checkout
```

**Kode Backend (PosController):**
```php
public function getProductByBarcode($barcode)
{
    $product = Product::with('category')
        ->where('barcode', $barcode)
        ->first();

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ], 404);
    }

    if ($product->stock <= 0) {
        return response()->json([
            'success' => false,
            'message' => 'Stok habis'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'product' => $product
    ]);
}
```

**Kode Frontend (POS Vue):**
```javascript
const barcodeInput = ref('');

const handleBarcodeInput = async () => {
    if (!barcodeInput.value) return;

    try {
        const response = await axios.get(`/kasir/api/products/barcode/${barcodeInput.value}`);

        if (response.data.success) {
            addToCart(response.data.product);
            showNotification(`${response.data.product.name} ditambahkan ke keranjang`, 'success');
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Produk tidak ditemukan', 'error');
    } finally {
        barcodeInput.value = '';
    }
};

// Hidden input untuk barcode scanner
<input
  id="hidden-scanner-input"
  v-model="barcodeInput"
  @keyup.enter="handleBarcodeInput"
  style="position: absolute; left: -9999px;"
/>
```

### **Setup Barcode Scanner:**

#### **Windows:**
1. Colokkan Barcode Scanner USB
2. Install driver (biasanya auto-detect sebagai HID Keyboard)
3. Test: Buka Notepad → Scan barcode → Barcode number muncul
4. Set scanner ke mode "Add Enter After Scan" (agar auto-submit)

---

## 6. 🗄️ Database Schema

### **Tabel: students**
```sql
CREATE TABLE students (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    nis VARCHAR(20) UNIQUE,
    nisn VARCHAR(20),
    kelas VARCHAR(50),
    jenjang ENUM('SMA Taruna Nusantara Indonesia', 'SMK Taruna Nusantara Jaya'),
    alamat_lengkap TEXT,
    rfid_uid VARCHAR(50) UNIQUE NULL, -- UID dari kartu RFID
    balance DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### **Tabel: transactions**
```sql
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT NOT NULL,
    type ENUM('top-up', 'pembelian', 'pengembalian'),
    amount DECIMAL(10,2),
    ending_balance DECIMAL(10,2), -- Saldo akhir setelah transaksi
    description TEXT,
    reference_type VARCHAR(50), -- 'sale', 'voucher', 'manual_topup'
    reference_id BIGINT NULL, -- ID dari reference_type
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    INDEX idx_student_created (student_id, created_at)
);
```

### **Tabel: sales**
```sql
CREATE TABLE sales (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL, -- Kasir yang melayani
    student_id BIGINT NULL, -- NULL jika cash, ada value jika balance
    total DECIMAL(10,2),
    payment_method ENUM('cash', 'balance'),
    cash_amount DECIMAL(10,2) NULL, -- Hanya untuk cash
    change_amount DECIMAL(10,2) NULL, -- Hanya untuk cash
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);
```

### **Tabel: sale_items**
```sql
CREATE TABLE sale_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sale_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity INT,
    price DECIMAL(10,2), -- Harga saat transaksi (snapshot)
    subtotal DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

### **Tabel: products**
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT,
    name VARCHAR(255),
    barcode VARCHAR(100) UNIQUE NULL,
    description TEXT,
    harga_beli DECIMAL(10,2),
    harga_jual DECIMAL(10,2),
    stock INT DEFAULT 0,
    image_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

### **Tabel: vouchers**
```sql
CREATE TABLE vouchers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE,
    nominal DECIMAL(10,2),
    is_used BOOLEAN DEFAULT 0,
    used_by BIGINT NULL, -- User ID admin yang redeem
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (used_by) REFERENCES users(id)
);
```

---

## 📊 Flow Diagram

### **Topup Manual:**
```
┌──────────┐     ┌──────────┐     ┌────────────┐     ┌──────────────┐
│  Admin   │────▶│ Search   │────▶│   Input    │────▶│   Database   │
│  Login   │     │ Student  │     │   Amount   │     │   Transaction│
└──────────┘     └──────────┘     └────────────┘     └──────────────┘
                                                              │
                                                              ▼
                                                      ┌──────────────┐
                                                      │ Update Saldo │
                                                      │ + Create Txn │
                                                      └──────────────┘
```

### **Transaksi POS dengan RFID:**
```
┌───────────┐     ┌──────────┐     ┌────────────┐     ┌──────────────┐
│  Kasir    │────▶│ Tap RFID │────▶│  Load Data │────▶│ Scan Produk  │
│  Login    │     │  Card    │     │  Student   │     │ (Barcode)    │
└───────────┘     └──────────┘     └────────────┘     └──────────────┘
                                                              │
                                                              ▼
                                                      ┌──────────────┐
                                                      │   Checkout   │
                                                      │  & Payment   │
                                                      └──────────────┘
                                                              │
                                                              ▼
                                                      ┌──────────────┐
                                                      │ Deduct Saldo │
                                                      │ Create Sale  │
                                                      │ Create Txn   │
                                                      └──────────────┘
```

---

## 🔧 Troubleshooting

### **RFID Scanner tidak terdeteksi:**
1. Cek koneksi USB
2. Install driver dari manufacturer
3. Test di Notepad (harus muncul text saat tap kartu)
4. Pastikan mode "Keyboard Emulation" aktif

### **Barcode Scanner tidak berfungsi:**
1. Cek koneksi USB/Bluetooth
2. Set scanner ke mode "HID Keyboard"
3. Enable "Add Enter After Scan"
4. Test scan di Notepad

### **Transaksi gagal:**
1. Cek koneksi database (MySQL running?)
2. Cek saldo siswa mencukupi
3. Cek stok produk tersedia
4. Lihat error di `storage/logs/laravel.log`

---

**Dokumentasi dibuat:** <?php echo date('Y-m-d'); ?>
**Sistem:** Koperasi Lemdiklat Taruna Nusantara Indonesia
**Tech Stack:** Laravel 12 + Vue 3 + Inertia.js + MySQL
