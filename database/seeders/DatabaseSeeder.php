<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeder mengikuti dependency tabel:
     *   Users → PaymentMethods → Categories → Products (butuh category_id)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PaymentMethodSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
