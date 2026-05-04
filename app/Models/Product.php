<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'unit',
        'min_qty',
        'weight_per_unit',
        'thumbnail',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price'      => 'decimal:2',
            'weight_per_unit' => 'decimal:2',
            'min_qty'         => 'integer',
            'sort_order'      => 'integer',
            'is_active'       => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Harga dasar dalam format Rupiah, contoh: "Rp 15.000".
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->base_price, 0, ',', '.');
    }

    /**
     * URL thumbnail — kembalikan placeholder jika belum ada gambar.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return \Storage::disk('r2')->url($this->thumbnail);
        }

        return 'https://placehold.co/400x400/f97316/ffffff?text=' . urlencode($this->name);
    }

    /**
     * Opsi yang dikelompokkan berdasarkan group_name.
     * Contoh return: ['Bahan' => [ProductOption, ...], 'Ukuran' => [...]]
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, ProductOption>>
     */
    public function getGroupedOptionsAttribute(): \Illuminate\Support\Collection
    {
        return $this->options->groupBy('group_name');
    }
}
