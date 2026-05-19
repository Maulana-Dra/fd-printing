<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CatalogCacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * ProductObserver — dua tanggung jawab:
 *   1. Invalidate catalog cache setiap kali produk berubah.
 *   2. Compress thumbnail setelah produk disimpan (server-side, max 800px, quality 80).
 */
class ProductObserver
{
    public function __construct(
        private readonly CatalogCacheService $cache,
    ) {}

    public function created(Product $product): void
    {
        $this->compressThumbnail($product);
        $this->cache->flushProducts($product->category_id);
    }

    public function updated(Product $product): void
    {
        // Jika thumbnail berubah, compress yang baru
        if ($product->wasChanged('thumbnail')) {
            $this->compressThumbnail($product);
        }

        // Jika kategori berubah, invalidate cache kategori lama juga
        if ($product->wasChanged('category_id')) {
            $oldCategoryId = $product->getOriginal('category_id');
            $this->cache->flushProducts((int) $oldCategoryId);
        }

        $this->cache->flushProducts($product->category_id);
    }

    public function deleted(Product $product): void
    {
        $this->cache->flushProducts($product->category_id);
    }

    public function restored(Product $product): void
    {
        $this->cache->flushProducts($product->category_id);
    }

    public function forceDeleted(Product $product): void
    {
        $this->cache->flushProducts($product->category_id);
    }

    // ── Private: Thumbnail Compression ───────────────────────────────────────

    /**
     * Compress thumbnail produk setelah disimpan ke disk.
     *
     * - Baca dari disk 'products'
     * - Resize ke max 800×800 (pertahankan rasio aspek)
     * - Encode ke JPEG quality 80
     * - Tulis balik ke path yang sama
     */
    private function compressThumbnail(Product $product): void
    {
        $path = $product->thumbnail;

        if (! $path) {
            return;
        }

        $disk = 'products';

        try {
            if (! Storage::disk($disk)->exists($path)) {
                return;
            }

            $content = Storage::disk($disk)->get($path);
            $image   = Image::read($content);

            // Skip jika gambar sudah kecil (< 800px lebar dan tinggi)
            if ($image->width() <= 800 && $image->height() <= 800) {
                return;
            }

            // Scale down, pertahankan rasio aspek
            $image->scaleDown(width: 800, height: 800);

            // Encode ke JPEG quality 80
            $compressed = (string) $image->toJpeg(quality: 80);

            // Ganti file lama dengan yang sudah dikompresi
            Storage::disk($disk)->put($path, $compressed);

            Log::info('Product thumbnail compressed', [
                'product_id' => $product->id,
                'path'       => $path,
                'new_width'  => $image->width(),
                'new_height' => $image->height(),
            ]);

        } catch (\Throwable $e) {
            // Jangan sampai gagal compress membuat proses save produk error
            Log::warning('Failed to compress product thumbnail', [
                'product_id' => $product->id,
                'path'       => $path,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
