<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * FileUploadService — layanan upload file terpusat.
 *
 * Strategi disk:
 *   - Local dev  → 'designs-local' (private) / 'payment-proofs' (public)
 *   - Production → 'designs' (R2 private, signed URL) / 'r2' (R2 public)
 *
 * Keamanan:
 *   - Validasi MIME via finfo (konten aktual, bukan hanya ekstensi)
 *   - Nama file diacak (uuid + timestamp), tidak pernah memakai nama asli
 *   - File desain disimpan di disk private, akses hanya via signed URL
 */
class FileUploadService
{
    // ── Disk constants ────────────────────────────────────────────────────────

    /** Disk untuk file desain (private). */
    private function designDisk(): string
    {
        return app()->isLocal() ? 'designs-local' : 'designs';
    }

    /** Disk untuk bukti pembayaran (public). */
    private function proofDisk(): string
    {
        return app()->isLocal() ? 'payment-proofs' : 'r2';
    }

    /** Prefix path bukti pembayaran di disk r2 (production). */
    private const PROOF_PREFIX = 'payment-proofs/';

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Upload file desain customer.
     *
     * - Validasi MIME melalui finfo (bukan ekstensi)
     * - Nama file: {orderId}_{timestamp}_{random8}.{ext}
     * - Path di storage: designs/{orderId}/{filename}
     *
     * @param  UploadedFile $file
     * @param  int          $orderId  ID order yang terkait
     * @return string        Path relatif di dalam disk (untuk disimpan di DB)
     *
     * @throws \InvalidArgumentException  Jika MIME tidak diizinkan
     * @throws \RuntimeException          Jika upload gagal
     */
    public function uploadDesignFile(UploadedFile $file, int $orderId): string
    {
        $this->validateMime(
            $file,
            config('printing.upload.allowed_mime_types', []),
            config('printing.upload.allowed_extensions', []),
        );

        $filename = $this->generateFilename($orderId, $file->getClientOriginalExtension());
        $directory = "designs/{$orderId}";

        $path = Storage::disk($this->designDisk())
            ->putFileAs($directory, $file, $filename, ['visibility' => 'private']);

        if ($path === false) {
            throw new \RuntimeException("Upload file desain gagal untuk order #{$orderId}.");
        }

        Log::info('Design file uploaded', [
            'order_id'  => $orderId,
            'disk'      => $this->designDisk(),
            'path'      => $path,
            'mime'      => $file->getMimeType(),
            'size_kb'   => round($file->getSize() / 1024, 1),
        ]);

        return $path;
    }

    /**
     * Upload bukti pembayaran.
     *
     * - Validasi MIME gambar/PDF
     * - Jika file gambar dan ukuran > 2MB → resize & compress via Intervention Image
     * - Nama file: {orderId}_{timestamp}_{random8}.{ext}
     * - Path di storage: payment-proofs/{orderId}/{filename}
     *
     * @param  UploadedFile $file
     * @param  int          $orderId
     * @return string        Path relatif di dalam disk
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function uploadPaymentProof(UploadedFile $file, int $orderId): string
    {
        $this->validateMime(
            $file,
            config('printing.upload.allowed_proof_mime_types', []),
            ['png', 'jpg', 'jpeg', 'webp', 'pdf'],
        );

        $mime      = $this->detectMime($file->getPathname());
        $isImage   = str_starts_with($mime, 'image/');
        $threshold = 2 * 1024 * 1024; // 2 MB

        $ext      = strtolower($file->getClientOriginalExtension());
        $filename = $this->generateFilename($orderId, $ext);
        $directory = "payment-proofs/{$orderId}";
        $disk      = $this->proofDisk();

        // Resize jika gambar > 2 MB
        if ($isImage && $file->getSize() > $threshold) {
            $imageContent = $this->resizePaymentProof($file, $ext);
            $fullPath     = "{$directory}/{$filename}";

            $stored = Storage::disk($disk)->put($fullPath, $imageContent, ['visibility' => 'public']);

            if (! $stored) {
                throw new \RuntimeException("Upload bukti pembayaran gagal (setelah resize) untuk order #{$orderId}.");
            }

            $path = $fullPath;
        } else {
            // Upload langsung tanpa resize
            $prefix = app()->isLocal() ? '' : self::PROOF_PREFIX;
            $path   = Storage::disk($disk)->putFileAs($prefix . $directory, $file, $filename, ['visibility' => 'public']);

            if ($path === false) {
                throw new \RuntimeException("Upload bukti pembayaran gagal untuk order #{$orderId}.");
            }
        }

        Log::info('Payment proof uploaded', [
            'order_id'  => $orderId,
            'disk'      => $disk,
            'path'      => $path,
            'mime'      => $mime,
            'size_kb'   => round($file->getSize() / 1024, 1),
            'resized'   => $isImage && $file->getSize() > $threshold,
        ]);

        return $path;
    }

    /**
     * Hapus file dari storage.
     *
     * @param  string $path  Path relatif di dalam disk
     * @param  string $disk  Nama disk (default: disk desain aktif)
     */
    public function deleteFile(string $path, ?string $disk = null): void
    {
        $disk ??= $this->designDisk();

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
            Log::info('File deleted', ['disk' => $disk, 'path' => $path]);
        }
    }

    /**
     * Buat temporary signed URL untuk file private (file desain).
     *
     * Hanya berlaku untuk disk yang mendukung temporary URL (S3/R2).
     * Di local dev: kembalikan URL serve Laravel biasa (tidak signed).
     *
     * @param  string $path     Path relatif di dalam disk designs
     * @param  int    $minutes  Durasi validitas URL (default: 60 menit)
     * @return string           URL untuk akses file
     */
    public function getTemporaryUrl(string $path, int $minutes = 60): string
    {
        $disk = $this->designDisk();

        // Local dev: disk 'designs-local' menggunakan local driver → tidak ada signed URL
        if (app()->isLocal()) {
            // Kembalikan URL via Laravel route serve (jika ada) atau placeholder
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }

            return '#'; // File belum diupload
        }

        // Production: generate presigned URL dari R2/S3
        try {
            return Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes($minutes),
            );
        } catch (\Throwable $e) {
            Log::error('Gagal generate temporary URL', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return '#';
        }
    }

    /**
     * Dapatkan public URL untuk file payment proof atau thumbnail.
     *
     * @param  string $path
     * @param  string $disk  Nama disk storage
     */
    public function getPublicUrl(string $path, string $disk = 'payment-proofs'): string
    {
        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return '#';
        }
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Validasi MIME type dari konten aktual file (bukan hanya header HTTP).
     * Menggunakan finfo_file() dari PHP fileinfo extension.
     *
     * @throws \InvalidArgumentException
     */
    private function validateMime(UploadedFile $file, array $allowedMimes, array $allowedExtensions): void
    {
        // 1. Validasi ekstensi
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowedExtensions, true)) {
            throw new \InvalidArgumentException(
                "Ekstensi file .{$ext} tidak diizinkan. "
                . "Format yang diterima: " . implode(', ', $allowedExtensions) . '.'
            );
        }

        // 2. Validasi MIME dari konten file (finfo) — lebih aman dari ekstensi saja
        $detectedMime = $this->detectMime($file->getPathname());

        if (! in_array($detectedMime, $allowedMimes, true)) {
            throw new \InvalidArgumentException(
                "Tipe file '{$detectedMime}' tidak diizinkan. "
                . "Pastikan file adalah dokumen desain atau gambar yang valid."
            );
        }
    }

    /**
     * Detect MIME type dari konten file menggunakan PHP finfo.
     */
    private function detectMime(string $filepath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $filepath);
            finfo_close($finfo);

            return $mime ?: 'application/octet-stream';
        }

        // Fallback jika ekstensi fileinfo tidak tersedia
        return mime_content_type($filepath) ?: 'application/octet-stream';
    }

    /**
     * Generate nama file unik yang tidak bisa ditebak.
     *
     * Format: {orderId}_{YmdHis}_{random8chars}.{ext}
     * Contoh: 42_20260511_a3f9b2c1.pdf
     */
    private function generateFilename(int $orderId, string $ext): string
    {
        $ext = strtolower($ext);
        if (! $ext) {
            $ext = 'bin';
        }

        return sprintf(
            '%d_%s_%s.%s',
            $orderId,
            now()->format('YmdHis'),
            Str::random(8),
            $ext,
        );
    }

    /**
     * Resize gambar bukti pembayaran agar ukurannya wajar.
     *
     * Target: max 1500×1500 px, kualitas 80, format tetap.
     * Return: konten binary gambar hasil resize.
     */
    private function resizePaymentProof(UploadedFile $file, string $ext): string
    {
        $image = Image::read($file->getPathname());

        // Scale down jika melebihi batas, pertahankan rasio aspek
        $image->scaleDown(width: 1500, height: 1500);

        // Encode ke format aslinya
        $quality = 80;

        return match (strtolower($ext)) {
            'png'  => (string) $image->toPng(),
            'webp' => (string) $image->toWebp(quality: $quality),
            default => (string) $image->toJpeg(quality: $quality),
        };
    }
}
