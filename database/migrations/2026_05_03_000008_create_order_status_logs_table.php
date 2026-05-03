<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            /**
             * Status sebelum perubahan. Nullable karena log pertama
             * saat order dibuat tidak memiliki status sebelumnya.
             */
            $table->string('from_status', 50)->nullable();

            /**
             * Status setelah perubahan (App\Enums\OrderStatus backing value).
             */
            $table->string('to_status', 50);

            /**
             * User yang melakukan perubahan status.
             * Nullable karena bisa saja diubah oleh sistem (job otomatis).
             */
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable()->comment('Catatan internal saat perubahan status');

            $table->timestamps();

            $table->index('order_id');
            $table->index('to_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
