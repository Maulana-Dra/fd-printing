<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->getCatalog();
        $totalProducts = 0;
        $totalOptions  = 0;

        foreach ($catalog as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($products as $productData) {
                $options = $productData['options'] ?? [];
                unset($productData['options']);

                $product = Product::firstOrCreate(
                    ['slug' => Str::slug($productData['name'])],
                    [
                        ...$productData,
                        'slug'        => Str::slug($productData['name']),
                        'category_id' => $category->id,
                        'is_active'   => true,
                    ],
                );

                foreach ($options as $sortOrder => $option) {
                    ProductOption::firstOrCreate(
                        [
                            'product_id'  => $product->id,
                            'group_name'  => $option['group_name'],
                            'option_name' => $option['option_name'],
                        ],
                        [
                            'sort_order'     => $sortOrder,
                            'price_modifier' => $option['price_modifier'],
                            'modifier_type'  => $option['modifier_type'] ?? 'fixed',
                            'is_default'     => $option['is_default'] ?? false,
                        ],
                    );
                    $totalOptions++;
                }

                $totalProducts++;
            }
        }

        $this->command->info("✅ ProductSeeder: {$totalProducts} produk dan {$totalOptions} opsi berhasil dibuat.");
    }

    // ── Catalog Data ──────────────────────────────────────────────────────────

    private function getCatalog(): array
    {
        return [

            // ─── 1. Cetak Sticker ──────────────────────────────────────────
            'Cetak Sticker' => [
                [
                    'name'            => 'Sticker Vinyl Glossy',
                    'description'     => 'Sticker berbahan vinyl berkualitas tinggi dengan finishing glossy. Tahan air, tahan panas, dan cocok untuk penggunaan indoor maupun outdoor.',
                    'base_price'      => 2500,
                    'unit'            => 'lembar',
                    'min_qty'         => 10,
                    'weight_per_unit' => 5,
                    'sort_order'      => 1,
                    'options'         => [
                        ['group_name' => 'Ukuran', 'option_name' => 'A4 (21x29,7 cm)',  'price_modifier' => 0,    'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'A3 (29,7x42 cm)', 'price_modifier' => 1500, 'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'A2 (42x59,4 cm)', 'price_modifier' => 3500, 'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Glossy',        'price_modifier' => 0,    'is_default' => true],
                        ['group_name' => 'Finishing', 'option_name' => 'Doff / Matte',  'price_modifier' => 500,  'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Emboss',         'price_modifier' => 1500, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Sticker Transparan',
                    'description'     => 'Sticker berbahan bening/transparan, ideal untuk kemasan produk, botol, dan display. Hasil cetak terlihat melayang di permukaan.',
                    'base_price'      => 3500,
                    'unit'            => 'lembar',
                    'min_qty'         => 10,
                    'weight_per_unit' => 5,
                    'sort_order'      => 2,
                    'options'         => [
                        ['group_name' => 'Ukuran',   'option_name' => 'A4 (21x29,7 cm)', 'price_modifier' => 0,    'is_default' => true],
                        ['group_name' => 'Ukuran',   'option_name' => 'A3 (29,7x42 cm)', 'price_modifier' => 2000, 'is_default' => false],
                        ['group_name' => 'Pemotongan', 'option_name' => 'Tanpa Pemotongan', 'price_modifier' => 0, 'is_default' => true],
                        ['group_name' => 'Pemotongan', 'option_name' => 'Die Cut (Bentuk Custom)', 'price_modifier' => 1500, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Label Produk / Kemasan',
                    'description'     => 'Label sticker khusus kemasan produk. Tersedia dalam bahan art paper, vinyl, dan silver foil. Cocok untuk UMKM dan brand lokal.',
                    'base_price'      => 1500,
                    'unit'            => 'lembar',
                    'min_qty'         => 50,
                    'weight_per_unit' => 3,
                    'sort_order'      => 3,
                    'options'         => [
                        ['group_name' => 'Bahan',    'option_name' => 'Art Paper 120gr',  'price_modifier' => 0,    'is_default' => true],
                        ['group_name' => 'Bahan',    'option_name' => 'Vinyl Putih',      'price_modifier' => 800,  'is_default' => false],
                        ['group_name' => 'Bahan',    'option_name' => 'Silver Foil',      'price_modifier' => 2000, 'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Glossy Lamination', 'price_modifier' => 200, 'is_default' => true],
                        ['group_name' => 'Finishing', 'option_name' => 'Doff Lamination',   'price_modifier' => 200, 'is_default' => false],
                    ],
                ],
            ],

            // ─── 2. Digital Print ──────────────────────────────────────────
            'Digital Print' => [
                [
                    'name'            => 'Banner Flexi / Spanduk',
                    'description'     => 'Cetak banner atau spanduk dengan bahan flexi berkualitas. Cocok untuk event, promosi toko, dan dekorasi indoor/outdoor.',
                    'base_price'      => 15000,
                    'unit'            => 'm²',
                    'min_qty'         => 1,
                    'weight_per_unit' => 550,
                    'sort_order'      => 1,
                    'options'         => [
                        ['group_name' => 'Bahan',    'option_name' => 'Flexi China 340gr',  'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',    'option_name' => 'Flexi Korea 440gr',  'price_modifier' => 5000,  'is_default' => false],
                        ['group_name' => 'Bahan',    'option_name' => 'Kain (Tetron)',       'price_modifier' => 15000, 'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Tanpa Ojek',    'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Finishing', 'option_name' => 'Dengan Ojek Kayu', 'price_modifier' => 12000, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Poster Indoor',
                    'description'     => 'Cetak poster untuk keperluan indoor dengan bahan art paper premium. Warna tajam, detail tinggi, cocok untuk display toko dan pameran.',
                    'base_price'      => 8000,
                    'unit'            => 'lembar',
                    'min_qty'         => 1,
                    'weight_per_unit' => 120,
                    'sort_order'      => 2,
                    'options'         => [
                        ['group_name' => 'Ukuran', 'option_name' => 'A3 (29,7x42 cm)',   'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'A2 (42x59,4 cm)',   'price_modifier' => 8000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'A1 (59,4x84,1 cm)', 'price_modifier' => 20000, 'is_default' => false],
                        ['group_name' => 'Kertas', 'option_name' => 'Art Paper 150gr',   'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Kertas', 'option_name' => 'Art Paper 260gr',   'price_modifier' => 3000,  'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Backwall / Backdrop',
                    'description'     => 'Cetak backdrop besar untuk photobooth, event, pameran, dan dekorasi pernikahan. Tersedia berbagai ukuran custom.',
                    'base_price'      => 18000,
                    'unit'            => 'm²',
                    'min_qty'         => 2,
                    'weight_per_unit' => 600,
                    'sort_order'      => 3,
                    'options'         => [
                        ['group_name' => 'Bahan',    'option_name' => 'Flexi Korea 440gr',  'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',    'option_name' => 'Kain Satin',          'price_modifier' => 20000, 'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Tanpa Rangka',       'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Finishing', 'option_name' => 'Dengan Rangka Lipat', 'price_modifier' => 85000, 'is_default' => false],
                    ],
                ],
            ],

            // ─── 3. Media Promosi ──────────────────────────────────────────
            'Media Promosi' => [
                [
                    'name'            => 'Brosur / Flyer',
                    'description'     => 'Cetak brosur dan flyer promosi dengan hasil cetak tajam dan warna vivid. Tersedia dalam berbagai ukuran dan pilihan kertas.',
                    'base_price'      => 500,
                    'unit'            => 'lembar',
                    'min_qty'         => 100,
                    'weight_per_unit' => 8,
                    'sort_order'      => 1,
                    'options'         => [
                        ['group_name' => 'Ukuran',   'option_name' => 'A5 (14,8x21 cm)', 'price_modifier' => 0,   'is_default' => true],
                        ['group_name' => 'Ukuran',   'option_name' => 'A4 (21x29,7 cm)', 'price_modifier' => 300, 'is_default' => false],
                        ['group_name' => 'Kertas',   'option_name' => 'Art Paper 150gr', 'price_modifier' => 0,   'is_default' => true],
                        ['group_name' => 'Kertas',   'option_name' => 'Art Paper 260gr', 'price_modifier' => 100, 'is_default' => false],
                        ['group_name' => 'Cetak',    'option_name' => '1 Sisi',           'price_modifier' => 0,   'is_default' => true],
                        ['group_name' => 'Cetak',    'option_name' => '2 Sisi',           'price_modifier' => 200, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Kartu Nama',
                    'description'     => 'Cetak kartu nama profesional dengan pilihan bahan premium. Tersedia dalam finishing glossy, doff, dan spot UV untuk kesan eksklusif.',
                    'base_price'      => 250,
                    'unit'            => 'lembar',
                    'min_qty'         => 100,
                    'weight_per_unit' => 4,
                    'sort_order'      => 2,
                    'options'         => [
                        ['group_name' => 'Bahan',    'option_name' => 'Art Carton 260gr',  'price_modifier' => 0,   'is_default' => true],
                        ['group_name' => 'Bahan',    'option_name' => 'Linen Emboss 310gr', 'price_modifier' => 100, 'is_default' => false],
                        ['group_name' => 'Bahan',    'option_name' => 'Soft Touch (Black)', 'price_modifier' => 300, 'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Glossy Lamination', 'price_modifier' => 0,   'is_default' => true],
                        ['group_name' => 'Finishing', 'option_name' => 'Doff Lamination',   'price_modifier' => 50,  'is_default' => false],
                        ['group_name' => 'Finishing', 'option_name' => 'Spot UV',            'price_modifier' => 200, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Kalender Dinding',
                    'description'     => 'Kalender dinding custom dengan desain sendiri. Cocok untuk souvenir akhir tahun, media promosi, dan hadiah pelanggan setia.',
                    'base_price'      => 35000,
                    'unit'            => 'pcs',
                    'min_qty'         => 50,
                    'weight_per_unit' => 350,
                    'sort_order'      => 3,
                    'options'         => [
                        ['group_name' => 'Ukuran', 'option_name' => 'A3 (29,7x42 cm)',  'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'A2 (42x59,4 cm)',  'price_modifier' => 15000, 'is_default' => false],
                        ['group_name' => 'Jilid',  'option_name' => 'Ring Kawat',        'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Jilid',  'option_name' => 'Spiral Plastik',    'price_modifier' => 5000,  'is_default' => false],
                    ],
                ],
            ],

            // ─── 4. Kaos & Garment ────────────────────────────────────────
            'Kaos & Garment' => [
                [
                    'name'            => 'Kaos Sablon Custom',
                    'description'     => 'Kaos polos sablon plastisol berkualitas tinggi. Cocok untuk seragam komunitas, merchandise band, event olahraga, dan corporate uniform.',
                    'base_price'      => 65000,
                    'unit'            => 'pcs',
                    'min_qty'         => 12,
                    'weight_per_unit' => 180,
                    'sort_order'      => 1,
                    'options'         => [
                        ['group_name' => 'Bahan',  'option_name' => 'Cotton Combed 24s',  'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',  'option_name' => 'Cotton Combed 30s',  'price_modifier' => 5000,  'is_default' => false],
                        ['group_name' => 'Bahan',  'option_name' => 'CVC 20s',            'price_modifier' => 8000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'S / M / L',          'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'XL',                  'price_modifier' => 2000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'XXL',                 'price_modifier' => 5000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'XXXL',                'price_modifier' => 8000,  'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Polo Shirt Bordir',
                    'description'     => 'Polo shirt dengan teknik bordir logo dan teks. Tampilannya lebih profesional dan elegan, ideal untuk seragam kantor dan corporate uniform.',
                    'base_price'      => 95000,
                    'unit'            => 'pcs',
                    'min_qty'         => 12,
                    'weight_per_unit' => 220,
                    'sort_order'      => 2,
                    'options'         => [
                        ['group_name' => 'Bahan',  'option_name' => 'Lacoste PE',          'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',  'option_name' => 'Lacoste CVC',         'price_modifier' => 10000, 'is_default' => false],
                        ['group_name' => 'Bahan',  'option_name' => 'Pique Premium',       'price_modifier' => 20000, 'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'S / M / L',           'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'XL',                   'price_modifier' => 3000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'XXL',                  'price_modifier' => 7000,  'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Hoodie Sablon',
                    'description'     => 'Hoodie dengan sablon DTF (Direct Transfer Film) untuk hasil warna cerah dan tahan lama. Tersedia dalam bahan fleece dan babyterry.',
                    'base_price'      => 175000,
                    'unit'            => 'pcs',
                    'min_qty'         => 6,
                    'weight_per_unit' => 450,
                    'sort_order'      => 3,
                    'options'         => [
                        ['group_name' => 'Bahan',  'option_name' => 'Fleece Standar',    'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',  'option_name' => 'Babyterry',         'price_modifier' => 15000, 'is_default' => false],
                        ['group_name' => 'Bahan',  'option_name' => 'French Terry',      'price_modifier' => 25000, 'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'S / M / L',         'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Ukuran', 'option_name' => 'XL',                 'price_modifier' => 5000,  'is_default' => false],
                        ['group_name' => 'Ukuran', 'option_name' => 'XXL',                'price_modifier' => 10000, 'is_default' => false],
                    ],
                ],
            ],

            // ─── 5. Merchandise ───────────────────────────────────────────
            'Merchandise' => [
                [
                    'name'            => 'Mug Sablon Custom',
                    'description'     => 'Mug keramik dengan cetak foto atau desain custom. Cocok untuk souvenir pernikahan, ulang tahun, dan hadiah corporate.',
                    'base_price'      => 45000,
                    'unit'            => 'pcs',
                    'min_qty'         => 12,
                    'weight_per_unit' => 350,
                    'sort_order'      => 1,
                    'options'         => [
                        ['group_name' => 'Jenis Mug', 'option_name' => 'Mug Putih Standar 11oz', 'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Jenis Mug', 'option_name' => 'Mug Magic (Berubah Warna)', 'price_modifier' => 15000, 'is_default' => false],
                        ['group_name' => 'Jenis Mug', 'option_name' => 'Mug Travel / Thermos',    'price_modifier' => 35000, 'is_default' => false],
                        ['group_name' => 'Kemasan',   'option_name' => 'Tanpa Kemasan',           'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Kemasan',   'option_name' => 'Kotak Hadiah (Gift Box)', 'price_modifier' => 5000,  'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Tote Bag Custom',
                    'description'     => 'Tote bag ramah lingkungan dengan cetak sablon atau DTF. Cocok untuk goodie bag event, promosi brand, dan souvenir.',
                    'base_price'      => 35000,
                    'unit'            => 'pcs',
                    'min_qty'         => 12,
                    'weight_per_unit' => 120,
                    'sort_order'      => 2,
                    'options'         => [
                        ['group_name' => 'Bahan',   'option_name' => 'Canvas 12oz',       'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Bahan',   'option_name' => 'Canvas 16oz Tebal', 'price_modifier' => 8000,  'is_default' => false],
                        ['group_name' => 'Bahan',   'option_name' => 'Spunbond',          'price_modifier' => -10000, 'is_default' => false],
                        ['group_name' => 'Sablon',  'option_name' => '1 Sisi',            'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Sablon',  'option_name' => '2 Sisi',            'price_modifier' => 10000, 'is_default' => false],
                    ],
                ],
                [
                    'name'            => 'Payung Souvenir',
                    'description'     => 'Payung souvenir dengan cetak logo dan desain custom di seluruh permukaan. Pilihan terbaik untuk souvenir pernikahan dan promosi brand.',
                    'base_price'      => 55000,
                    'unit'            => 'pcs',
                    'min_qty'         => 12,
                    'weight_per_unit' => 280,
                    'sort_order'      => 3,
                    'options'         => [
                        ['group_name' => 'Jenis',   'option_name' => 'Lipat 3 (Compact)',  'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Jenis',   'option_name' => 'Golf / Stick',       'price_modifier' => 25000, 'is_default' => false],
                        ['group_name' => 'Sablon',  'option_name' => '1 Panel Warna',      'price_modifier' => 0,     'is_default' => true],
                        ['group_name' => 'Sablon',  'option_name' => 'Full Printing (8 Panel)', 'price_modifier' => 20000, 'is_default' => false],
                    ],
                ],
            ],
        ];
    }
}
