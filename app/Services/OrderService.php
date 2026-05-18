<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\OrderStatusException;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * OrderService — logika bisnis inti pembuatan dan manajemen order.
 *
 * Tanggungjawab:
 *  - Membuat order dari data keranjang (createFromCart)
 *  - Memindahkan file desain dari temp ke lokasi permanen
 *  - Transisi status order beserta pencatatan log
 */
class OrderService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Buat order baru dari data keranjang + data checkout.
     *
     * Urutan operasi (semua dalam satu DB transaction):
     *   1. Generate order number unik
     *   2. Hitung subtotal, shipping, total
     *   3. Buat record orders
     *   4. Loop cart items → buat order_items (snapshot harga saat itu)
     *   5. Pindahkan file desain temp → designs/{orderId}/
     *   6. Log status awal ke order_status_logs
     *   7. Kosongkan cart (di luar transaction agar tidak rollback kalau cart clear gagal)
     *
     * @param  array  $checkoutData  Validated data dari CheckoutRequest
     * @param  User   $user          User yang melakukan checkout
     * @return Order                 Order yang baru dibuat (dengan relasi items ter-load)
     *
     * @throws \Throwable  Jika terjadi error DB — transaction di-rollback otomatis
     */
    public function createFromCart(array $checkoutData, User $user): Order
    {
        $items        = $this->cart->getItems();
        $subtotal     = $this->cart->getTotal();
        $isDelivery   = ($checkoutData['delivery_type'] ?? 'pickup') === 'delivery';
        $shippingCost = $isDelivery ? (float) ($checkoutData['shipping_cost'] ?? 0) : 0.0;
        $totalAmount  = $subtotal + $shippingCost;

        $order = DB::transaction(function () use (
            $checkoutData, $user, $items, $subtotal, $shippingCost, $totalAmount, $isDelivery
        ) {
            // 1–3. Buat record order
            $order = Order::create([
                'order_number'         => Order::generateOrderNumber(),
                'user_id'              => $user->id,
                'status'               => OrderStatus::PENDING_PAYMENT,
                'subtotal'             => $subtotal,
                'shipping_cost'        => $shippingCost,
                'total_amount'         => $totalAmount,
                'delivery_type'        => $checkoutData['delivery_type'],
                'recipient_name'       => $isDelivery ? ($checkoutData['recipient_name']        ?? null) : null,
                'recipient_phone'      => $isDelivery ? ($checkoutData['recipient_phone']       ?? null) : null,
                'shipping_address'     => $isDelivery ? ($checkoutData['shipping_address']      ?? null) : null,
                'shipping_city'        => $isDelivery ? ($checkoutData['shipping_city']         ?? null) : null,
                'shipping_province'    => $isDelivery ? ($checkoutData['shipping_province']     ?? null) : null,
                'shipping_postal_code' => $isDelivery ? ($checkoutData['shipping_postal_code']  ?? null) : null,
                'courier'              => $isDelivery ? ($checkoutData['courier']               ?? null) : null,
                'courier_service'      => $isDelivery ? ($checkoutData['courier_service']       ?? null) : null,
                'notes'                => $checkoutData['notes'] ?? null,
            ]);

            // 4. Buat order_items — snapshot harga, nama produk, opsi saat checkout
            foreach ($items as $item) {
                // Pindahkan file desain dari temp ke lokasi permanen
                $permanentPath = $this->moveDesignFileFromTemp(
                    $item['design_file_path'] ?? null,
                    $order->id,
                );

                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item['product_id'],
                    'product_name'     => $item['product_name'],   // snapshot
                    'unit_price'       => $item['unit_price'],     // snapshot harga
                    'quantity'         => $item['quantity'],
                    'subtotal'         => $item['subtotal'],
                    'selected_options' => $item['selected_options'] ?? [],
                    'design_file_path' => $permanentPath,
                    'design_file_name' => $item['design_file_path']
                        ? basename($item['design_file_path'])
                        : null,
                    'design_notes'     => $item['design_notes'] ?? null,
                ]);
            }

            // 6. Log status awal (from_status = null = order baru dibuat)
            OrderStatusLog::create([
                'order_id'   => $order->id,
                'from_status' => null,
                'to_status'   => OrderStatus::PENDING_PAYMENT->value,
                'changed_by'  => $user->id,
                'notes'       => 'Order dibuat oleh customer.',
            ]);

            Log::channel('order_logs')->info('Order created', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'user_id'      => $user->id,
                'total'        => $totalAmount,
                'items_count'  => $items->count(),
                'delivery'     => $checkoutData['delivery_type'],
            ]);

            return $order;
        });

        // 7. Kosongkan cart (di luar transaction agar session clear tidak di-rollback)
        $this->cart->clear();

        // 8. Kirim notifikasi WA ke customer (async, non-blocking)
        SendWhatsAppNotification::dispatch($order, 'orderConfirmation');

        return $order->load('items');
    }

    /**
     * Ubah status order dengan validasi transisi enum dan pencatatan log.
     *
     * @param  Order        $order      Order yang akan diubah statusnya
     * @param  OrderStatus  $newStatus  Status tujuan
     * @param  string|null  $notes      Catatan opsional (alasan, info kurir, dll.)
     * @param  User|null    $changedBy  User yang mengubah (null = sistem/otomatis)
     *
     * @throws \DomainException  Jika transisi status tidak diizinkan
     */
    public function updateStatus(
        Order $order,
        OrderStatus $newStatus,
        ?string $notes = null,
        ?User $changedBy = null,
    ): void {
        $currentStatus = $order->status;

        // Validasi transisi via Enum method
        if (! $currentStatus->canTransitionTo($newStatus)) {
            $ex = OrderStatusException::invalidTransition(
                $currentStatus->value,
                $newStatus->value,
                $order->id,
            );
            Log::channel('order_logs')->warning('Invalid status transition attempted', $ex->getContext());
            throw $ex;
        }

        DB::transaction(function () use ($order, $currentStatus, $newStatus, $notes, $changedBy) {
            // Update status di tabel orders
            $order->update(['status' => $newStatus]);

            // Catat di order_status_logs
            OrderStatusLog::create([
                'order_id'    => $order->id,
                'from_status' => $currentStatus->value,
                'to_status'   => $newStatus->value,
                'changed_by'  => $changedBy?->id,
                'notes'       => $notes,
            ]);

            Log::channel('order_logs')->info('Order status updated', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'from_status'  => $currentStatus->value,
                'to_status'    => $newStatus->value,
                'changed_by'   => $changedBy?->id,
                'notes'        => $notes,
            ]);
        });

        // Hook notifikasi — diperluas di langkah berikutnya
        $this->dispatchStatusNotification($order, $newStatus);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Pindahkan file desain dari direktori temp ke lokasi permanen
     * berdasarkan order ID yang sudah diketahui.
     *
     * Temp path:  designs/temp/{filename}
     * Final path: designs/{orderId}/{filename}
     *
     * @param  string|null $tempPath   Path relatif di disk designs-local
     * @param  int         $orderId
     * @return string|null             Path permanen, atau null jika tidak ada file
     */
    private function moveDesignFileFromTemp(?string $tempPath, int $orderId): ?string
    {
        if (! $tempPath) {
            return null;
        }

        $disk = app()->isLocal() ? 'designs-local' : 'designs';

        if (! Storage::disk($disk)->exists($tempPath)) {
            Log::channel('order_logs')->warning('Design temp file not found — skipping move', [
                'temp_path' => $tempPath,
                'order_id'  => $orderId,
            ]);
            return null;
        }

        $filename    = basename($tempPath);
        $finalPath   = "designs/{$orderId}/{$filename}";

        $moved = Storage::disk($disk)->move($tempPath, $finalPath);

        if (! $moved) {
            Log::channel('order_logs')->error('Failed to move design file from temp', [
                'from'     => $tempPath,
                'to'       => $finalPath,
                'order_id' => $orderId,
            ]);
            // Kembalikan temp path agar data tidak hilang; admin bisa perbaiki manual
            return $tempPath;
        }

        Log::channel('order_logs')->info('Design file moved to permanent storage', [
            'order_id' => $orderId,
            'from'     => $tempPath,
            'to'       => $finalPath,
        ]);

        return $finalPath;
    }

    /**
     * Dispatch notifikasi berdasarkan status baru.
     */
    private function dispatchStatusNotification(Order $order, OrderStatus $newStatus): void
    {
        $type = match ($newStatus) {
            OrderStatus::PAID       => 'paymentApproved',
            OrderStatus::SHIPPED    => 'orderShipped',
            default                 => null,
        };

        if ($type === null) {
            return;
        }

        $extra = [];
        if ($type === 'orderShipped' && $order->tracking_number) {
            $extra['trackingNumber'] = $order->tracking_number;
        }

        SendWhatsAppNotification::dispatch($order, $type, $extra);

        Log::channel('order_logs')->info('WA notification dispatched from OrderService', [
            'order_number' => $order->order_number,
            'type'         => $type,
        ]);
    }
}
