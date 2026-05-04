<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'delivery_type',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'courier',
        'courier_service',
        'tracking_number',
        'notes',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'        => OrderStatus::class,
            'delivery_type' => DeliveryType::class,
            'subtotal'      => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount'  => 'decimal:2',
        ];
    }

    // ── Order Number Generator ────────────────────────────────────────────────

    /**
     * Menghasilkan nomor order unik dengan format ORD-YYYYMMDD-XXXX.
     * Loop hingga ditemukan nomor yang belum terpakai di database.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentConfirmations(): HasMany
    {
        return $this->hasMany(PaymentConfirmation::class);
    }

    /** Konfirmasi pembayaran yang paling terakhir disubmit. */
    public function latestPaymentConfirmation(): HasOne
    {
        return $this->hasOne(PaymentConfirmation::class)->latestOfMany();
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING_PAYMENT->value);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PAID->value);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PROCESSING->value);
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::READY->value);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::SHIPPED->value);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::DONE->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::CANCELLED->value);
    }

    /** Semua order yang masih aktif (belum selesai / belum dibatalkan). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            OrderStatus::DONE->value,
            OrderStatus::CANCELLED->value,
        ]);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Total jumlah item (satuan/qty) dalam order ini.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Total jumlah baris item (berapa jenis produk) dalam order ini.
     */
    public function getTotalLineItemsAttribute(): int
    {
        return $this->items->count();
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->subtotal, 0, ',', '.');
    }

    public function getFormattedShippingCostAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->shipping_cost, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->total_amount, 0, ',', '.');
    }

    /**
     * Apakah order ini sudah bisa dibatalkan oleh customer.
     * Hanya bisa dibatalkan jika masih di status pending_payment.
     */
    public function getIsCancellableByCustomerAttribute(): bool
    {
        return $this->status === OrderStatus::PENDING_PAYMENT;
    }

    /**
     * Apakah customer perlu upload bukti pembayaran.
     */
    public function getNeedsPaymentConfirmationAttribute(): bool
    {
        return $this->status === OrderStatus::PENDING_PAYMENT;
    }

    /**
     * Label alamat pengiriman lengkap dalam satu string.
     */
    public function getFullShippingAddressAttribute(): ?string
    {
        if ($this->delivery_type === DeliveryType::PICKUP) {
            return null;
        }

        return implode(', ', array_filter([
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_province,
            $this->shipping_postal_code,
        ]));
    }
}
