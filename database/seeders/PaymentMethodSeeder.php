<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'type'           => PaymentMethodType::QRIS,
                'name'           => 'QRIS Perusahaan',
                'account_number' => null,
                'account_name'   => 'CV Percetakan Maju',
                'bank_name'      => null,
                'qr_image'       => null, // Di-upload manual via admin panel
                'description'    => 'Scan QR Code di bawah menggunakan aplikasi dompet digital apapun (GoPay, OVO, Dana, ShopeePay, dll). Pastikan nominal sesuai dengan total tagihan.',
                'is_active'      => true,
                'sort_order'     => 1,
            ],
            [
                'type'           => PaymentMethodType::BANK_TRANSFER,
                'name'           => 'Transfer Bank BCA',
                'account_number' => '1234567890',
                'account_name'   => 'CV Percetakan Maju',
                'bank_name'      => 'BCA',
                'qr_image'       => null,
                'description'    => 'Transfer ke rekening BCA di atas, kemudian upload bukti transfer. Pembayaran akan diverifikasi dalam 1x24 jam hari kerja.',
                'is_active'      => true,
                'sort_order'     => 2,
            ],
            [
                'type'           => PaymentMethodType::BANK_TRANSFER,
                'name'           => 'Transfer Bank Mandiri',
                'account_number' => '0987654321',
                'account_name'   => 'CV Percetakan Maju',
                'bank_name'      => 'Mandiri',
                'qr_image'       => null,
                'description'    => 'Transfer ke rekening Mandiri di atas, kemudian upload bukti transfer. Pembayaran akan diverifikasi dalam 1x24 jam hari kerja.',
                'is_active'      => true,
                'sort_order'     => 3,
            ],
            [
                'type'           => PaymentMethodType::EWALLET,
                'name'           => 'GoPay',
                'account_number' => '081234567890',
                'account_name'   => 'Budi Santoso',
                'bank_name'      => null,
                'qr_image'       => null,
                'description'    => 'Transfer GoPay ke nomor di atas, kemudian upload screenshot konfirmasi pembayaran.',
                'is_active'      => true,
                'sort_order'     => 4,
            ],
            [
                'type'           => PaymentMethodType::EWALLET,
                'name'           => 'OVO',
                'account_number' => '081234567890',
                'account_name'   => 'Budi Santoso',
                'bank_name'      => null,
                'qr_image'       => null,
                'description'    => 'Transfer OVO ke nomor di atas, kemudian upload screenshot konfirmasi pembayaran.',
                'is_active'      => true,
                'sort_order'     => 5,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                $method,
            );
        }

        $this->command->info('✅ PaymentMethodSeeder: ' . count($methods) . ' metode pembayaran berhasil dibuat.');
    }
}
