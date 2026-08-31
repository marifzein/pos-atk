<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Aplikasi - POS ATK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#071e22] min-h-screen flex items-center justify-center font-sans p-4">
    <div class="w-full sm:max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden p-8 border border-slate-100">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                <i class="ri-store-2-line text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Selamat Datang Kembali</h2>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk menggunakan akun anda</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Username</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="username anda" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Password</label>
                
                <!-- Wajib dibungkus div class="relative flex items-center" seperti ini -->
                <div class="relative flex items-center">
                    <input id="password-field" type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-sm">
                    
                    <!-- Tombol Mata berada di dalam container relative -->
                    <button type="button" id="toggle-password" class="absolute right-3.5 text-slate-400 hover:text-emerald-600 focus:outline-none transition-colors">
                        <!-- Icon Mata Terbuka -->
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <!-- Icon Mata Coret -->
                        <svg id="eye-close" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 hidden">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 1-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/20 transition active:scale-[0.98]">
                Masuk Aplikasi
            </button>
        </form>
    </div>

    <!-- Script Native JavaScript untuk Toggle Reveal Password -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordField = document.getElementById('password-field');
            const toggleButton = document.getElementById('toggle-password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClose = document.getElementById('eye-close');

            toggleButton.addEventListener('click', function () {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClose.classList.remove('hidden');
                } else {
                    passwordField.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClose.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>