<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Http\Requests\PaymentConfirmationRequest;
use App\Models\Order;
use App\Models\PaymentConfirmation;
use App\Models\PaymentMethod;
use App\Services\FileUploadService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUpload,
        private readonly OrderService      $orderService,
    ) {}

    // ── GET /pesanan/{order}/bayar ─────────────────────────────────────────────

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        // Order harus masih pending payment
        if ($order->status !== OrderStatus::PENDING_PAYMENT) {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Order ini sudah tidak memerlukan konfirmasi pembayaran.');
        }

        $paymentMethods = PaymentMethod::active()->sorted()->get()->groupBy(
            fn ($pm) => $pm->type->value
        );

        return view('checkout.payment', compact('order', 'paymentMethods'));
    }

    // ── POST /pesanan/{order}/bayar/konfirmasi ─────────────────────────────────

    public function confirm(PaymentConfirmationRequest $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->status !== OrderStatus::PENDING_PAYMENT) {
            return back()->with('error', 'Order ini sudah tidak bisa dikonfirmasi pembayarannya.');
        }

        // Upload bukti bayar via FileUploadService
        $proofPath = $this->fileUpload->uploadPaymentProof(
            $request->file('proof_image'),
            $order->id,
        );

        // Simpan konfirmasi pembayaran
        PaymentConfirmation::create([
            'order_id'          => $order->id,
            'payment_method_id' => $request->validated('payment_method_id'),
            'amount_paid'       => $request->validated('amount_paid'),
            'transfer_date'     => $request->validated('transfer_date'),
            'proof_image'       => $proofPath,
            'notes'             => $request->validated('notes'),
            'status'            => PaymentStatus::PENDING,
        ]);

        return redirect()->route('orders.thankyou', $order)
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim! Kami akan segera memverifikasi.');
    }

    // ── GET /pesanan/{order}/terima-kasih ─────────────────────────────────────

    public function thankyou(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        return view('checkout.thankyou', compact('order'));
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }
    }
}
