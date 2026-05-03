<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            /**
             * Tipe metode pembayaran (App\Enums\PaymentMethodType).
             * Disimpan sebagai string sesuai backing value enum.
             */
            $table->string('type', 50)->comment('qris | bank_transfer | ewallet');

            $table->string('name');
            $table->string('account_number', 50)->nullable()->comment('Nomor rekening / nomor VA / nomor e-wallet');
            $table->string('account_name', 100)->nullable()->comment('Nama pemilik rekening');
            $table->string('bank_name', 100)->nullable()->comment('Nama bank, contoh: BCA, BNI, Mandiri');
            $table->string('qr_image')->nullable()->comment('Path gambar QRIS di storage');
            $table->text('description')->nullable()->comment('Petunjuk pembayaran tambahan untuk customer');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
