<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        /**
         * Ambil semua kategori aktif beserta produk-produknya.
         *
         * Catatan: limit() di dalam with() closure pada Eloquent berlaku secara
         * global (bukan per kategori). Untuk MVP dengan data kecil, kita fetch
         * semua lalu slice di view menggunakan ->take(6).
         * Jika produk sudah ribuan, ganti dengan query UNION atau pakai package
         * "staudenmeir/eloquent-eager-limit".
         */
        $categories = Category::active()
            ->sorted()
            ->with([
                'products' => fn ($q) => $q->active()->sorted(),
            ])
            ->get();

        /**
         * Slide hero banner.
         * Sementara diambil dari array statis — bisa dipindahkan ke tabel
         * "settings" atau CMS di langkah selanjutnya.
         */
        $heroSlides = $this->heroSlides();

        /**
         * Stats singkat untuk social proof di bawah hero.
         */
        $stats = [
            ['label' => 'Produk Tersedia', 'value' => '50+'],
            ['label' => 'Pelanggan Puas',  'value' => '1.200+'],
            ['label' => 'Tahun Pengalaman', 'value' => '10+'],
            ['label' => 'Kota Terjangkau', 'value' => '30+'],
        ];

        return view('home', compact('categories', 'heroSlides', 'stats'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Data slide hero banner.
     * Setiap slide: title, subtitle, cta_label, cta_url, badge, gradient CSS class.
     */
    private function heroSlides(): array
    {
        return [
            [
                'badge'     => '🖨️ Percetakan Terpercaya',
                'title'     => 'Cetak <span class="text-gradient-primary">Berkualitas</span><br>untuk Bisnis Anda',
                'subtitle'  => 'Dari sticker, banner, kaos, hingga merchandise — semua tersedia dengan harga kompetitif dan pengerjaan cepat.',
                'cta_label' => 'Lihat Semua Produk',
                'cta_url'   => route('products.index'),
                'cta2_label' => 'Hubungi Kami',
                'cta2_url'  => 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('printing.company.phone')),
                'image_side' => 'printer',
            ],
            [
                'badge'     => '⚡ Pengerjaan Cepat',
                'title'     => 'Siap dalam <span class="text-gradient-primary">1–3 Hari</span><br>Kerja',
                'subtitle'  => 'Sistem produksi terorganisir memastikan pesanan Anda selesai tepat waktu. Cocok untuk kebutuhan mendesak.',
                'cta_label' => 'Pesan Sekarang',
                'cta_url'   => route('products.index'),
                'cta2_label' => 'Cek Status Pesanan',
                'cta2_url'  => route('orders.index'),
                'image_side' => 'clock',
            ],
            [
                'badge'     => '🎨 Konsultasi Gratis',
                'title'     => 'Tim Desainer<br><span class="text-gradient-primary">Siap Membantu</span> Anda',
                'subtitle'  => 'Belum punya desain? Konsultasikan kebutuhan Anda dengan tim kreatif kami secara gratis via WhatsApp.',
                'cta_label' => 'Konsultasi via WhatsApp',
                'cta_url'   => 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('printing.company.phone')),
                'cta2_label' => 'Lihat Produk',
                'cta2_url'  => route('products.index'),
                'image_side' => 'design',
            ],
        ];
    }
}
