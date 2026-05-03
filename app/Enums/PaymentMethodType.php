<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case QRIS          = 'qris';
    case BANK_TRANSFER = 'bank_transfer';
    case EWALLET       = 'ewallet';

    /**
     * Label Bahasa Indonesia untuk ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::QRIS          => 'QRIS',
            self::BANK_TRANSFER => 'Transfer Bank',
            self::EWALLET       => 'Dompet Digital (e-Wallet)',
        };
    }

    /**
     * Ikon Heroicon untuk form / badge.
     */
    public function icon(): string
    {
        return match ($this) {
            self::QRIS          => 'heroicon-o-qr-code',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::EWALLET       => 'heroicon-o-device-phone-mobile',
        };
    }

    /**
     * Apakah metode ini membutuhkan upload bukti bayar oleh customer.
     * QRIS dan bank transfer perlu bukti; e-wallet biasanya auto-verifikasi.
     */
    public function requiresProofUpload(): bool
    {
        return match ($this) {
            self::QRIS,
            self::BANK_TRANSFER => true,
            self::EWALLET        => false,
        };
    }
}
