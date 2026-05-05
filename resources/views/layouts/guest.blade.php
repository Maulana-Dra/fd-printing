<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ config('printing.company.tagline') }}">

    <title>{{ $title ?? config('app.name') }} — {{ config('printing.company.name') }}</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .auth-gradient {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #ea580c 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .input-focus:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #c2410c, #b91c1c);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.35);
        }
        .btn-primary:active { transform: translateY(0); }
    </style>
</head>
<body class="antialiased">

    <div class="min-h-screen auth-gradient flex">

        {{-- ── Sisi Kiri: Branding (tersembunyi di mobile) ── --}}
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-center items-center p-12 text-white">
            <div class="max-w-md text-center">
                {{-- Logo / Brand --}}
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
                        <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold mb-2">{{ config('printing.company.name') }}</h1>
                    <p class="text-lg text-white/80 font-light">{{ config('printing.company.tagline') }}</p>
                </div>

                {{-- Fitur Unggulan --}}
                <div class="space-y-4 text-left">
                    @foreach([
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Kualitas cetak premium dengan teknologi terkini'],
                        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Pengerjaan cepat, tepat waktu sesuai jadwal'],
                        ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z', 'text' => 'Konsultasi desain gratis oleh tim profesional'],
                    ] as $feature)
                        <div class="flex items-center gap-3 bg-white/10 rounded-xl p-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-orange-500/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                                </svg>
                            </div>
                            <p class="text-sm text-white/90">{{ $feature['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Sisi Kanan: Form Auth ── --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 bg-gray-50">
            {{-- Logo mobile --}}
            <div class="lg:hidden mb-8 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-orange-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ config('printing.company.name') }}</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

    </div>

</body>
</html>
