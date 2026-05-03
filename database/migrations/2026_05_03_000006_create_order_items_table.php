<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            /**
             * SNAPSHOT — nama produk saat order dibuat.
             * Disimpan agar tidak berubah jika admin mengubah nama produk di kemudian hari.
             */
            $table->string('product_name');

            /**
             * SNAPSHOT — harga satuan saat order dibuat (sudah termasuk price_modifier opsi).
             * Tidak boleh mengacu langsung ke products.base_price.
             */
            $table->decimal('unit_price', 12, 2);

            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2)->comment('unit_price × quantity');

            /**
             * Opsi yang dipilih customer, disimpan dalam format JSON.
             * Contoh: [{"group": "Bahan", "option": "Art Paper 260gsm", "modifier": 500}]
             * Snapshot penuh agar tidak bergantung pada tabel product_options.
             */
            $table->json('selected_options')->nullable();

            // ── File desain customer ───────────────────────────────────────
            $table->string('design_file_path')->nullable()->comment('Path file di disk "designs" (R2 private)');
            $table->string('design_file_name')->nullable()->comment('Nama file asli yang diupload customer');
            $table->text('design_notes')->nullable()->comment('Instruksi desain / catatan cetak dari customer');

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
