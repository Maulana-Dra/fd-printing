<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method_id',
        'amount_paid',
        'transfer_date',
        'proof_image',
        'notes',
        'status',
        'confirmed_by',
        'confirmed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid'   => 'decimal:2',
            'transfer_date' => 'date',
            'status'        => PaymentStatus::class,
            'confirmed_at'  => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Admin yang melakukan verifikasi/penolakan konfirmasi pembayaran ini.
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PENDING->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::APPROVED->value);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::REJECTED->value);
    }

    public function scopeByStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /** Konfirmasi yang belum diproses hari ini. */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFormattedAmountPaidAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->amount_paid, 0, ',', '.');
    }

    public function getFormattedTransferDateAttribute(): string
    {
        return $this->transfer_date->translatedFormat('d F Y');
    }

    /**
     * URL bukti pembayaran dari storage R2 (public).
     */
    public function getProofImageUrlAttribute(): string
    {
        return \Storage::disk('r2')->url($this->proof_image);
    }

    /**
     * Apakah konfirmasi ini masih bisa diproses (di-approve/reject) oleh admin.
     */
    public function getIsProcessableAttribute(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }
}
