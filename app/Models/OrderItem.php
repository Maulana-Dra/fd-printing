<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
        'selected_options',
        'design_file_path',
        'design_file_name',
        'design_notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'       => 'decimal:2',
            'subtotal'         => 'decimal:2',
            'quantity'         => 'integer',
            'selected_options' => 'array',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke produk asli. Bisa null jika produk sudah dihapus
     * (data order tetap aman via snapshot product_name & unit_price).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name,
        ]);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Item yang memiliki file desain terupload. */
    public function scopeWithDesignFile(Builder $query): Builder
    {
        return $query->whereNotNull('design_file_path');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->unit_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->subtotal, 0, ',', '.');
    }

    /**
     * URL download file desain customer (signed URL dari R2 private bucket).
     * URL berlaku 60 menit.
     */
    public function getDesignFileUrlAttribute(): ?string
    {
        if (! $this->design_file_path) {
            return null;
        }

        return \Storage::disk('designs')->temporaryUrl(
            path: $this->design_file_path,
            expiration: now()->addMinutes(60),
        );
    }

    /**
     * Opsi yang dipilih diformat menjadi string ringkas.
     * Contoh: "Art Paper 260gsm | A4 | Glossy Lamination"
     */
    public function getSelectedOptionsLabelAttribute(): string
    {
        if (empty($this->selected_options)) {
            return '-';
        }

        return collect($this->selected_options)
            ->pluck('option')
            ->filter()
            ->implode(' | ');
    }
}
