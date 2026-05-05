<x-guest-layout>
    <div class="glass-card rounded-2xl shadow-xl p-8">
        <div class="mb-7">
            <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan password baru Anda di bawah ini.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm placeholder-gray-400 transition-all @error('email') border-red-400 bg-red-50 @enderror">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm placeholder-gray-400 transition-all @error('password') border-red-400 bg-red-50 @enderror">
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    placeholder="Ulangi password baru"
                    class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm placeholder-gray-400 transition-all @error('password_confirmation') border-red-400 bg-red-50 @enderror">
                @error('password_confirmation')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full py-3 px-4 text-white text-sm font-semibold rounded-xl">
                Simpan Password Baru
            </button>
        </form>
    </div>
</x-guest-layout>
