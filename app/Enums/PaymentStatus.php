<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Label Bahasa Indonesia untuk ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Menunggu Verifikasi',
            self::APPROVED => 'Pembayaran Dikonfirmasi',
            self::REJECTED => 'Pembayaran Ditolak',
        };
    }

    /**
     * Warna badge untuk Filament admin panel.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    /**
     * Ikon Heroicon untuk badge / timeline.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING  => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }
}
