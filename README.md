# 🏪 Sistem Informasi Koperasi Lemdiklat Taruna Nusantara

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.4-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.2-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**Sistem Manajemen Koperasi Modern dengan Fitur Point of Sale, E-Wallet Siswa, dan Pelaporan Komprehensif**

[Dokumentasi](#-dokumentasi) • [Instalasi](#-instalasi) • [Fitur](#-fitur-utama) • [Kontribusi](#-kontribusi)

</div>

---

## 📖 Tentang Proyek

**Sistem Informasi Koperasi Lemdiklat** adalah aplikasi web modern yang dirancang khusus untuk mengelola operasional koperasi lembaga pendidikan. Sistem ini mengintegrasikan Point of Sale (POS), manajemen inventori, sistem e-wallet siswa, serta pelaporan keuangan dan transaksi yang komprehensif.

Dikembangkan dengan teknologi terkini menggunakan Laravel 12 dan Vue.js 3, aplikasi ini menawarkan pengalaman pengguna yang responsif, cepat, dan intuitif. Sistem ini mendukung multi-role user (Admin, Master, Kasir, Siswa) dengan permission management yang fleksibel.

### 🎯 Tujuan Pengembangan

- Digitalisasi penuh operasional koperasi sekolah/lembaga pendidikan
- Mempermudah transaksi penjualan dengan dukungan RFID dan barcode scanner
- Memberikan transparansi penuh dalam pengelolaan keuangan dan inventori
- Meningkatkan efisiensi operasional dengan sistem terintegrasi
- Menyediakan laporan real-time untuk pengambilan keputusan strategis

---

## ✨ Fitur Utama

### 🛒 Point of Sale (POS)
- **Multi-Mode Transaksi**: Manual, RFID Card, Barcode Scanner
- **Real-time Product Search**: Pencarian produk cepat dengan autocomplete
- **Student E-Wallet Integration**: Pembayaran menggunakan saldo siswa
- **Receipt Printing**: Cetak struk transaksi otomatis
- **Transaction History**: Riwayat transaksi lengkap dengan filter
- **Void Transaction**: Pembatalan transaksi dengan audit trail

### 💳 Manajemen Saldo Siswa (E-Wallet)
- **Top-up Balance**: Pengisian saldo manual oleh kasir
- **RFID Card Registration**: Pendaftaran kartu RFID siswa
- **Transaction Tracking**: Pelacakan setiap transaksi siswa
- **Balance Limits**: Batas maksimal saldo dan top-up
- **Student Card Printing**: Cetak kartu identitas siswa dengan barcode

### 📦 Manajemen Inventori
- **Product Management**: CRUD produk lengkap dengan kategori
- **Stock Monitoring**: Monitoring stok real-time dengan alert low stock
- **Stock Adjustment**: Penyesuaian stok dengan purpose tracking
- **Barcode Generation**: Generate barcode otomatis untuk produk
- **Stock Movement History**: Histori pergerakan stok detail

### 🎫 Sistem Voucher
- **Voucher Creation**: Pembuatan voucher dengan kode unik
- **Expiry Management**: Manajemen masa berlaku voucher
- **Redemption Tracking**: Pelacakan penukaran voucher
- **Voucher Printing**: Cetak voucher untuk distribusi

### 📊 Pelaporan & Analitik
- **Sales Report**: Laporan penjualan dengan filter multi-parameter
- **Financial Report**: Laporan keuangan lengkap (revenue, profit, expenses)
- **Inventory Report**: Laporan stok dan valuasi inventori
- **Student Transaction Report**: Laporan transaksi per siswa
- **Stock Adjustment Report**: Laporan penyesuaian stok
- **Excel Export**: Export semua laporan ke format Excel
- **Dashboard Analytics**: Visualisasi data dengan Chart.js

### 👥 Manajemen User & Permission
- **Multi-Role System**: Admin, Master, Kasir, Siswa
- **Granular Permissions**: Permission-based access control
- **User Profile Management**: Manajemen profil dengan foto
- **Audit Trail**: Log aktivitas user (created_by, updated_by)

### 🎨 User Interface & Experience
- **Responsive Design**: Optimized untuk desktop, tablet, dan mobile
- **Galaxy Theme**: Theme modern dengan gradient animasi
- **Real-time Notifications**: Notifikasi real-time untuk event penting
- **Keyboard Shortcuts**: Shortcut keyboard untuk operasi cepat

---

## 🛠 Teknologi yang Digunakan

### Backend
| Teknologi | Versi | Deskripsi |
|-----------|-------|-----------|
| **PHP** | 8.2+ | Bahasa pemrograman backend |
| **Laravel** | 12.0 | PHP Framework modern dengan fitur lengkap |
| **Inertia.js** | 2.0 | Modern monolith untuk SPA |
| **MySQL** | 8.0+ | Relational database management system |
| **Laravel Sanctum** | 4.0 | API authentication |
| **Maatwebsite Excel** | 3.1 | Excel import/export |

### Frontend
| Teknologi | Versi | Deskripsi |
|-----------|-------|-----------|
| **Vue.js** | 3.4 | Progressive JavaScript framework |
| **Tailwind CSS** | 3.2 | Utility-first CSS framework |
| **Chart.js** | 4.5 | JavaScript charting library |
| **JSBarcode** | 3.12 | Barcode generation library |
| **Vite** | 6.0 | Next generation frontend tooling |
| **Axios** | 1.11 | Promise-based HTTP client |

### Development Tools
- **Laravel Pint**: PHP code style fixer
- **Laravel Pail**: Real-time log viewer
- **Concurrently**: Run multiple commands concurrently
- **PostCSS**: CSS transformation tool
- **Autoprefixer**: CSS vendor prefixer

---

## 🗄 Struktur Database

### Entity Relationship Diagram (ERD)

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│    users    │───────│   students   │───────│transactions │
└─────────────┘       └──────────────┘       └─────────────┘
      │                      │
      │                      │
      ▼                      ▼
┌─────────────┐       ┌──────────────┐
│   sales     │───────│  sale_items  │
└─────────────┘       └──────────────┘
      │                      │
      │                      ▼
      │               ┌──────────────┐       ┌─────────────┐
      │               │   products   │───────│ categories  │
      │               └──────────────┘       └─────────────┘
      │                      │
      │                      ▼
      │               ┌──────────────┐
      │               │  stock_ins   │
      │               └──────────────┘
      │                      │
      │                      ▼
      │               ┌──────────────────────┐
      └───────────────│ stock_adjustments    │
                      └──────────────────────┘

                      ┌──────────────┐
                      │   vouchers   │
                      └──────────────┘

                      ┌──────────────┐
                      │   expenses   │
                      └──────────────┘
```

### Tabel Utama

Sistem ini memiliki 11 tabel utama dengan 36 migrations untuk mengelola seluruh operasional koperasi:

- **users** - Data user sistem (Admin, Kasir, Siswa)
- **students** - Data siswa dan saldo e-wallet
- **products** - Data produk
- **categories** - Kategori produk
- **sales** - Header transaksi penjualan
- **sale_items** - Detail item penjualan
- **transactions** - Riwayat transaksi saldo siswa
- **stock_ins** - Pembelian/stok masuk
- **stock_adjustments** - Penyesuaian stok
- **vouchers** - Data voucher
- **expenses** - Pengeluaran operasional

---

## 💻 Persyaratan Sistem

### Minimum Requirements

- **PHP**: 8.2 atau lebih tinggi
- **Composer**: 2.0 atau lebih tinggi
- **Node.js**: 18.0 atau lebih tinggi
- **NPM**: 9.0 atau lebih tinggi
- **MySQL**: 8.0 atau lebih tinggi
- **Web Server**: Apache/Nginx
- **RAM**: Minimal 2GB
- **Storage**: Minimal 1GB free space

### PHP Extensions (Required)

```
BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, GD
```

---

## 📥 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/efrivahmi/koperasi-lemdiklat.git
cd koperasi-lemdiklat
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi_lemdiklat
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Database Migration & Seeding

```bash
# Jalankan migration
php artisan migrate

# (Opsional) Seed data dummy untuk testing
php artisan db:seed
```

### 6. Storage Link

```bash
# Buat symbolic link untuk storage
php artisan storage:link
```

### 7. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 8. Jalankan Aplikasi

```bash
# Development server
php artisan serve

# Atau gunakan composer script untuk menjalankan semua service
composer run dev
```

Aplikasi akan berjalan di: `http://localhost:8000`

---

## ⚙️ Konfigurasi

### Business Configuration

Edit file `config/business.php` untuk menyesuaikan pengaturan bisnis:

```php
return [
    'inventory' => [
        'low_stock_threshold' => 10,  // Batas stok rendah
    ],
    'balance' => [
        'max_balance' => 50000000,    // Maksimal saldo siswa
        'min_topup' => 1000,          // Minimal top-up
        'max_topup' => 10000000,      // Maksimal top-up
    ],
];
```

### Default Login Credentials

Setelah seeding, gunakan credentials berikut:

```
Admin:
Email: admin@example.com
Password: password

Kasir:
Email: kasir@example.com
Password: password
```

**⚠️ PENTING**: Segera ubah password default setelah login pertama!

---

## 📂 Struktur Proyek

```
koperasi-lemdiklat/
├── app/
│   ├── Http/Controllers/      # Application controllers (30+ controllers)
│   ├── Models/                # Eloquent models (11 models)
│   ├── Exports/               # Excel export classes (5 exporters)
│   ├── Traits/                # Reusable traits
│   └── Services/              # Business logic services
│
├── config/
│   ├── business.php           # Business rules configuration
│   └── school.php             # School information
│
├── database/
│   └── migrations/            # 36 database migrations
│
├── resources/
│   ├── js/
│   │   ├── Components/        # Vue components (12 components)
│   │   ├── Layouts/           # Page layouts (2 layouts)
│   │   └── Pages/             # Inertia pages (80+ pages)
│   └── views/                 # Blade templates for printing
│
└── routes/
    └── web.php                # 127 lines of organized routes
```

---

## 📊 Optimasi Performa

### Database Indexing

Sistem sudah dilengkapi dengan index pada kolom-kolom penting:
- `students.rfid_uid` - Pencarian RFID cepat
- `products.barcode` - Pencarian barcode cepat
- `sales.created_at` - Filtering laporan
- `transactions.student_id` - Riwayat transaksi siswa

### Query Optimization

- ✅ Eager loading untuk menghindari N+1 query
- ✅ Database transaction untuk data consistency
- ✅ Aggregate queries untuk reporting (7x queries → 1 query)
- ✅ Rate limiting pada API endpoints

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` di `.env`
- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Generate production key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Build assets: `npm run build`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Set proper permissions untuk `storage/` dan `bootstrap/cache/`
- [ ] Setup SSL certificate
- [ ] Setup database backup schedule

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Untuk berkontribusi:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👨‍💻 Author

**efrivahmi**

- GitHub: [@efrivahmi](https://github.com/efrivahmi)
- Email: efri.crb@gmail.com

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP Framework
- [Vue.js](https://vuejs.org/) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com/) - The Modern Monolith
- [Tailwind CSS](https://tailwindcss.com/) - A utility-first CSS framework
- [Chart.js](https://www.chartjs.org/) - Simple yet flexible JavaScript charting

---

## 📞 Support

Jika Anda mengalami masalah atau memiliki pertanyaan:

1. Cek [Issues](https://github.com/efrivahmi/koperasi-lemdiklat/issues) yang sudah ada
2. Buat [New Issue](https://github.com/efrivahmi/koperasi-lemdiklat/issues/new) jika belum ada
3. Hubungi via email: efri.crb@gmail.com

---

## 🔄 Changelog

### Version 1.0.0 (2025-12-14)

#### Added
- ✨ Point of Sale system dengan multi-mode transaksi
- ✨ Student e-wallet management
- ✨ Comprehensive reporting system
- ✨ Stock adjustment dengan purpose tracking
- ✨ Voucher management system
- ✨ RFID & Barcode integration
- ✨ Excel export untuk semua laporan
- ✨ Dashboard analytics dengan Chart.js
- ✨ Multi-role & permission system
- ✨ Audit trail untuk semua transaksi

#### Optimized
- ⚡ Database indexing untuk query performa
- ⚡ Query optimization (7x queries → 1 query)
- ⚡ Rate limiting pada API endpoints
- ⚡ Password validation yang lebih kuat
- ⚡ Config-based business rules

---

<div align="center">

**Made with ❤️ by efrivahmi**

⭐ Jika proyek ini membantu, berikan star di repository!

[⬆ Back to Top](#-sistem-informasi-koperasi-lemdiklat-taruna-nusantara)

</div>
