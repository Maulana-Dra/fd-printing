<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ───────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@percetakan.test'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('password'),
                'phone'      => '081200000000',
                'address'    => 'Jl. Percetakan Raya No. 1, Jakarta Pusat',
                'is_admin'   => true,
                'email_verified_at' => now(),
            ],
        );

        // Pastikan admin lama (dari langkah 1) juga punya is_admin = true
        User::where('email', 'admin@fdprinting.id')
            ->update(['is_admin' => true]);

        // ── Dummy Customers ───────────────────────────────────────────────────
        $customers = [
            [
                'name'    => 'Budi Santoso',
                'email'   => 'budi@customer.test',
                'phone'   => '081234567890',
                'address' => 'Jl. Melati No. 12, Bandung, Jawa Barat 40111',
            ],
            [
                'name'    => 'Siti Rahayu',
                'email'   => 'siti@customer.test',
                'phone'   => '085678901234',
                'address' => 'Jl. Mawar Indah Blok C No. 5, Surabaya, Jawa Timur 60111',
            ],
            [
                'name'    => 'Ahmad Fauzi',
                'email'   => 'ahmad@customer.test',
                'phone'   => '087712345678',
                'address' => 'Perum Griya Asri Blok D2 No. 8, Tangerang Selatan, Banten 15411',
            ],
        ];

        foreach ($customers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    ...$customer,
                    'password'          => Hash::make('password'),
                    'is_admin'          => false,
                    'email_verified_at' => now(),
                ],
            );
        }

        $this->command->info('✅ UserSeeder: 1 admin dan ' . count($customers) . ' customer berhasil dibuat.');
        $this->command->line('   Admin login  : admin@percetakan.test / password');
        $this->command->line('   Legacy admin : admin@fdprinting.id / Admin@123456');
        $this->command->line('   Customers    : budi@, siti@, ahmad@customer.test / password');
    }
}
