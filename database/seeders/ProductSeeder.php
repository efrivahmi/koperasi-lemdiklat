<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'category_id' => 1, // Makanan
                'name' => 'Nasi Goreng',
                'description' => 'Nasi goreng spesial dengan telur',
                'stock' => 20,
                'harga_beli' => 8000,
                'harga_jual' => 12000,
                'barcode' => '1001',
            ],
            [
                'category_id' => 1, // Makanan
                'name' => 'Mie Ayam',
                'description' => 'Mie ayam dengan pangsit',
                'stock' => 15,
                'harga_beli' => 7000,
                'harga_jual' => 10000,
                'barcode' => '1002',
            ],
            [
                'category_id' => 2, // Minuman
                'name' => 'Teh Botol',
                'description' => 'Teh kemasan botol 500ml',
                'stock' => 50,
                'harga_beli' => 3500,
                'harga_jual' => 5000,
                'barcode' => '2001',
            ],
            [
                'category_id' => 2, // Minuman
                'name' => 'Air Mineral',
                'description' => 'Air mineral kemasan 600ml',
                'stock' => 100,
                'harga_beli' => 2000,
                'harga_jual' => 3000,
                'barcode' => '2002',
            ],
            [
                'category_id' => 3, // Alat Tulis
                'name' => 'Pensil 2B',
                'description' => 'Pensil 2B untuk ujian',
                'stock' => 200,
                'harga_beli' => 1500,
                'harga_jual' => 2500,
                'barcode' => '3001',
            ],
            [
                'category_id' => 3, // Alat Tulis
                'name' => 'Buku Tulis 38 Lembar',
                'description' => 'Buku tulis ukuran folio 38 lembar',
                'stock' => 150,
                'harga_beli' => 3000,
                'harga_jual' => 5000,
                'barcode' => '3002',
            ],
            [
                'category_id' => 3, // Alat Tulis
                'name' => 'Penghapus',
                'description' => 'Penghapus karet putih',
                'stock' => 5, // Low stock untuk testing warning
                'harga_beli' => 500,
                'harga_jual' => 1000,
                'barcode' => '3003',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
