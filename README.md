# Koperasi Lemdiklat

Sistem manajemen koperasi dengan fitur lengkap untuk Point of Sale, manajemen siswa, dan pelaporan.

## Fitur Utama

- Point of Sale (POS) dengan dukungan RFID/Barcode
- Manajemen Student Balance (E-Wallet)
- Inventory Management
- Voucher System
- Financial Reporting
- Multi-Role System (Admin, Kasir, Siswa)

## Tech Stack

- Backend: Laravel 12
- Frontend: Vue.js 3 + Inertia.js
- Database: MySQL
- Styling: Tailwind CSS

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Contributors

- efrivahmi - Main Developer

