<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mengirim notifikasi WhatsApp secara asinkron.
 *
 * Dieksekusi di background queue agar tidak memblokir HTTP response.
 * Kegagalan job hanya di-log — tidak di-retry otomatis karena WA
 * yang gagal terkirim lebih baik di-skip daripada mengirim duplikat.
 *
 * Cara dispatch:
 *   SendWhatsAppNotification::dispatch($order, 'orderConfirmation');
 *   SendWhatsAppNotification::dispatch($order, 'orderShipped', ['trackingNumber' => 'JNE1234']);
 */
class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tidak ada retry — WA gagal lebih baik di-skip.
     * @var int
     */
    public int $tries = 1;

    /**
     * Timeout per job (detik).
     * @var int
     */
    public int $timeout = 30;

    /**
     * Tipe notifikasi yang akan dikirim.
     *
     * Nilai yang valid:
     *   - 'orderConfirmation'
     *   - 'paymentReceived'
     *   - 'paymentApproved'
     *   - 'orderShipped'   (butuh $extra['trackingNumber'])
     *   - 'adminNewPayment'
     */
    public function __construct(
        public readonly Order  $order,
        public readonly string $type,
        public readonly array  $extra = [],
    ) {}

    /**
     * Eksekusi job.
     */
    public function handle(WhatsAppService $wa): void
    {
        try {
            match ($this->type) {
                'orderConfirmation' => $wa->sendOrderConfirmation($this->order),
                'paymentReceived'   => $wa->sendPaymentReceived($this->order),
                'paymentApproved'   => $wa->sendPaymentApproved($this->order),
                'orderShipped'      => $wa->sendOrderShipped(
                    $this->order,
                    $this->extra['trackingNumber'] ?? '',
                ),
                'adminNewPayment'   => $wa->sendAdminNewPayment($this->order),
                default             => Log::warning('SendWhatsAppNotification: unknown type', [
                    'type'         => $this->type,
                    'order_number' => $this->order->order_number,
                ]),
            };
        } catch (\Throwable $e) {
            // Kegagalan WA tidak boleh mengganggu alur utama — log saja
            Log::error('SendWhatsAppNotification job failed', [
                'type'         => $this->type,
                'order_number' => $this->order->order_number,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Callback jika job gagal setelah semua percobaan habis.
     * (tries=1 jadi ini hanya dipanggil jika ada unhandled exception)
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppNotification permanently failed', [
            'type'         => $this->type,
            'order_number' => $this->order->order_number,
            'error'        => $exception->getMessage(),
        ]);
    }
}
