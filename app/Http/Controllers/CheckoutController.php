<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryType;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService  $cart,
        private readonly OrderService $orderService,
    ) {}

    // ── GET /checkout ─────────────────────────────────────────────────────────

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('warning', 'Keranjang Anda kosong. Tambahkan produk terlebih dahulu.');
        }

        $items    = $this->cart->getItems();
        $subtotal = $this->cart->getTotal();
        $user     = Auth::user();

        $couriers = [
            'jne'         => 'JNE',
            'jnt'         => 'J&T Express',
            'sicepat'     => 'SiCepat',
            'anteraja'    => 'AnterAja',
            'gosend'      => 'GoSend (Gojek)',
            'grabexpress' => 'Grab Express',
            'wahana'      => 'Wahana',
            'tiki'        => 'TIKI',
            'pos'         => 'Pos Indonesia',
            'other'       => 'Lainnya',
        ];

        return view('checkout.index', compact('items', 'subtotal', 'user', 'couriers'));
    }

    // ── POST /checkout ────────────────────────────────────────────────────────

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('warning', 'Keranjang Anda kosong.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $order = $this->orderService->createFromCart(
                checkoutData: $request->validated(),
                user:         $user,
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memproses order. Silakan coba lagi.');
        }

        return redirect()->route('orders.payment', $order)
            ->with('success', "Order #{$order->order_number} berhasil dibuat! Silakan lakukan pembayaran.");
    }
}
