<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan index database untuk query yang sering digunakan.
 *
 * orders:
 *   - (user_id)           → query order by customer
 *   - (status)            → filter by status (admin list, dashboard widget)
 *   - (user_id, status)   → akun customer filter per status
 *   - (created_at)        → sort terbaru, laporan revenue
 *
 * order_items:
 *   - (order_id)          → load items per order (relasi)
 *   - (product_id)        → laporan produk terlaris
 *
 * payment_confirmations:
 *   - (order_id)          → cari konfirmasi per order
 *   - (status)            → filter pending/approved/rejected
 *   - (created_at)        → sort terbaru di admin panel
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── orders ────────────────────────────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            if (! $this->hasIndex('orders', 'orders_user_id_index')) {
                $table->index('user_id');
            }
            if (! $this->hasIndex('orders', 'orders_status_index')) {
                $table->index('status');
            }
            if (! $this->hasIndex('orders', 'orders_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (! $this->hasIndex('orders', 'orders_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ── order_items ───────────────────────────────────────────────────────
        Schema::table('order_items', function (Blueprint $table) {
            if (! $this->hasIndex('order_items', 'order_items_order_id_index')) {
                $table->index('order_id');
            }
            if (! $this->hasIndex('order_items', 'order_items_product_id_index')) {
                $table->index('product_id');
            }
        });

        // ── payment_confirmations ─────────────────────────────────────────────
        Schema::table('payment_confirmations', function (Blueprint $table) {
            if (! $this->hasIndex('payment_confirmations', 'payment_confirmations_order_id_index')) {
                $table->index('order_id');
            }
            if (! $this->hasIndex('payment_confirmations', 'payment_confirmations_status_index')) {
                $table->index('status');
            }
            if (! $this->hasIndex('payment_confirmations', 'payment_confirmations_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    public function down(): void
    {
        $drops = [
            'orders' => [
                'orders_user_id_index',
                'orders_status_index',
                'orders_user_id_status_index',
                'orders_created_at_index',
            ],
            'order_items' => [
                'order_items_order_id_index',
                'order_items_product_id_index',
            ],
            'payment_confirmations' => [
                'payment_confirmations_order_id_index',
                'payment_confirmations_status_index',
                'payment_confirmations_created_at_index',
            ],
        ];

        foreach ($drops as $table => $indexes) {
            foreach ($indexes as $index) {
                try {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
                } catch (\Throwable) {
                    // Index tidak ada atau sudah dihapus — skip
                }
            }
        }
    }

    /**
     * Cek apakah index sudah ada menggunakan SHOW INDEX (MySQL/MariaDB compatible).
     * Menghindari penggunaan Doctrine SchemaManager yang deprecated di Laravel 11.
     */
    private function hasIndex(string $table, string $index): bool
    {
        $results = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$index]
        );
        return count($results) > 0;
    }
};
