<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    protected $fillable = [
        'product_id',
        'group_name',
        'option_name',
        'sort_order',
        'price_modifier',
        'modifier_type',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'sort_order'     => 'integer',
            'is_default'     => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSorted(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('option_name');
    }

    /** Hanya opsi yang menjadi pilihan default. */
    public function scopeDefaults(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /** Filter opsi berdasarkan nama grup. */
    public function scopeForGroup(Builder $query, string $groupName): Builder
    {
        return $query->where('group_name', $groupName);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Harga modifier dalam format Rupiah atau persentase.
     * Contoh: "+Rp 2.000" / "-Rp 500" / "+10%" / "-5%"
     */
    public function getFormattedPriceModifierAttribute(): string
    {
        $value = (float) $this->price_modifier;
        $sign  = $value >= 0 ? '+' : '';

        if ($this->modifier_type === 'percentage') {
            return $sign . number_format($value, 1, ',', '.') . '%';
        }

        return $sign . 'Rp ' . number_format(abs($value), 0, ',', '.');
    }

    /**
     * Hitung harga final opsi ini berdasarkan harga dasar produk.
     */
    public function calculateFinalPrice(float $basePrice): float
    {
        return match ($this->modifier_type) {
            'percentage' => $basePrice + ($basePrice * ((float) $this->price_modifier / 100)),
            default      => $basePrice + (float) $this->price_modifier,
        };
    }
}
