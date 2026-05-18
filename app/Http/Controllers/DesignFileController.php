<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controller untuk download file desain dari storage private.
 * Hanya bisa diakses via signed URL yang dibuat oleh admin melalui Filament.
 * Route dilindungi middleware: auth, signed, throttle:10,1
 */
class DesignFileController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUpload,
    ) {}

    /**
     * GET /admin/design-download/{orderItem}
     *
     * Middleware:
     *   - auth         → wajib login
     *   - signed       → URL harus punya signature valid (ValidateSignature)
     *   - throttle:10,1 → max 10 request/menit
     *
     * Authorization:
     *   - Hanya admin (is_admin = true)
     */
    public function download(Request $request, OrderItem $orderItem)
    {
        // Double-check: hanya admin yang bisa download file desain
        Gate::authorize('downloadDesign', $orderItem->order);

        $path = $orderItem->design_file_path;

        if (! $path) {
            abort(404, 'File desain tidak ditemukan untuk item ini.');
        }

        // Gunakan temporary URL (60 menit) — redirect ke S3/R2 presigned URL
        $url = $this->fileUpload->getTemporaryUrl($path, 60);

        if ($url === '#') {
            abort(404, 'File desain tidak dapat diakses saat ini.');
        }

        // Di production: redirect ke R2 presigned URL
        // Di local dev: FileUploadService sudah return URL lokal
        return redirect($url);
    }
}
