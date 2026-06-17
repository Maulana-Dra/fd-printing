<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    // ── GET /cart ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        $items = $this->cart->getItems();
        $total = $this->cart->getTotal();

        return view('cart.index', compact('items', 'total'));
    }

    // ── POST /cart/add ────────────────────────────────────────────────────────

    /**
     * Tambah produk ke keranjang.
     * Mendukung form biasa (redirect) dan AJAX (JSON).
     */
    public function add(CartRequest $request): RedirectResponse|JsonResponse
    {
        // Upload file desain (opsional) ke storage sementara
        $designFilePath = null;
        if ($request->hasFile('design_file')) {
            $designFilePath = $request->file('design_file')
                ->store('designs/temp', 'local');
        }

        $this->cart->add(
            productId:         (int) $request->validated('product_id'),
            qty:               (int) $request->validated('quantity'),
            selectedOptionIds: $request->validated('selected_options', []),
            designFilePath:    $designFilePath,
            notes:             $request->validated('notes'),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
                'count'   => $this->cart->count(),
                'total'   => $this->cart->getTotal(),
                'items'   => $this->cart->getItems()->values(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang! 🎉');
    }

    // ── PATCH /cart/{id} ──────────────────────────────────────────────────────

    /**
     * Update jumlah item.
     * Jika qty ≤ 0, item dihapus otomatis.
     */
    public function update(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $this->cart->update($id, (int) $request->quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Keranjang diperbarui.',
                'count'   => $this->cart->count(),
                'total'   => $this->cart->getTotal(),
                'items'   => $this->cart->getItems()->values(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Keranjang diperbarui.');
    }

    // ── DELETE /cart/{id} ─────────────────────────────────────────────────────

    public function remove(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $this->cart->remove($id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Item dihapus dari keranjang.',
                'count'   => $this->cart->count(),
                'total'   => $this->cart->getTotal(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk dihapus dari keranjang.');
    }

    // ── DELETE /cart ──────────────────────────────────────────────────────────

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Keranjang dikosongkan.']);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }
}
