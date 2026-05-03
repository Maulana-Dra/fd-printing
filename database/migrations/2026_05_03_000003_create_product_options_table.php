<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            /**
             * Nama grup opsi, contoh: "Bahan", "Ukuran", "Finishing".
             * Digunakan untuk mengelompokkan opsi saat ditampilkan ke customer.
             */
            $table->string('group_name', 100);

            /**
             * Nama opsi spesifik, contoh: "Art Paper 260gsm", "A4", "Glossy Lamination".
             */
            $table->string('option_name', 150);

            $table->unsignedSmallInteger('sort_order')->default(0);

            /**
             * Nilai penyesuaian harga.
             *   modifier_type = 'fixed'      → ditambahkan/dikurangi langsung (Rp)
             *   modifier_type = 'percentage' → dikalikan terhadap base_price (%)
             * Nilai negatif = diskon.
             */
            $table->decimal('price_modifier', 12, 2)->default(0);
            $table->enum('modifier_type', ['fixed', 'percentage'])->default('fixed');

            /**
             * Apakah opsi ini dipilih secara default saat customer membuka halaman produk.
             * Hanya satu opsi per group_name yang boleh is_default = true.
             */
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index(['product_id', 'group_name']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
