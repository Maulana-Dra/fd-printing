<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fonnte WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Fonnte API sebagai layanan pengiriman
    | notifikasi WhatsApp otomatis ke pelanggan dan admin.
    |
    | Dokumentasi: https://fonnte.com/api
    |
    */

    'token' => env('FONNTE_TOKEN', ''),

    'sender' => env('FONNTE_SENDER', ''),

    'base_url' => 'https://api.fonnte.com',

    /**
     * Delay antar pengiriman pesan (dalam detik).
     * Digunakan untuk menghindari rate-limit Fonnte.
     */
    'delay' => env('FONNTE_DELAY', 1),

    /**
     * Mode countdown — jika true, pesan dikirim setelah countdown habis.
     * Gunakan false untuk pengiriman instan.
     */
    'schedule' => false,

];
