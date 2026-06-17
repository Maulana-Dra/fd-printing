<x-app-layout>
    <x-slot name="title">Cara Pemesanan</x-slot>
    <x-slot name="description">Panduan lengkap langkah demi langkah untuk memesan produk percetakan di FD Printing.</x-slot>

    <div class="container-app py-8 max-w-3xl">
        
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Cara Pemesanan</span>
        </nav>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-10">
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">Cara Pemesanan</h1>
            <p class="text-sm text-gray-500 mb-8">Ikuti panduan mudah berikut untuk melakukan transaksi cetak di platform kami:</p>

            <div class="space-y-8 relative before:absolute before:left-[19px] before:top-4 before:bottom-4 before:w-0.5 before:bg-gray-100">
                
                {{-- Step 1 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        1
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Pilih Produk & Spesifikasi</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Cari dan pilih produk percetakan yang Anda butuhkan melalui katalog produk. Tentukan opsi spesifikasi seperti jenis bahan, ukuran, dan kuantitas produk.
                        </p>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        2
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Unggah File Desain</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Unggah file desain Anda pada kolom yang disediakan. Pastikan desain Anda sudah mengikuti <a href="{{ route('pages.design-guide') }}" class="text-orange-600 font-semibold hover:underline">Panduan Desain</a> kami untuk hasil cetak yang maksimal. Jika belum memiliki desain, Anda dapat berkonsultasi secara gratis dengan tim kami via WhatsApp.
                        </p>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        3
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Masukkan Keranjang & Checkout</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Klik tombol "Tambah ke Keranjang", tinjau kembali daftar pesanan Anda, lalu klik "Checkout". Lengkapi alamat pengiriman, pilih kurir, dan selesaikan pembuatan pesanan.
                        </p>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        4
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Lakukan Pembayaran</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Transfer nominal pembayaran sesuai tagihan Anda ke salah satu rekening bank resmi FD Printing yang tertera pada detail tagihan checkout.
                        </p>
                    </div>
                </div>

                {{-- Step 5 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        5
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Upload Bukti Transfer</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Masuk ke menu <strong>"Pesanan Saya"</strong> di akun Anda, buka detail pesanan, kemudian klik tombol <strong>"Upload Bukti Pembayaran"</strong> untuk mengirim konfirmasi agar segera diverifikasi oleh tim admin kami.
                        </p>
                    </div>
                </div>

                {{-- Step 6 --}}
                <div class="flex gap-4 relative">
                    <div class="w-10 h-10 rounded-full bg-orange-50 border-2 border-orange-500 flex items-center justify-center text-orange-600 font-bold shrink-0 z-10">
                        6
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Proses Produksi & Pengiriman</h2>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Setelah pembayaran diverifikasi, pesanan Anda akan masuk tahap antrean cetak. Estimasi pengerjaan adalah 1–3 hari kerja. Setelah selesai, produk akan dikirim ke alamat Anda atau dapat diambil langsung di toko fisik kami.
                        </p>
                    </div>
                </div>

            </div>

            <div class="mt-10 pt-6 border-t border-gray-100 flex justify-between items-center flex-wrap gap-4">
                <a href="{{ route('home') }}" class="btn-secondary text-xs">
                    &larr; Kembali ke Beranda
                </a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}" 
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-xl transition duration-150 shadow-md shadow-green-500/20">
                    Hubungi via WhatsApp
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
