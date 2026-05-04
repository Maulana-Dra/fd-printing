<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Cetak Sticker',
                'description' => 'Berbagai jenis cetak sticker berkualitas tinggi: vinyl, transparan, hologram, dan label produk.',
                'icon'        => 'heroicon-o-star',
                'sort_order'  => 1,
                'is_active'   => true,
            ],
            [
                'name'        => 'Digital Print',
                'description' => 'Layanan cetak digital format besar untuk banner, spanduk, backwall, dan poster indoor/outdoor.',
                'icon'        => 'heroicon-o-printer',
                'sort_order'  => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'Media Promosi',
                'description' => 'Cetak brosur, flyer, kartu nama, kalender, dan berbagai materi promosi bisnis Anda.',
                'icon'        => 'heroicon-o-megaphone',
                'sort_order'  => 3,
                'is_active'   => true,
            ],
            [
                'name'        => 'Kaos & Garment',
                'description' => 'Sablon kaos, polo shirt, hoodie, dan jaket untuk seragam perusahaan, komunitas, atau event.',
                'icon'        => 'heroicon-o-shopping-bag',
                'sort_order'  => 4,
                'is_active'   => true,
            ],
            [
                'name'        => 'Merchandise',
                'description' => 'Produk merchandise souvenir: mug, tote bag, payung, tumbler, pin, dan masih banyak lagi.',
                'icon'        => 'heroicon-o-gift',
                'sort_order'  => 5,
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [...$data, 'slug' => Str::slug($data['name'])],
            );
        }

        $this->command->info('✅ CategorySeeder: ' . count($categories) . ' kategori berhasil dibuat.');
    }
}
