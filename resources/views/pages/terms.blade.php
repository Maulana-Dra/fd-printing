<x-app-layout>
    <x-slot name="title">Syarat & Ketentuan</x-slot>
    <x-slot name="description">Syarat dan ketentuan umum pemesanan produk dan layanan cetak di FD Printing.</x-slot>

    <div class="container-app py-8 max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Syarat & Ketentuan</span>
        </nav>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-10">
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">Syarat & Ketentuan</h1>
            <p class="text-sm text-gray-500 mb-8">Pembaruan Terakhir: 17 Juni 2026</p>

            <div class="space-y-6 text-sm text-gray-600 leading-relaxed">
                
                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">1. Ketentuan Pembayaran</h2>
                    <p>
                        Setiap pesanan baru akan diproses ke tahap produksi setelah kami menerima pembayaran penuh (Lunas) sesuai dengan nominal invoice tagihan, dan bukti transfer pembayaran telah diverifikasi secara valid oleh tim admin FD Printing.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">2. Kualitas File Desain</h2>
                    <p>
                        Pengguna bertanggung jawab penuh atas kualitas resolusi, kesalahan penulisan (typo), layout, serta hak cipta atas materi/file desain yang diunggah. Kami tidak bertanggung jawab atas hasil cetakan yang pecah, blur, atau salah desain akibat ketidaksesuaian file dengan <a href="{{ route('pages.design-guide') }}" class="text-orange-600 font-semibold hover:underline">Panduan Desain</a> kami.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">3. Waktu Produksi & Pengerjaan</h2>
                    <p>
                        Estimasi waktu produksi normal berkisar antara <strong>1 hingga 3 hari kerja</strong> (tidak termasuk hari Minggu dan libur nasional). Penghitungan waktu produksi dimulai sejak berkas desain dinyatakan "siap cetak" oleh tim produksi dan pembayaran telah diverifikasi.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">4. Kebijakan Retur & Komplain</h2>
                    <p>
                        Komplain mengenai cacat cetak atau ketidaksesuaian kuantitas pesanan wajib menyertakan bukti <strong>video unboxing lengkap</strong> tanpa terpotong. Komplain maksimal diajukan dalam waktu <strong>1x24 jam</strong> sejak paket pesanan diterima berdasarkan status pelacakan kurir. Retur hanya berlaku jika kesalahan murni berasal dari pihak proses produksi kami.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">5. Pembatalan Pesanan</h2>
                    <p>
                        Pesanan yang sudah masuk ke proses produksi tidak dapat dibatalkan secara sepihak oleh pelanggan dengan alasan apa pun. Pengembalian dana (refund) hanya dapat dilakukan jika kami tidak dapat memenuhi pesanan akibat kendala produksi internal yang tidak dapat dihindari.
                    </p>
                </section>

            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('home') }}" class="btn-secondary text-sm">
                    &larr; Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
