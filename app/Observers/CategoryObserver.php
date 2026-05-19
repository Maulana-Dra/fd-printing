<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CatalogCacheService;
use Illuminate\Support\Facades\Cache;

/**
 * CategoryObserver — invalidate catalog cache setiap kali kategori berubah.
 */
class CategoryObserver
{
    public function __construct(
        private readonly CatalogCacheService $cache,
    ) {}

    public function created(Category $category): void
    {
        $this->cache->flush();
    }

    public function updated(Category $category): void
    {
        $this->cache->flushCategory($category->id);

        // Jika slug berubah, hapus cache slug lama juga
        if ($category->wasChanged('slug') && $category->getOriginal('slug')) {
            Cache::forget('catalog:category:slug:' . $category->getOriginal('slug'));
        }
    }

    public function deleted(Category $category): void
    {
        $this->cache->flush();
    }

    public function restored(Category $category): void
    {
        $this->cache->flush();
    }

    public function forceDeleted(Category $category): void
    {
        $this->cache->flush();
    }
}
