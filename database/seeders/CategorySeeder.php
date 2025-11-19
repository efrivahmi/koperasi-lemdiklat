<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'description' => 'Kategori untuk produk makanan',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Kategori untuk produk minuman',
            ],
            [
                'name' => 'Alat Tulis',
                'description' => 'Kategori untuk alat tulis dan perlengkapan sekolah',
            ],
            [
                'name' => 'Seragam',
                'description' => 'Kategori untuk seragam sekolah',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
