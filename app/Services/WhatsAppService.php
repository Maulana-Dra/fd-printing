<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppService — kirim pesan WA via Fonnte API.
 *
 * Strategi:
 *  - Semua method high-level (sendOrderConfirmation, dll.) hanya memformat
 *    pesan dan memanggil send(). Dispatch job dilakukan dari luar service ini.
 *  - Jika WA_NOTIFICATIONS_ENABLED=false, semua pesan di-skip (local dev).
 *  - Kegagalan API hanya di-log, tidak melempar exception — agar tidak
 *    memblokir alur utama order.
 */
class WhatsAppService
{
    // ── Config helpers ────────────────────────────────────────────────────────

    private function isEnabled(): bool
    {
        return (bool) config('printing.whatsapp.enabled', false);
    }

    private function prefix(): string
    {
        return config('printing.whatsapp.message_prefix', '🖨️ *FD Printing*');
    }

    private function adminNumber(): string
    {
        return (string) config('printing.whatsapp.admin_number', '');
    }

    // ── Low-level send ────────────────────────────────────────────────────────

    /**
     * Kirim satu pesan ke nomor tertentu via Fonnte API.
     *
     * @param  string $phone    Nomor tujuan (format: 628xxx)
     * @param  string $message  Teks pesan (mendukung markdown WA)
     * @return bool             true jika API mengembalikan sukses
     */
    public function send(string $phone, string $message): bool
    {
        if (! $this->isEnabled()) {
            Log::channel('whatsapp_logs')->debug('WhatsApp notification skipped (disabled)', [
                'phone'   => $phone,
                'preview' => substr($message, 0, 80),
            ]);
            return true; // Anggap sukses agar caller tidak perlu handle
        }

        $token = config('printing.whatsapp.fonnte_token', '');
        $url   = config('printing.whatsapp.fonnte_url', 'https://api.fonnte.com/send');

        if (empty($token)) {
            Log::channel('whatsapp_logs')->warning('WhatsApp send skipped: FONNTE_TOKEN belum dikonfigurasi.');
            return false;
        }

        if (empty($phone)) {
            Log::channel('whatsapp_logs')->warning('WhatsApp send skipped: nomor tujuan kosong.', ['preview' => substr($message, 0, 80)]);
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $token])
                ->post($url, [
                    'target'  => $phone,
                    'message' => $message,
                    'delay'   => 2, // detik, agar tidak terhitung spam
                ]);

            $success = $response->successful() && ($response->json('status') !== false);

            if (! $success) {
                Log::channel('whatsapp_logs')->warning('WhatsApp API response not success', [
                    'phone'       => $phone,
                    'http_status' => $response->status(),
                    'body'        => substr($response->body(), 0, 300),
                ]);
            } else {
                Log::channel('whatsapp_logs')->info('WhatsApp sent successfully', [
                    'phone' => $phone,
                    'chars' => strlen($message),
                ]);
            }

            return $success;

        } catch (\Throwable $e) {
            Log::channel('whatsapp_logs')->error('WhatsApp send exception', [
                'phone'   => $phone,
                'error'   => $e->getMessage(),
                'preview' => substr($message, 0, 80),
            ]);
            return false;
        }
    }

    // ── High-level notification methods ───────────────────────────────────────

    /**
     * Kirim konfirmasi order baru ke customer.
     * Dipanggil setelah order berhasil dibuat.
     */
    public function sendOrderConfirmation(Order $order): void
    {
        $phone = $this->normalizePhone($order->user?->phone ?? '');
        if (! $phone) return;

        $nama  = $order->user->name;
        $nomor = $order->order_number;
        $total = 'Rp ' . number_format((float) $order->total_amount, 0, ',', '.');

        $message = <<<MSG
{$this->prefix()}

Halo *{$nama}*, pesanan Anda telah kami terima! 🎉

📋 *Nomor Pesanan:* {$nomor}
💰 *Total Pembayaran:* {$total}

Silakan lakukan pembayaran sesuai total di atas dan upload bukti pembayaran melalui link berikut:
{$this->orderPaymentUrl($order)}

Jika ada pertanyaan, hubungi kami kapan saja. Terima kasih! 🙏
MSG;

        $this->send($phone, $message);
    }

    /**
     * Kirim notifikasi bahwa bukti pembayaran sudah diterima dan sedang diverifikasi.
     */
    public function sendPaymentReceived(Order $order): void
    {
        $phone = $this->normalizePhone($order->user?->phone ?? '');
        if (! $phone) return;

        $nama  = $order->user->name;
        $nomor = $order->order_number;

        $message = <<<MSG
{$this->prefix()}

Halo *{$nama}*,

✅ Bukti pembayaran untuk pesanan *{$nomor}* sudah kami terima dan sedang dalam proses verifikasi.

Tim kami akan memverifikasi dalam 1×24 jam. Kami akan mengirimkan notifikasi jika pembayaran sudah dikonfirmasi.

Terima kasih telah berbelanja di FD Printing! 🙏
MSG;

        $this->send($phone, $message);
    }

    /**
     * Kirim notifikasi bahwa pembayaran sudah diverifikasi dan order masuk produksi.
     */
    public function sendPaymentApproved(Order $order): void
    {
        $phone = $this->normalizePhone($order->user?->phone ?? '');
        if (! $phone) return;

        $nama  = $order->user->name;
        $nomor = $order->order_number;
        $days  = config('printing.order.default_production_days', 3);

        $message = <<<MSG
{$this->prefix()}

Halo *{$nama}*,

🎊 Pembayaran pesanan *{$nomor}* telah *dikonfirmasi*!

Pesanan Anda kini sedang masuk ke antrian produksi dan akan selesai dalam estimasi *{$days} hari kerja*.

Kami akan menghubungi Anda kembali jika pesanan sudah siap. 🖨️
MSG;

        $this->send($phone, $message);
    }

    /**
     * Kirim notifikasi bahwa order sudah dikirim beserta nomor resi.
     *
     * @param  Order  $order
     * @param  string $trackingNumber  Nomor resi pengiriman
     */
    public function sendOrderShipped(Order $order, string $trackingNumber): void
    {
        $phone = $this->normalizePhone($order->user?->phone ?? '');
        if (! $phone) return;

        $nama   = $order->user->name;
        $nomor  = $order->order_number;
        $kurir  = strtoupper($order->courier ?? 'Kurir');

        $message = <<<MSG
{$this->prefix()}

Halo *{$nama}*,

📦 Pesanan *{$nomor}* sudah dikirim!

🚚 *Kurir:* {$kurir}
📝 *No. Resi:* `{$trackingNumber}`

Lacak pengirimannya di website kurir atau hubungi kami jika ada kendala.

Terima kasih telah berbelanja di FD Printing! 🙏
MSG;

        $this->send($phone, $message);
    }

    /**
     * Kirim notifikasi ke admin bahwa ada konfirmasi pembayaran baru.
     */
    public function sendAdminNewPayment(Order $order): void
    {
        $adminPhone = $this->adminNumber();
        if (! $adminPhone) {
            Log::channel('whatsapp_logs')->warning('WhatsApp admin notification skipped: ADMIN_WA_NUMBER belum dikonfigurasi.');
            return;
        }

        $customerName = $order->user?->name ?? 'Unknown';
        $nomor        = $order->order_number;
        $total        = 'Rp ' . number_format((float) $order->total_amount, 0, ',', '.');
        $adminUrl     = config('app.url') . '/admin/orders';

        $message = <<<MSG
{$this->prefix()} — *Notifikasi Admin*

💳 Ada konfirmasi pembayaran baru!

👤 *Customer:* {$customerName}
📋 *Order:* {$nomor}
💰 *Total:* {$total}

Segera verifikasi di panel admin:
{$adminUrl}
MSG;

        $this->send($adminPhone, $message);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Normalisasi nomor HP ke format internasional 628xxx.
     * Mendukung format: 08xxx, +628xxx, 628xxx.
     * Return string kosong jika nomor tidak valid.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (empty($phone)) {
            return '';
        }

        // Hapus + di awal
        $phone = ltrim($phone, '+');

        // 08xxx → 628xxx
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        // Validasi minimal 10 digit (62 + 8 digit)
        if (strlen($phone) < 10) {
            return '';
        }

        return $phone;
    }

    /**
     * URL halaman pembayaran order.
     */
    private function orderPaymentUrl(Order $order): string
    {
        return route('orders.payment', $order);
    }
}
