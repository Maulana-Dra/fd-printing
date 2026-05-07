<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Katalog produk — tampil semua atau filter per kategori.
     *
     * Route: GET /k/{slug?}  → products.index
     */
    public function index(Request $request, ?string $slug = null): View
    {
        // Semua kategori aktif untuk sidebar/chip filter
        $categories = Category::active()->sorted()->withCount('activeProducts')->get();

        // Resolve kategori aktif (jika ada slug)
        $category = null;
        if ($slug) {
            $category = Category::active()->where('slug', $slug)->firstOrFail();
        }

        // Base query
        $query = Product::active()->with('category');

        // Filter by category
        if ($category) {
            $query->where('category_id', $category->id);
        }

        // Filter: pencarian teks
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
     *
     * Route: GET /p/{slug}  → products.show
     */
    public function show(string $slug): View
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['category', 'options' => fn ($q) => $q->sorted()])
            ->firstOrFail();

        // Produk serupa dalam kategori yang sama (kecuali produk ini)
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->sorted()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
