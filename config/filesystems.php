<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // ── Cloudflare R2 (Production) ─────────────────────────────────────

        /**
         * Disk R2 utama — file publik: thumbnail produk, avatar, QR code.
         * Di local dev: fallback ke disk 'public'.
         * Di production: set FILESYSTEM_DISK=r2 dan isi R2_* credentials.
         */
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_BUCKET'),
            'url' => env('R2_URL'),
            'endpoint' => env('R2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'report' => false,
            'visibility' => 'public',
        ],

        /**
         * Disk khusus file desain customer — PRIVATE, akses via temporary signed URL.
         * Bucket terpisah dari bucket utama agar kebijakan akses bisa berbeda.
         * URL signed berlaku 60 menit (lihat OrderItem::getDesignFileUrlAttribute).
         */
        'designs' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_BUCKET').'-designs',
            'endpoint' => env('R2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'report' => false,
            'visibility' => 'private',
        ],

        // ── Local Disks (Development) ──────────────────────────────────────

        /**
         * Disk lokal untuk file desain customer (development).
         * Di production: diarahkan ke bucket R2 designs yang private.
         * Path: storage/app/private/designs/{order_id}/
         */
        'designs-local' => [
            'driver' => 'local',
            'root' => storage_path('app/private/designs'),
            'serve' => true,
            'visibility' => 'private',
            'throw' => true,
        ],

        /**
         * Disk lokal untuk bukti pembayaran customer.
         * Di production: upload ke disk 'r2' dengan prefix 'payment-proofs/'.
         * Path: storage/app/public/payment-proofs/
         */
        'payment-proofs' => [
            'driver' => 'local',
            'root' => storage_path('app/public/payment-proofs'),
            'url' => env('APP_URL').'/storage/payment-proofs',
            'visibility' => 'public',
            'throw' => true,
        ],

        /**
         * Disk lokal untuk gambar produk / thumbnail.
         * Di production: upload ke disk 'r2' dengan prefix 'products/'.
         * Path: storage/app/public/products/
         */
        'products' => [
            'driver' => 'local',
            'root' => storage_path('app/public/products'),
            'url' => env('APP_URL').'/storage/products',
            'visibility' => 'public',
            'throw' => true,
        ],

        /**
         * Disk lokal untuk gambar QR code metode pembayaran.
         * Di production: upload ke disk 'r2' dengan prefix 'qr-codes/'.
         * Path: storage/app/public/qr-codes/
         */
        'qr-codes' => [
            'driver' => 'local',
            'root' => storage_path('app/public/qr-codes'),
            'url' => env('APP_URL').'/storage/qr-codes',
            'visibility' => 'public',
            'throw' => true,
        ],

        /**
         * Disk lokal untuk avatar / foto profil user.
         * Di production: upload ke disk 'r2' dengan prefix 'avatars/'.
         * Path: storage/app/public/avatars/
         */
        'avatars' => [
            'driver' => 'local',
            'root' => storage_path('app/public/avatars'),
            'url' => env('APP_URL').'/storage/avatars',
            'visibility' => 'public',
            'throw' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
