<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'type',
        'name',
        'account_number',
        'account_name',
        'bank_name',
        'qr_image',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type'       => PaymentMethodType::class,
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function paymentConfirmations(): HasMany
    {
        return $this->hasMany(PaymentConfirmation::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeOfType(Builder $query, PaymentMethodType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * URL gambar QR code dari storage R2.
     */
    public function getQrImageUrlAttribute(): ?string
    {
        if ($this->qr_image) {
            return \Storage::disk('r2')->url($this->qr_image);
        }

        return null;
    }

    /**
     * Label tampilan lengkap, contoh: "BCA - 1234567890 (a.n. John Doe)".
     */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->type === PaymentMethodType::BANK_TRANSFER && $this->account_number) {
            return implode(' - ', array_filter([
                $this->bank_name,
                $this->account_number,
                $this->account_name ? "(a.n. {$this->account_name})" : null,
            ]));
        }

        return $this->name;
    }
}
