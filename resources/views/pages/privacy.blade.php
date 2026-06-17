<x-app-layout>
    <x-slot name="title">Kebijakan Privasi</x-slot>
    <x-slot name="description">Kebijakan privasi data pengguna dan perlindungan berkas desain di FD Printing.</x-slot>

    <div class="container-app py-8 max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Kebijakan Privasi</span>
        </nav>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-10">
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">Kebijakan Privasi</h1>
            <p class="text-sm text-gray-500 mb-8">Pembaruan Terakhir: 17 Juni 2026</p>

            <div class="space-y-6 text-sm text-gray-600 leading-relaxed">
                
                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">1. Pengumpulan Informasi Pribadi</h2>
                    <p>
                        Kami mengumpulkan data informasi yang Anda berikan secara langsung saat melakukan registrasi akun, pemesanan produk, dan pengiriman pesan. Informasi ini mencakup nama lengkap, alamat pengiriman, nomor telepon, alamat email, serta data transaksi pembayaran Anda.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">2. Penggunaan Informasi Anda</h2>
                    <p>
                        Informasi pribadi yang kami kumpulkan digunakan secara eksklusif untuk memproses transaksi belanja Anda, mengirimkan pesanan ke alamat tujuan, memverifikasi pembayaran transfer bank, memberikan pemberitahuan status pesanan, serta meningkatkan mutu pelayanan kami.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">3. Kerahasiaan Berkas Desain (Design Files)</h2>
                    <p>
                        Seluruh file desain grafis atau materi cetak yang Anda unggah ke situs kami dilindungi kerahasiaannya. Berkas tersebut hanya akan diakses oleh tim operator produksi kami untuk keperluan cetak pesanan Anda. Kami berkomitmen untuk tidak menyebarluaskan, menyalahgunakan, atau membagikan file desain Anda kepada pihak ketiga tanpa izin tertulis dari Anda.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">4. Penggunaan Cookie</h2>
                    <p>
                        Situs kami menggunakan cookie lokal untuk menyimpan session keranjang belanja (cart) Anda serta status masuk akun. Hal ini bertujuan memberikan pengalaman navigasi belanja yang lancar di situs web kami. Cookie tidak menyimpan informasi pribadi sensitif secara acak.
                    </p>
                </section>

                <section>
                    <h2 class="text-gray-900 font-bold text-base mb-2">5. Hubungi Kami</h2>
                    <p>
                        Apabila Anda memiliki pertanyaan, saran, atau keluhan mengenai kebijakan privasi data ini, Anda dapat menghubungi kami melalui kontak resmi yang tertera di halaman <a href="{{ route('pages.contact') }}" class="text-orange-600 font-semibold hover:underline">Hubungi Kami</a>.
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
