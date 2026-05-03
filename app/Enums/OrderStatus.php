<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID            = 'paid';
    case PROCESSING      = 'processing';
    case READY           = 'ready';
    case SHIPPED         = 'shipped';
    case DONE            = 'done';
    case CANCELLED       = 'cancelled';

    /**
     * Label Bahasa Indonesia untuk ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::PAID            => 'Sudah Dibayar',
            self::PROCESSING      => 'Sedang Diproses',
            self::READY           => 'Siap Diambil / Dikirim',
            self::SHIPPED         => 'Dalam Pengiriman',
            self::DONE            => 'Selesai',
            self::CANCELLED       => 'Dibatalkan',
        };
    }

    /**
     * Warna badge untuk Filament admin panel.
     * Nilai yang valid: 'gray' | 'info' | 'warning' | 'success' | 'danger' | 'primary'
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'warning',
            self::PAID            => 'info',
            self::PROCESSING      => 'primary',
            self::READY           => 'success',
            self::SHIPPED         => 'info',
            self::DONE            => 'success',
            self::CANCELLED       => 'danger',
        };
    }

    /**
     * Ikon Heroicon untuk ditampilkan di timeline / badge Filament.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'heroicon-o-clock',
            self::PAID            => 'heroicon-o-credit-card',
            self::PROCESSING      => 'heroicon-o-cog-6-tooth',
            self::READY           => 'heroicon-o-archive-box-arrow-down',
            self::SHIPPED         => 'heroicon-o-truck',
            self::DONE            => 'heroicon-o-check-badge',
            self::CANCELLED       => 'heroicon-o-x-circle',
        };
    }

    /**
     * Validasi apakah transisi status order diizinkan.
     *
     * Aturan alur:
     *   PENDING_PAYMENT → PAID | CANCELLED
     *   PAID            → PROCESSING | CANCELLED
     *   PROCESSING      → READY | CANCELLED
     *   READY           → SHIPPED | DONE | CANCELLED
     *   SHIPPED         → DONE
     *   DONE            → (terminal — tidak bisa berubah)
     *   CANCELLED       → (terminal — tidak bisa berubah)
     */
    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::PENDING_PAYMENT => in_array($new, [self::PAID, self::CANCELLED], strict: true),
            self::PAID            => in_array($new, [self::PROCESSING, self::CANCELLED], strict: true),
            self::PROCESSING      => in_array($new, [self::READY, self::CANCELLED], strict: true),
            self::READY           => in_array($new, [self::SHIPPED, self::DONE, self::CANCELLED], strict: true),
            self::SHIPPED         => in_array($new, [self::DONE], strict: true),
            self::DONE,
            self::CANCELLED       => false,
        };
    }

    /**
     * Daftar status yang bisa dijadikan tujuan transisi dari status ini.
     * Berguna untuk mengisi opsi select di form admin.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return array_filter(
            self::cases(),
            fn (self $status) => $this->canTransitionTo($status),
        );
    }

    /**
     * Apakah status ini bersifat terminal (tidak bisa diubah lagi).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::DONE, self::CANCELLED], strict: true);
    }
}
