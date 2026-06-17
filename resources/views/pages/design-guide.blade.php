<x-app-layout>
    <x-slot name="title">Panduan Upload Desain</x-slot>
    <x-slot name="description">Spesifikasi file desain untuk memastikan kualitas cetak terbaik di FD Printing.</x-slot>

    <div class="container-app py-8 max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Panduan Upload Desain</span>
        </nav>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 md:p-10">
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">Panduan Upload Desain</h1>
            <p class="text-sm text-gray-500 mb-8">Patuhi standar teknis berikut agar hasil cetakan Anda tajam, presisi, dan sesuai ekspektasi:</p>

            <div class="space-y-6 text-sm text-gray-600">
                
                {{-- Format File --}}
                <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                    <h3 class="text-gray-900 font-bold text-base mb-2 flex items-center gap-2">
                        Format File yang Didukung
                    </h3>
                    <p class="leading-relaxed">
                        Kami menerima file desain dengan ekstensi: <strong>PDF, CDR (CorelDRAW), PSD (Photoshop), AI (Illustrator), JPG/JPEG,</strong> atau <strong>PNG</strong>. Untuk hasil terbaik yang tidak pecah saat diperbesar, format berbasis vector (PDF/CDR/AI) sangat direkomendasikan.
                    </p>
                </div>

                {{-- Resolusi Gambar --}}
                <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                    <h3 class="text-gray-900 font-bold text-base mb-2 flex items-center gap-2">
                        Resolusi & Ketajaman Gambar
                    </h3>
                    <p class="leading-relaxed">
                        Pastikan resolusi gambar Anda minimal <strong>300 DPI (Dots Per Inch)</strong> pada dimensi skala cetak asli. Resolusi di bawah nilai ini dapat menyebabkan cetakan tampak pecah, buram, atau bergerigi.
                    </p>
                </div>

                {{-- Mode Warna --}}
                <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                    <h3 class="text-gray-900 font-bold text-base mb-2 flex items-center gap-2">
                        Gunakan Mode Warna CMYK
                    </h3>
                    <p class="leading-relaxed">
                        Desain wajib menggunakan format warna <strong>CMYK</strong>. Jika Anda mengirim file dalam mode RGB, sistem mesin cetak kami akan mengonversinya secara otomatis ke CMYK, yang berisiko mengubah kecerahan atau merubah keakuratan warna asli desain Anda.
                    </p>
                </div>

                {{-- Margin Potong --}}
                <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                    <h3 class="text-gray-900 font-bold text-base mb-2 flex items-center gap-2">
                        Jarak Aman & Margin Potong (Bleed)
                    </h3>
                    <p class="leading-relaxed">
                        Berikan jarak aman (bleed) minimal <strong>2 mm</strong> di sekeliling area luar desain Anda. Hindari meletakkan teks penting atau logo terlalu dekat dengan garis potong untuk mencegah teks terpotong saat proses pemotongan (cutting/finishing).
                    </p>
                </div>

                {{-- Convert Font --}}
                <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                    <h3 class="text-gray-900 font-bold text-base mb-2 flex items-center gap-2">
                        Convert Fonts ke Outlines
                    </h3>
                    <p class="leading-relaxed">
                        Bila menggunakan file vector (CorelDRAW/Illustrator), pastikan semua font sudah diubah menjadi kurva (<strong>Convert to Curves / Outlines</strong>) agar teks tidak berubah (missing font) ketika file dibuka di server kami.
                    </p>
                </div>

            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="btn-secondary text-sm">
                        &larr; Kembali ke Beranda
                    </a>
                    <a href="{{ route('products.index') }}" class="btn-primary">
                        Mulai Belanja
                    </a>
                </div>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}" 
                   target="_blank" rel="noopener"
                   class="text-sm font-semibold text-orange-600 hover:text-orange-700">
                    Tanya Desainer via WhatsApp &rarr;
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
