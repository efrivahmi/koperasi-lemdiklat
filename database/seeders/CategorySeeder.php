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
                'name' => 'Makanan & Minuman (Food & Beverage)',
                'description' => 'Kategori untuk toko kelontong, minimarket, supermarket.',
                'unit_group' => 'makanan', // Mapped to closest group
                'children' => [
                    ['name' => 'Sembako & Bahan Pokok', 'description' => 'Beras, Minyak Goreng, Gula, Terigu, Telur'],
                    ['name' => 'Makanan Instan & Kaleng', 'description' => 'Mie Instan, Sarden, Kornet, Bubur Instan'],
                    ['name' => 'Minuman Kemasan', 'description' => 'Air Mineral, Teh, Kopi Botol, Susu UHT, Jus, Soda'],
                    ['name' => 'Makanan Ringan (Snack)', 'description' => 'Keripik, Biskuit, Cokelat, Permen, Kacang'],
                    ['name' => 'Bumbu & Bahan Masak', 'description' => 'Kecap, Saus, Garam, MSG, Rempah, Santan'],
                    ['name' => 'Bahan Kue (Baking)', 'description' => 'Mentega, Ragi, Pewarna Makanan, Cokelat Masak'],
                    ['name' => 'Makanan Beku (Frozen Food)', 'description' => 'Nugget, Sosis, Bakso, Kentang Goreng, Es Krim'],
                    ['name' => 'Produk Susu & Olahan', 'description' => 'Susu Bubuk, Keju, Yogurt, Kental Manis'],
                    ['name' => 'Makanan Segar', 'description' => 'Sayur, Buah, Daging, Ikan'],
                ]
            ],
            [
                'name' => 'Kesehatan & Farmasi (Health & Medical)',
                'description' => 'Kategori untuk apotek dan toko obat.',
                'unit_group' => 'farmasi',
                'children' => [
                    ['name' => 'Obat Bebas (OTC)', 'description' => 'Obat Flu, Batuk, Sakit Kepala, Maag - Tanpa Resep'],
                    ['name' => 'Obat Keras (Resep)', 'description' => 'Antibiotik, Obat Jantung, Obat Diabetes - Wajib Resep'],
                    ['name' => 'Suplemen & Vitamin', 'description' => 'Vitamin C, Multivitamin, Penambah Darah'],
                    ['name' => 'Obat Herbal & Tradisional', 'description' => 'Jamu, Minyak Kayu Putih, Tolak Angin'],
                    ['name' => 'Alat Kesehatan (Alkes)', 'description' => 'Termometer, Tensi Darah, Alat Cek Gula, Kursi Roda'],
                    ['name' => 'P3K & Perawatan Luka', 'description' => 'Plester, Perban, Betadine, Kasa, Alkohol'],
                    ['name' => 'Kontrasepsi & Kesehatan Seksual', 'description' => 'Kondom, Pil KB, Testpack'],
                ]
            ],
            [
                'name' => 'Perawatan Diri & Kosmetik (Personal Care)',
                'description' => 'Kategori untuk minimarket dan toko kosmetik.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Perawatan Rambut', 'description' => 'Sampo, Kondisioner, Minyak Rambut, Pewarna Rambut'],
                    ['name' => 'Perawatan Tubuh (Bath & Body)', 'description' => 'Sabun Mandi, Lulur, Deodoran, Hand Sanitizer'],
                    ['name' => 'Perawatan Wajah (Skincare)', 'description' => 'Pembersih Muka, Toner, Serum, Krim Malam/Siang, Sunscreen'],
                    ['name' => 'Kosmetik & Make-up', 'description' => 'Lipstik, Bedak, Pensil Alis, Maskara'],
                    ['name' => 'Perawatan Gigi & Mulut', 'description' => 'Pasta Gigi, Sikat Gigi, Obat Kumur'],
                    ['name' => 'Perlengkapan Bayi & Anak', 'description' => 'Popok/Diapers, Minyak Telon, Bedak Bayi, Botol Susu'],
                    ['name' => 'Pembalut & Produk Kewanitaan', 'description' => ''],
                ]
            ],
            [
                'name' => 'Perlengkapan Rumah Tangga (Home & Living)',
                'description' => 'Kategori untuk toserba dan swalayan.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Kebersihan Rumah (Cleaning)', 'description' => 'Deterjen, Pewangi Pakaian, Pembersih Lantai, Sabun Cuci Piring'],
                    ['name' => 'Alat Kebersihan', 'description' => 'Sapu, Pel, Ember, Sikat, Lap'],
                    ['name' => 'Peralatan Dapur & Masak', 'description' => 'Panci, Wajan, Pisau, Spatula, Gas LPG'],
                    ['name' => 'Peralatan Makan & Minum', 'description' => 'Piring, Gelas, Sendok, Kotak Bekal, Botol Minum'],
                    ['name' => 'Kamar Tidur & Tekstil', 'description' => 'Sprei, Bantal, Selimut, Handuk, Keset'],
                    ['name' => 'Penyimpanan & Organizer', 'description' => 'Container Box, Rak Plastik, Gantungan Baju'],
                    ['name' => 'Dekorasi Rumah', 'description' => 'Jam Dinding, Vas Bunga, Cermin, Karpet'],
                    ['name' => 'Pembasmi Hama', 'description' => 'Obat Nyamuk, Kapur Barus, Racun Tikus'],
                ]
            ],
            [
                'name' => 'Elektronik & Gadget',
                'description' => 'Kategori untuk toko elektronik dan konter HP.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Handphone & Tablet', 'description' => ''],
                    ['name' => 'Komputer & Laptop', 'description' => 'PC Rakitan, Laptop, Monitor'],
                    ['name' => 'Aksesoris HP', 'description' => 'Casing, Charger, Kabel Data, Powerbank, Tempered Glass'],
                    ['name' => 'Aksesoris Komputer', 'description' => 'Mouse, Keyboard, Flashdisk, Harddisk Eksternal, Printer'],
                    ['name' => 'Elektronik Rumah Tangga', 'description' => 'TV, Kulkas, Mesin Cuci, AC, Kipas Angin, Rice Cooker, Blender'],
                    ['name' => 'Audio & Video', 'description' => 'Speaker, Headset/Earphone, TWS, Kamera, CCTV'],
                    ['name' => 'Komponen Elektronik & IoT', 'description' => 'Arduino, Sensor, Resistor, Lampu LED, Kabel Listrik'],
                    ['name' => 'Kelistrikan', 'description' => 'Stop Kontak, Saklar, Baterai, Bohlam Lampu'],
                ]
            ],
            [
                'name' => 'Pakaian & Mode (Fashion)',
                'description' => 'Kategori untuk distro, butik, atau toko baju.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Pakaian Pria', 'description' => 'Kaos, Kemeja, Celana Panjang/Pendek, Jaket, Batik'],
                    ['name' => 'Pakaian Wanita', 'description' => 'Blouse, Dress, Rok, Gamis, Tunic'],
                    ['name' => 'Pakaian Anak & Bayi', 'description' => ''],
                    ['name' => 'Pakaian Dalam & Tidur', 'description' => ''],
                    ['name' => 'Alas Kaki', 'description' => 'Sepatu, Sandal, Kaos Kaki'],
                    ['name' => 'Tas & Dompet', 'description' => ''],
                    ['name' => 'Aksesoris Mode', 'description' => 'Topi, Ikat Pinggang, Kacamata, Perhiasan, Jam Tangan'],
                    ['name' => 'Perlengkapan Ibadah', 'description' => 'Mukena, Sarung, Peci, Sajadah'],
                ]
            ],
            [
                'name' => 'Alat Tulis & Kantor (Stationery)',
                'description' => 'Kategori untuk toko buku dan fotokopi.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Kertas & Buku', 'description' => 'HVS, Buku Tulis, Buku Gambar, Amplop, Folio'],
                    ['name' => 'Alat Tulis', 'description' => 'Pulpen, Pensil, Spidol, Penghapus, Penggaris'],
                    ['name' => 'Alat Pengikat & Pemotong', 'description' => 'Stapler, Gunting, Cutter, Lem, Klip'],
                    ['name' => 'File & Organizer', 'description' => 'Map, Binder, Odner'],
                    ['name' => 'Perlengkapan Sekolah', 'description' => 'Tas Sekolah, Tempat Pensil, Sampul Buku'],
                    ['name' => 'Perlengkapan Kantor', 'description' => 'Stempel, Tinta Printer, Kalkulator'],
                ]
            ],
            [
                'name' => 'Pertukangan & Bangunan (Hardware & Tools)',
                'description' => 'Kategori untuk toko material.',
                'unit_group' => 'konstruksi',
                'children' => [
                    ['name' => 'Material Dasar', 'description' => 'Semen, Pasir, Bata, Besi Beton, Kayu'],
                    ['name' => 'Cat & Pelapis', 'description' => 'Cat Tembok, Cat Kayu/Besi, Thinner, Kuas, Roll'],
                    ['name' => 'Perpipaan (Plumbing)', 'description' => 'Pipa PVC, Keran Air, Sambungan Pipa, Pompa Air'],
                    ['name' => 'Alat Perkakas (Tools)', 'description' => 'Palu, Obeng, Tang, Gergaji, Bor, Gerinda'],
                    ['name' => 'Kunci & Gembok', 'description' => 'Handle Pintu, Engsel, Gembok'],
                    ['name' => 'Paku & Baut', 'description' => 'Paku, Sekrup, Mur, Baut, Fisher'],
                    ['name' => 'Lantai & Dinding', 'description' => 'Keramik, Granit, Wallpaper'],
                ]
            ],
            [
                'name' => 'Otomotif & Kendaraan',
                'description' => 'Kategori untuk bengkel dan toko variasi.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Suku Cadang Motor', 'description' => 'Kampas Rem, Busi, Filter Udara, Rantai, Gir'],
                    ['name' => 'Suku Cadang Mobil', 'description' => ''],
                    ['name' => 'Oli & Cairan', 'description' => 'Oli Mesin, Oli Gardan, Minyak Rem, Air Radiator'],
                    ['name' => 'Ban & Velg', 'description' => 'Ban Luar, Ban Dalam'],
                    ['name' => 'Aksesoris Kendaraan', 'description' => 'Helm, Jaket Motor, Cover Jok, Stiker, Parfum Mobil'],
                    ['name' => 'Perawatan Kendaraan', 'description' => 'Sampo Motor, Kit Poles, Lap Chamois'],
                ]
            ],
            [
                'name' => 'Hobi, Mainan & Olahraga',
                'description' => 'Kategori hiburan dan hobi.',
                'unit_group' => 'umum',
                'children' => [
                    ['name' => 'Mainan Anak', 'description' => 'Boneka, Mobil-mobilan, Lego/Balok, Mainan Edukasi'],
                    ['name' => 'Video Game & Konsol', 'description' => 'PS, Xbox, Nintendo, Kaset Game'],
                    ['name' => 'Olahraga', 'description' => 'Bola, Raket, Sepatu Olahraga, Jersey, Alat Gym'],
                    ['name' => 'Hobi & Koleksi', 'description' => 'Action Figure, Diecast, Alat Pancing, Alat Musik'],
                    ['name' => 'Makanan & Perlengkapan Hewan (Petshop)', 'description' => 'Makanan Kucing/Anjing, Pasir Kucing, Kandang'],
                ]
            ],
            [
                'name' => 'Pertanian & Peternakan (Agro)',
                'description' => 'Kategori untuk toko tani.',
                'unit_group' => 'logistik',
                'children' => [
                    ['name' => 'Benih & Bibit', 'description' => 'Benih Sayur, Bibit Buah, Bibit Bunga'],
                    ['name' => 'Pupuk & Nutrisi', 'description' => 'Urea, NPK, Pupuk Cair, Kompos'],
                    ['name' => 'Obat Tanaman (Pestisida)', 'description' => 'Insektisida, Herbisida, Fungisida'],
                    ['name' => 'Alat Pertanian', 'description' => 'Cangkul, Semprotan/Sprayer, Polybag, Pot'],
                    ['name' => 'Pakan Ternak', 'description' => 'Dedak, Konsentrat, Jagung, Pur Ayam'],
                ]
            ],
            [
                'name' => 'Produk Digital & Jasa (Virtual)',
                'description' => 'Kategori untuk konter PPOB.',
                'unit_group' => 'digital',
                'children' => [
                    ['name' => 'Pulsa & Data', 'description' => 'Pulsa Reguler, Paket Data Internet, Paket Nelpon'],
                    ['name' => 'Tagihan & Pembayaran', 'description' => 'Token Listrik, Bayar Air, BPJS, Indihome'],
                    ['name' => 'Voucher & Top Up', 'description' => 'Voucher Game, Saldo E-Wallet: Dana/OVO/Gopay'],
                    ['name' => 'Jasa Layanan', 'description' => 'Servis Elektronik, Jasa Ketik, Jasa Print, Ongkos Kirim'],
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);
            
            // Create Parent
            $parent = Category::create($catData);

            // Create Children
            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                $childData['unit_group'] = $parent->unit_group; // Inherit unit group
                Category::create($childData);
            }
        }
    }
}
