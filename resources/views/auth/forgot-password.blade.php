<x-guest-layout>
    <div class="glass-card rounded-2xl shadow-xl p-8">
        <div class="mb-7">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Lupa Password?</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan email Anda dan kami akan kirimkan tautan reset password.</p>
        </div>

        @if (session('status'))
            <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Email Terdaftar</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@email.com"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm placeholder-gray-400 transition-all @error('email') border-red-400 bg-red-50 @enderror">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary w-full py-3 px-4 text-white text-sm font-semibold rounded-xl">
                Kirim Tautan Reset Password
            </button>
            <p class="text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-orange-600 hover:text-orange-700 font-semibold">← Kembali ke halaman masuk</a>
            </p>
        </form>
    </div>
</x-guest-layout>
