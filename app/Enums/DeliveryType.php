<?php

namespace App\Enums;

enum DeliveryType: string
{
    case PICKUP   = 'pickup';
    case DELIVERY = 'delivery';

    /**
     * Label Bahasa Indonesia untuk ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::PICKUP   => 'Ambil di Tempat',
            self::DELIVERY => 'Dikirim (Pengiriman)',
        };
    }

    /**
     * Ikon Heroicon untuk form / badge.
     */
    public function icon(): string
    {
        return match ($this) {
            self::PICKUP   => 'heroicon-o-home',
            self::DELIVERY => 'heroicon-o-truck',
        };
    }

    /**
     * Apakah tipe pengiriman ini memerlukan input alamat tujuan oleh customer.
     */
    public function requiresAddress(): bool
    {
        return match ($this) {
            self::PICKUP   => false,
            self::DELIVERY => true,
        };
    }
}
