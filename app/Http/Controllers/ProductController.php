<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CatalogCacheService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly CatalogCacheService $catalog,
    ) {}

    /**
     * Katalog produk — tampil semua atau filter per kategori.
     * Route: GET /k/{slug?}  → products.index
     */
    public function index(Request $request, ?string $slug = null): View
    {
        // Semua kategori aktif — dari cache (60 menit), dengan withCount('activeProducts')
        $categories = $this->catalog->allActiveCategories();

        // Resolve kategori aktif dari cache (60 menit)
        $category = null;
        if ($slug) {
            $category = $this->catalog->categoryBySlug($slug);
            if (! $category) {
                abort(404);
            }
        }

        // Base query dengan eager load 'category' (cegah N+1 di table)
        $query = $this->catalog->productsQuery($category?->id);

        // Filter: pencarian teks (tidak di-cache)
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter: urutkan
        match ($request->input('sort', 'default')) {
            'price_asc'  => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'newest'     => $query->latest(),
            default      => $query->sorted(),
        };

        $products = $query->paginate(
            config('printing.pagination.products_per_page', 12)
        )->withQueryString();

        return view('products.index', compact('categories', 'category', 'products'));
    }

    /**
     * Detail produk — konfigurasi spesifikasi, upload desain, tambah ke keranjang.
     * Route: GET /p/{slug}  → products.show
     */
    public function show(string $slug): View
    {
        // Eager load 'category' + 'options' — tidak ada N+1
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['category', 'options' => fn ($q) => $q->sorted()])
            ->firstOrFail();

        // Produk serupa (eager load tidak diperlukan, hanya list sederhana)
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->sorted()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
