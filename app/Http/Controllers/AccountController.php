<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    // ── GET /akun/pesanan ─────────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = Order::query()
            ->forUser(Auth::id())
            ->with(['items'])
            ->latest();

        if ($statusFilter && $status = OrderStatus::tryFrom($statusFilter)) {
            $query->byStatus($status);
        }

        $orders   = $query->paginate(10)->withQueryString();
        $statuses = OrderStatus::cases();

        return view('account.orders', compact('orders', 'statuses', 'statusFilter'));
    }

    // ── GET /akun/pesanan/{orderNumber} ───────────────────────────────────────

    public function orderDetail(string $orderNumber): View|RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with([
                'items',
                'statusLogs.changer',
                'latestPaymentConfirmation.paymentMethod',
            ])
            ->firstOrFail();

        return view('account.order-detail', compact('order'));
    }
}
