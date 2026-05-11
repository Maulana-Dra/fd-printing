<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    // ── GET /checkout ─────────────────────────────────────────────────────────

    public function index(): View|RedirectResponse
    {
        // Tidak bisa checkout kalau keranjang kosong
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('warning', 'Keranjang Anda kosong. Tambahkan produk terlebih dahulu.');
        }

        $items    = $this->cart->getItems();
        $subtotal = $this->cart->getTotal();
        $user     = Auth::user();

        // Daftar kurir untuk dropdown
        $couriers = [
            'jne'     => 'JNE',
            'jnt'     => 'J&T Express',
            'sicepat' => 'SiCepat',
            'anteraja'=> 'AnterAja',
            'gosend'  => 'GoSend (Gojek)',
            'grabexpress' => 'Grab Express',
            'wahana'  => 'Wahana',
            'tiki'    => 'TIKI',
            'pos'     => 'Pos Indonesia',
            'other'   => 'Lainnya',
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

        $validated    = $request->validated();
        $items        = $this->cart->getItems();
        $subtotal     = $this->cart->getTotal();
        $isDelivery   = $validated['delivery_type'] === DeliveryType::DELIVERY->value;
        $shippingCost = $isDelivery ? (float) ($validated['shipping_cost'] ?? 0) : 0.0;
        $totalAmount  = $subtotal + $shippingCost;

        // Buat order
        $order = Order::create([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => Auth::id(),
            'status'                => OrderStatus::PENDING_PAYMENT,
            'subtotal'              => $subtotal,
            'shipping_cost'         => $shippingCost,
            'total_amount'          => $totalAmount,
            'delivery_type'         => $validated['delivery_type'],
            'recipient_name'        => $isDelivery ? ($validated['recipient_name'] ?? null) : null,
            'recipient_phone'       => $isDelivery ? ($validated['recipient_phone'] ?? null) : null,
            'shipping_address'      => $isDelivery ? ($validated['shipping_address'] ?? null) : null,
            'shipping_city'         => $isDelivery ? ($validated['shipping_city'] ?? null) : null,
            'shipping_province'     => $isDelivery ? ($validated['shipping_province'] ?? null) : null,
            'shipping_postal_code'  => $isDelivery ? ($validated['shipping_postal_code'] ?? null) : null,
            'courier'               => $isDelivery ? ($validated['courier'] ?? null) : null,
            'courier_service'       => $isDelivery ? ($validated['courier_service'] ?? null) : null,
            'notes'                 => $validated['notes'] ?? null,
        ]);

        // Buat order items dari cart
        foreach ($items as $item) {
            OrderItem::create([
                'order_id'         => $order->id,
                'product_id'       => $item['product_id'],
                'product_name'     => $item['product_name'],
                'unit_price'       => $item['unit_price'],
                'quantity'         => $item['quantity'],
                'subtotal'         => $item['subtotal'],
                'selected_options' => $item['selected_options'],
                'design_file_path' => $item['design_file_path'] ?? null,
                'design_notes'     => $item['design_notes'] ?? null,
            ]);
        }

        // Kosongkan keranjang setelah order berhasil dibuat
        $this->cart->clear();

        return redirect()->route('orders.payment', $order)
            ->with('success', "Order #{$order->order_number} berhasil dibuat! Silakan lakukan pembayaran.");
    }
}
