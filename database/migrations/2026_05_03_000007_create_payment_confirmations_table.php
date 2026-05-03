<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_confirmations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                ->constrained('payment_methods')
                ->restrictOnDelete();

            $table->decimal('amount_paid', 12, 2)->comment('Nominal yang diklaim customer sudah dibayar');
            $table->date('transfer_date')->comment('Tanggal transfer sesuai klaim customer');

            /**
             * Path bukti pembayaran (foto/screenshot struk) di storage.
             * Wajib diisi oleh customer saat submit konfirmasi.
             */
            $table->string('proof_image')->comment('Path file di disk "r2"');

            $table->text('notes')->nullable()->comment('Catatan tambahan dari customer');

            /**
             * Status verifikasi (App\Enums\PaymentStatus).
             * Diubah oleh admin setelah mengecek bukti bayar.
             */
            $table->string('status', 30)->default('pending')
                ->comment('pending | approved | rejected');

            /**
             * Admin yang memverifikasi pembayaran ini.
             * Nullable karena saat pertama dibuat belum ada yang verifikasi.
             */
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('confirmed_at')->nullable();
            $table->text('rejection_reason')->nullable()->comment('Alasan penolakan jika status = rejected');

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('transfer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_confirmations');
    }
};
