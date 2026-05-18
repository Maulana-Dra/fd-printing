<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception khusus untuk error yang berhubungan dengan operasi Order.
 *
 * Hierarki:
 *   OrderException                → base, error umum order
 *   ├── OrderStatusException      → transisi status tidak valid
 *   ├── OrderNotFoundException    → order tidak ditemukan
 *   └── OrderAuthorizationException → akses order tidak diizinkan
 *
 * Semua subclass ini boleh di-catch secara spesifik di controller
 * untuk memberikan respons yang lebih tepat ke user.
 */
class OrderException extends RuntimeException
{
    /**
     * Context tambahan untuk logging (order_id, user_id, dll.)
     */
    protected array $context = [];

    public function __construct(string $message = '', array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Factory — buat exception dengan context order.
     */
    public static function forOrder(string $message, int $orderId, array $extra = []): static
    {
        return new static($message, array_merge(['order_id' => $orderId], $extra));
    }
}

// ── Subclasses ────────────────────────────────────────────────────────────────

/**
 * Transisi status order yang tidak valid.
 * Misalnya: mencoba move dari CANCELLED ke PROCESSING.
 */
class OrderStatusException extends OrderException
{
    public static function invalidTransition(string $from, string $to, int $orderId): static
    {
        return new static(
            "Transisi status dari '{$from}' ke '{$to}' tidak diizinkan.",
            ['order_id' => $orderId, 'from_status' => $from, 'to_status' => $to],
        );
    }
}

/**
 * Order tidak ditemukan (sudah dihapus atau ID salah).
 */
class OrderNotFoundException extends OrderException
{
    public static function withNumber(string $orderNumber): static
    {
        return new static(
            "Order dengan nomor '{$orderNumber}' tidak ditemukan.",
            ['order_number' => $orderNumber],
        );
    }
}

/**
 * Customer mencoba mengakses order milik orang lain.
 */
class OrderAuthorizationException extends OrderException
{
    public static function accessDenied(int $orderId, int $userId): static
    {
        return new static(
            'Anda tidak memiliki akses ke order ini.',
            ['order_id' => $orderId, 'user_id' => $userId],
        );
    }
}
