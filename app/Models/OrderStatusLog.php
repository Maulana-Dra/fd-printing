<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => OrderStatus::class,
            'to_status'   => OrderStatus::class,
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * User yang mengubah status. Null jika diubah oleh sistem/job otomatis.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeToStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('to_status', $status->value);
    }

    public function scopeFromStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('from_status', $status->value);
    }

    /** Log yang dibuat oleh sistem (bukan manusia). */
    public function scopeBySystem(Builder $query): Builder
    {
        return $query->whereNull('changed_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Deskripsi singkat perubahan status, contoh:
     * "pending_payment → paid" atau "Dibuat (paid)"
     */
    public function getChangeDescriptionAttribute(): string
    {
        $to = $this->to_status instanceof OrderStatus
            ? $this->to_status->label()
            : $this->to_status;

        if (! $this->from_status) {
            return "Order dibuat dengan status: {$to}";
        }

        $from = $this->from_status instanceof OrderStatus
            ? $this->from_status->label()
            : $this->from_status;

        return "{$from} → {$to}";
    }

    /**
     * Nama yang melakukan perubahan — "Sistem" jika changed_by null.
     */
    public function getChangerNameAttribute(): string
    {
        return $this->changer?->name ?? 'Sistem';
    }
}
