<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * CatalogCacheService — pemusatan logika cache untuk katalog produk.
 *
 * Cache keys:
 *   catalog:categories           → semua kategori aktif (60 menit)
 *   catalog:category:{id}        → 1 kategori by ID (60 menit)
 *   catalog:products:{catId}     → produk per kategori (30 menit)
 *   catalog:products:all         → semua produk aktif (30 menit)
 *   catalog:home_categories      → data home page (kategori + 6 produk) (60 menit)
 *
 * Invalidasi:
 *   - flush() → hapus semua cache katalog (dipanggil dari Observer)
 *   - flushCategory() → hapus cache 1 kategori + produk terkait
 *   - flushProducts() → hapus semua cache produk
 */
class CatalogCacheService
{
    private const TTL_CATEGORIES = 60;  // menit
    private const TTL_PRODUCTS   = 30;  // menit
    private const TAG             = 'catalog';

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Semua kategori aktif, sorted, dengan jumlah produk aktif.
     * Digunakan di sidebar katalog dan filter.
     */
    public function allActiveCategories(): Collection
    {
        return Cache::remember(
            'catalog:categories',
            now()->addMinutes(self::TTL_CATEGORIES),
            fn () => Category::active()
                ->sorted()
                ->withCount('activeProducts')
                ->get()
        );
    }

    /**
     * Data home page: kategori aktif + relasi produk aktif (6 per kategori).
     * Digunakan di HomeController::index().
     */
    public function homeCategories(): Collection
    {
        return Cache::remember(
            'catalog:home_categories',
            now()->addMinutes(self::TTL_CATEGORIES),
            fn () => Category::active()
                ->sorted()
                ->with([
                    'products' => fn ($q) => $q->active()->sorted(),
                ])
                ->get()
        );
    }

    /**
     * Produk aktif dengan kategori & opsi, filter by category_id (opsional).
     * Hanya di-cache jika tidak ada query tambahan (pencarian/sort tidak di-cache).
     *
     * @param  int|null $categoryId  null = semua
     * @return \Illuminate\Database\Eloquent\Builder  — gunakan ini untuk paginate()
     */
    public function productsQuery(?int $categoryId = null): \Illuminate\Database\Eloquent\Builder
    {
        // Query-nya tidak di-cache karena paginate + sort dinamis.
        // Cache hanya untuk data statis seperti daftar kategori.
        return Product::active()
            ->with(['category'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId));
    }

    /**
     * Single kategori by slug — cache 60 menit.
     */
    public function categoryBySlug(string $slug): ?Category
    {
        return Cache::remember(
            "catalog:category:slug:{$slug}",
            now()->addMinutes(self::TTL_CATEGORIES),
            fn () => Category::active()->where('slug', $slug)->first()
        );
    }

    // ── Invalidation ─────────────────────────────────────────────────────────

    /**
     * Hapus semua cache katalog (kategori + produk + home).
     * Dipanggil dari Observer saat ada perubahan produk atau kategori.
     */
    public function flush(): void
    {
        $keys = [
            'catalog:categories',
            'catalog:home_categories',
            'catalog:products:all',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Hapus cache per-kategori (slug-based)
        // Karena tidak tahu slug mana yang berubah, hapus semua dengan pattern
        // Laravel tidak support wildcard delete di semua driver,
        // jadi kita gunakan cache tags jika driver mendukung, atau flush specific keys.
        $this->flushCategorySlugCaches();
    }

    /**
     * Hapus cache satu produk spesifik berdasarkan category_id.
     */
    public function flushCategory(int $categoryId): void
    {
        Cache::forget("catalog:category:{$categoryId}");
        Cache::forget("catalog:products:{$categoryId}");
        Cache::forget('catalog:categories');
        Cache::forget('catalog:home_categories');
        $this->flushCategorySlugCaches();
    }

    /**
     * Hapus cache produk (tanpa menyentuh cache kategori).
     * Dipanggil saat hanya produk yang berubah.
     */
    public function flushProducts(int $categoryId): void
    {
        Cache::forget('catalog:home_categories');
        Cache::forget("catalog:products:{$categoryId}");
        Cache::forget('catalog:products:all');
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Hapus semua cache slug kategori yang mungkin ada.
     * Ambil semua slug kategori dari DB (tanpa cache, karena ini proses invalidasi).
     */
    private function flushCategorySlugCaches(): void
    {
        try {
            $slugs = Category::pluck('slug');
            foreach ($slugs as $slug) {
                Cache::forget("catalog:category:slug:{$slug}");
            }
        } catch (\Throwable) {
            // Jika DB tidak bisa diakses, skip saja
        }
    }
}
