<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in - FlowPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
</head>
<body class="bg-[#0b0c10] min-h-screen flex items-center justify-center p-4 font-sans">

    <!-- Container Utama: Diturunkan lebar max-nya dari max-w-5xl ke max-w-4xl -->
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[500px]">
        
        <!-- Sisi Kiri: Mengambil 7 Kolom (L Lebih Luas) -->
       
        <div class="relative md:col-span-7 flex flex-col items-center justify-center p-8 text-white overflow-hidden ">
            
            
            <!-- Background Image dengan bg-contain (gambar utuh 100% tanpa terpotong) -->
            <div class="absolute inset-0 bg-no-repeat bg-center bg-cover" 
                style="background-image: url('{{ asset('images/ftcopy2.webp') }}'); background-position: center center;">
            </div>

            <!-- Overlay Tosca Transparan -->
            {{-- <div class="absolute inset-0 bg-emerald-700/80 mix-blend-multiply"></div> --}}

            <!-- Konten Sisi Kiri -->
            <div class="relative z-10 flex flex-col items-start">
                <!-- Logo -->
                
                <div class="flex items-center justify-center gap-3">
                        <img src="{{ asset('images/tatakas.png') }}" alt="TATAKAS Logo" class="h-6  object-contain">                    
                </div>
                
                
                <p class="font-light text-emerald-100 mb-10">Toko tertata, usaha makin lancar</p>

                <p class=" text-slate-200  font-light">Mengelola POS, Stock, dan Akuntansi secara praktis, akurat dan terintegrasi.!</p>
                
            </div>
        </div>

        <!-- Sisi Kanan-->
        <div class="md:col-span-5 p-6 md:p-8 flex flex-col justify-between bg-white relative">
            
            <!-- Header Kanan -->
            <div class="text-right">
                <a href="#" class="text-[11px] text-slate-400 hover:text-emerald-600 transition">Create a new Account</a>
            </div>

            <!-- Form Container -->
            <div class="my-auto w-full max-w-[280px] mx-auto">
                <h2 class="text-2xl font-bold text-slate-800 text-center mb-6">Log in</h2>

                <form method="POST" action="{{ route('login') }}" class="space-y-3.5">
                    @csrf
                    
                    <!-- Input Username -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="ri-user-3-line text-base"></i>
                        </span>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Username" 
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                    </div>

                    <!-- Input Password -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="ri-lock-2-line text-base"></i>
                        </span>
                        <input id="password-input" type="password" name="password" required placeholder="Password" 
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                    </div>

                    <!-- Checkbox Show Password & Forgot Password -->
                    <div class="flex items-center justify-between text-[11px] text-slate-500 pt-0.5">
                        <label class="flex items-center cursor-pointer select-none">
                            <input type="checkbox" id="toggle-password" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-1.5 w-3.5 h-3.5">
                            Show my Password
                        </label>
                        <a href="#" class="hover:underline text-slate-400">Forgot Password ?</a>
                    </div>

                    <!-- Tombol Login -->
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-lg shadow-md shadow-emerald-500/20 text-xs transition active:scale-[0.99]">
                            Log in
                        </button>
                    </div>
                </form>

                <!-- Separator -->
                <div class="relative my-6 text-center">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                    <span class="relative bg-white px-2.5 text-[10px] text-slate-400 uppercase tracking-wider">Or with</span>
                </div>

                <!-- Social Login Buttons -->
                <div class="flex justify-center space-x-3">
                    <button type="button" class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-sm shadow hover:opacity-90 transition">
                        <i class="ri-google-fill"></i>
                    </button>
                    <button type="button" class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm shadow hover:opacity-90 transition">
                        <i class="ri-facebook-fill"></i>
                    </button>
                    <button type="button" class="w-8 h-8 rounded-full bg-sky-400 text-white flex items-center justify-center text-sm shadow hover:opacity-90 transition">
                        <i class="ri-twitter-fill"></i>
                    </button>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-300 mt-4">
                &copy; {{ date('Y') }} FlowPOS. All rights reserved.
            </div>
        </div>

    </div>

    <!-- Script Toggle Show Password -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password-input');
            const togglePassword = document.getElementById('toggle-password');

            togglePassword.addEventListener('change', function () {
                passwordInput.type = this.checked ? 'text' : 'password';
            });
        });
    </script>

</body>
</html>