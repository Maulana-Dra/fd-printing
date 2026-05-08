<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Collection;

/**
 * CartService — kelola keranjang belanja berbasis Laravel Session.
 *
 * Session key: 'cart'
 * Struktur tiap item: lihat constant ITEM_KEYS di bawah.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    // ── Write Operations ──────────────────────────────────────────────────────

    /**
     * Tambahkan produk ke keranjang.
     * Jika produk + kombinasi opsi yang sama sudah ada, qty akan ditambahkan.
     *
     * @param  int         $productId
     * @param  int         $qty
     * @param  int[]       $selectedOptionIds  Array ID ProductOption yang dipilih
     * @param  string|null $designFilePath     Path file desain yang sudah diupload ke storage
     * @param  string|null $notes              Catatan tambahan dari customer
     */
    public function add(
        int $productId,
        int $qty,
        array $selectedOptionIds = [],
        ?string $designFilePath = null,
        ?string $notes = null,
    ): void {
        $product = Product::active()->findOrFail($productId);

        // Hitung unit price berdasarkan opsi yang dipilih
        [$unitPrice, $resolvedOptions] = $this->resolvePrice($product, $selectedOptionIds);

        // Paksa minimum order quantity
        $qty = max($qty, $product->min_qty);

        // Buat fingerprint untuk deteksi duplikat (product + opsi yang sama)
        $fingerprint = $productId . '_' . implode('_', sort($selectedOptionIds) ?: []);

        $cart = $this->sessionCart();

        // Jika kombinasi produk+opsi sudah ada, tambah qty saja
        foreach ($cart as $id => $item) {
            if ($item['fingerprint'] === $fingerprint) {
                $cart[$id]['quantity'] += $qty;
                $cart[$id]['subtotal']  = round($cart[$id]['unit_price'] * $cart[$id]['quantity'], 2);
                $this->saveCart($cart);
                return;
            }
        }

        // Buat item baru
        $itemId = uniqid('ci_', true);
        $cart[$itemId] = [
            'id'                => $itemId,
            'fingerprint'       => $fingerprint,
            'product_id'        => $product->id,
            'product_name'      => $product->name,
            'product_slug'      => $product->slug,
            'product_thumbnail' => $product->thumbnail_url,
            'unit'              => $product->unit,
            'min_qty'           => $product->min_qty,
            'quantity'          => $qty,
            'unit_price'        => round($unitPrice, 2),
            'subtotal'          => round($unitPrice * $qty, 2),
            'selected_options'  => $resolvedOptions,
            'design_file_path'  => $designFilePath,
            'design_notes'      => $notes,
            'added_at'          => now()->toISOString(),
        ];

        $this->saveCart($cart);
    }

    /**
     * Hapus satu item dari keranjang.
     */
    public function remove(string $cartItemId): void
    {
        $cart = $this->sessionCart();
        unset($cart[$cartItemId]);
        $this->saveCart($cart);
    }

    /**
     * Update jumlah item. Jika qty < min_qty item, gunakan min_qty.
     * Jika qty = 0, hapus item.
     */
    public function update(string $cartItemId, int $qty): void
    {
        $cart = $this->sessionCart();

        if (! isset($cart[$cartItemId])) {
            return;
        }

        if ($qty <= 0) {
            $this->remove($cartItemId);
            return;
        }

        $minQty = $cart[$cartItemId]['min_qty'] ?? 1;
        $qty    = max($qty, $minQty);

        $cart[$cartItemId]['quantity'] = $qty;
        $cart[$cartItemId]['subtotal'] = round($cart[$cartItemId]['unit_price'] * $qty, 2);

        $this->saveCart($cart);
    }

    /**
     * Kosongkan seluruh keranjang.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    // ── Read Operations ───────────────────────────────────────────────────────

    /**
     * Ambil semua item sebagai Collection.
     *
     * @return Collection<string, array>
     */
    public function getItems(): Collection
    {
        return collect($this->sessionCart());
    }

    /**
     * Total harga seluruh item (subtotal).
     */
    public function getTotal(): float
    {
        return round($this->getItems()->sum('subtotal'), 2);
    }

    /**
     * Total item berdasarkan jumlah unit (bukan jumlah baris).
     */
    public function count(): int
    {
        return (int) $this->getItems()->sum('quantity');
    }

    /**
     * Apakah keranjang kosong.
     */
    public function isEmpty(): bool
    {
        return $this->getItems()->isEmpty();
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Hitung unit price dari base_price + modifier opsi terpilih.
     * Return: [float $unitPrice, array $resolvedOptions]
     */
    private function resolvePrice(Product $product, array $selectedOptionIds): array
    {
        $unitPrice       = (float) $product->base_price;
        $resolvedOptions = [];

        if (! empty($selectedOptionIds)) {
            $options = ProductOption::whereIn('id', $selectedOptionIds)
                ->where('product_id', $product->id)
                ->sorted()
                ->get();

            foreach ($options as $opt) {
                $modifier = (float) $opt->price_modifier;

                $unitPrice += match ($opt->modifier_type) {
                    'percentage' => $product->base_price * ($modifier / 100),
                    default      => $modifier,
                };

                $resolvedOptions[] = [
                    'id'             => $opt->id,
                    'group_name'     => $opt->group_name,
                    'option_name'    => $opt->option_name,
                    'price_modifier' => (float) $opt->price_modifier,
                    'modifier_type'  => $opt->modifier_type,
                ];
            }
        }

        return [$unitPrice, $resolvedOptions];
    }

    /** Ambil array cart dari session. */
    private function sessionCart(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /** Simpan array cart ke session. */
    private function saveCart(array $cart): void
    {
        session([self::SESSION_KEY => $cart]);
    }
}
