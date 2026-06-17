<x-app-layout>
    <x-slot name="title">Hubungi Kami</x-slot>
    <x-slot name="description">Informasi kontak, jam operasional, dan lokasi workshop FD Digital Printing di Sidoarjo.</x-slot>

    <div class="container-app py-8 max-w-4xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Hubungi Kami</span>
        </nav>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden p-6 md:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12">
                {{-- Detail Info (5 cols) --}}
                <div class="lg:col-span-5 flex flex-col justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-4">Hubungi Kami</h1>
                        <p class="text-sm text-gray-500 leading-relaxed mb-6">
                            Kami melayani pemesanan langsung di tempat serta konsultasi desain dan bahan secara tatap muka. Silakan hubungi kami atau kunjungi alamat kami di bawah ini.
                        </p>

                        <div class="space-y-5">
                            {{-- Alamat --}}
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Alamat Workshop</p>
                                    <p class="text-sm text-gray-700 font-semibold mt-0.5 leading-relaxed">
                                        {{ config('printing.company.address') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Jam Operasional --}}
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Jam Operasional</p>
                                    <p class="text-sm text-gray-700 font-semibold mt-0.5">
                                        Senin - Sabtu: Buka 24 Jam
                                    </p>
                                    <p class="text-xs text-gray-800 mt-0.5">Hari Minggu: 10.00 - 18.00 WIB</p>
                                </div>
                            </div>

                            {{-- Kontak --}}
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Hubungi Kami</p>
                                    <a href="tel:{{ config('printing.company.phone') }}" class="text-sm text-gray-700 hover:text-orange-600 font-semibold mt-0.5 block">
                                        {{ config('printing.company.phone') }}
                                    </a>
                                    <a href="mailto:{{ config('printing.company.email') }}" class="text-xs text-gray-400 hover:text-orange-600 mt-0.5 block">
                                        {{ config('printing.company.email') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-gray-100 flex items-center gap-3 flex-wrap">
                        <a href="{{ route('home') }}" class="btn-secondary text-sm">
                            &larr; Kembali ke Beranda
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone')) }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-xl transition duration-150 shadow-md shadow-green-500/20">
                            Chat via WhatsApp
                        </a>
                    </div>
                </div>

                {{-- Map Iframe (7 cols) --}}
                <div class="lg:col-span-7">
                    <div class="relative w-full h-[320px] md:h-[420px] rounded-2xl overflow-hidden border border-gray-200 shadow-inner group">
                        <iframe
                            src="https://maps.google.com/maps?q=FD%20Digital%20Printing%20Wadung%20Asri%20Sidoarjo&t=&z=16&ie=UTF8&iwloc=&output=embed"
                            class="absolute inset-0 w-full h-full border-0 grayscale-[20%] contrast-[110%] group-hover:grayscale-0 transition-all duration-300"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi FD Printing"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
