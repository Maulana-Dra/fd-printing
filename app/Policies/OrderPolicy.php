<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Customer hanya boleh melihat / berinteraksi dengan order milik sendiri.
     * Admin (is_admin = true) bisa mengakses semua order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->is_admin || $order->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // Semua auth user boleh membuat order
    }

    public function update(User $user, Order $order): bool
    {
        // Customer hanya bisa update order milik sendiri yang masih pending
        return $user->is_admin
            || ($order->user_id === $user->id && $order->status->value === 'pending_payment');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->is_admin;
    }

    /**
     * Akses ke halaman payment confirmation (POST bukti bayar).
     * Customer hanya boleh confirm order milik sendiri yang pending payment.
     */
    public function confirmPayment(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            && $order->status->value === 'pending_payment';
    }

    /**
     * Download file desain dari order.
     * Hanya admin yang boleh download file desain.
     */
    public function downloadDesign(User $user, Order $order): bool
    {
        return $user->is_admin;
    }
}
