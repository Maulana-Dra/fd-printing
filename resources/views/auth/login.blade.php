<x-guest-layout>
    <div class="glass-card rounded-2xl shadow-xl p-8">

        {{-- Header --}}
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Masuk ke Akun</h2>
            <p class="text-sm text-gray-500 mt-1">Selamat datang kembali di <span class="font-semibold text-orange-600">{{ config('printing.company.name') }}</span></p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Alamat Email
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    placeholder="nama@email.com"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 @error('email') border-red-400 bg-red-50 @enderror"
                >
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-orange-600 hover:text-orange-700 font-medium">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 @error('password') border-red-400 bg-red-50 @enderror"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-2">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                >
                <label for="remember_me" class="text-sm text-gray-600 cursor-pointer select-none">
                    Ingat saya di perangkat ini
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-primary w-full py-3 px-4 text-white text-sm font-semibold rounded-xl shadow-sm">
                Masuk
            </button>

            {{-- Register Link --}}
            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-orange-600 hover:text-orange-700 font-semibold">
                        Daftar sekarang
                    </a>
                </p>
            @endif
        </form>
    </div>
</x-guest-layout>
