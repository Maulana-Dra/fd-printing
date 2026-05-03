<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            /**
             * Nomor order yang ditampilkan ke customer, format: ORD-YYYYMMDD-XXXX.
             * Di-generate oleh OrderService saat order dibuat.
             */
            $table->string('order_number', 30)->unique();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            /**
             * Status order (App\Enums\OrderStatus).
             * Transisi status divalidasi oleh OrderService::updateStatus().
             */
            $table->string('status', 50)->default('pending_payment')
                ->comment('pending_payment | paid | processing | ready | shipped | done | cancelled');

            // ── Ringkasan harga ────────────────────────────────────────────
            $table->decimal('subtotal', 12, 2)->comment('Total harga item sebelum ongkir');
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->comment('subtotal + shipping_cost');

            // ── Pengiriman ─────────────────────────────────────────────────
            $table->string('delivery_type', 20)->comment('pickup | delivery');
            $table->string('recipient_name', 150)->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_province', 100)->nullable();
            $table->string('shipping_postal_code', 10)->nullable();
            $table->string('courier', 50)->nullable()->comment('Nama jasa kirim, contoh: JNE, Sicepat');
            $table->string('courier_service', 50)->nullable()->comment('Layanan jasa kirim, contoh: REG, YES');
            $table->string('tracking_number', 100)->nullable();

            // ── Catatan & pembatalan ───────────────────────────────────────
            $table->text('notes')->nullable()->comment('Catatan tambahan dari customer');
            $table->text('cancelled_reason')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('delivery_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
