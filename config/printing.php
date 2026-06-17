<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informasi Perusahaan
    |--------------------------------------------------------------------------
    |
    | Digunakan di seluruh aplikasi: email, invoice, footer, notifikasi WA.
    |
    */

    'company' => [
        'name'    => env('COMPANY_NAME', 'FD Printing'),
        'tagline' => env('COMPANY_TAGLINE', 'Solusi Cetak Berkualitas untuk Bisnis Anda'),
        'phone'   => env('COMPANY_PHONE', '628113298877'),
        'email'   => env('COMPANY_EMAIL', 'info@fdprinting.id'),
        'address' => env('COMPANY_ADDRESS', 'Jl. Raya Wadung Asri No.42, Wadungasri, Kec. Waru, Kabupaten Sidoarjo, Jawa Timur 61256'),
        'website' => env('APP_URL', 'http://localhost:8000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Upload File Desain
    |--------------------------------------------------------------------------
    |
    | Aturan upload file desain oleh customer pada saat order.
    |
    */

    'upload' => [
        /**
         * Ukuran maksimum file desain per upload, dalam Megabyte.
         * Pastikan nilai ini sinkron dengan php.ini: upload_max_filesize & post_max_size.
         */
        'max_upload_size_mb' => (int) env('MAX_UPLOAD_SIZE_MB', 50),

        /**
         * MIME type yang diizinkan untuk file desain customer.
         * Digunakan pada FileUploadService::validate() dan FormRequest validation.
         */
        'allowed_mime_types' => [
            'application/pdf',                                         // PDF
            'image/png',                                               // PNG
            'image/jpeg',                                              // JPEG
            'image/webp',                                              // WebP
            'application/postscript',                                  // AI / EPS
            'application/illustrator',                                 // Adobe Illustrator
            'image/vnd.adobe.photoshop',                               // PSD
            'application/x-coreldraw',                                 // CDR (CorelDRAW)
            'application/octet-stream',                                // CDR / AI (fallback MIME)
        ],

        /**
         * Ekstensi file yang diizinkan (lowercase).
         * Divalidasi bersama MIME type untuk keamanan ganda.
         */
        'allowed_extensions' => [
            'pdf',
            'png',
            'jpg',
            'jpeg',
            'webp',
            'ai',
            'eps',
            'cdr',
            'psd',
        ],

        /**
         * Ukuran maksimum bukti pembayaran (foto/screenshot), dalam Megabyte.
         */
        'max_proof_size_mb' => (int) env('MAX_PROOF_SIZE_MB', 5),

        /**
         * MIME type yang diizinkan untuk bukti pembayaran.
         */
        'allowed_proof_mime_types' => [
            'image/png',
            'image/jpeg',
            'image/webp',
            'application/pdf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Order
    |--------------------------------------------------------------------------
    */

    'order' => [
        /**
         * Prefix nomor order. Format: {PREFIX}-{YYYYMMDD}-{XXXX}
         * Contoh: ORD-20260503-4521
         */
        'number_prefix' => env('ORDER_NUMBER_PREFIX', 'ORD'),

        /**
         * Berapa jam batas waktu pembayaran setelah order dibuat.
         * Setelah melewati waktu ini, order otomatis di-cancel (via scheduled job).
         */
        'payment_deadline_hours' => (int) env('ORDER_PAYMENT_DEADLINE_HOURS', 24),

        /**
         * Berapa hari estimasi produksi default (jika tidak diisi admin).
         */
        'default_production_days' => (int) env('ORDER_DEFAULT_PRODUCTION_DAYS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'products_per_page' => 12,
        'orders_per_page'   => 15,
        'admin_per_page'    => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Gambar & Thumbnail
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh FileUploadService saat resize thumbnail produk.
    |
    */

    'image' => [
        /**
         * Dimensi thumbnail produk (dalam pixel). Rasio aspek dipertahankan.
         */
        'thumbnail_width'  => 800,
        'thumbnail_height' => 800,

        /**
         * Kualitas kompresi JPEG/WebP untuk thumbnail (0–100).
         */
        'thumbnail_quality' => 85,

        /**
         * Dimensi avatar user (dalam pixel, square).
         */
        'avatar_size' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi WhatsApp (Fonnte)
    |--------------------------------------------------------------------------
    |
    | Pengaturan tambahan notifikasi WA di luar fonnte.php.
    |
    */

    'whatsapp' => [
        /**
         * Apakah notifikasi WA aktif. Set false di local dev.
         */
        'enabled' => (bool) env('WA_NOTIFICATIONS_ENABLED', false),

        /**
         * Prefix pesan WA untuk identifikasi brand.
         */
        'message_prefix' => env('WA_MESSAGE_PREFIX', '🖨️ *FD Printing*'),

        /**
         * Fonnte API endpoint.
         */
        'fonnte_url' => 'https://api.fonnte.com/send',

        /**
         * Token API Fonnte (dari dashboard fonnte.com).
         */
        'fonnte_token' => env('FONNTE_TOKEN', ''),

        /**
         * Nomor WA admin untuk menerima notifikasi order baru.
         * Format: 628xxx (tanpa + atau 0 di depan).
         */
        'admin_number' => env('ADMIN_WA_NUMBER', ''),
    ],

];
