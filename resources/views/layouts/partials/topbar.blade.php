{{-- <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-20">
    <div>
        <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-emerald-500 rounded-xl hover:bg-slate-100 transition">
            <i class="ri-menu-2-line text-xl"></i>
        </button>
    </div>


        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 p-1.5 rounded-xl">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-semibold">
                    <i class="ri-user-3-line"></i>
                </div>
                <div>
                    <div class="font-semibold text-xs text-slate-800">{{ Auth::user()->name ?? 'Developer' }}</div>
                    <div class="text-[10px] text-slate-400 font-medium capitalize">{{ Auth::user()->role ?? 'Admin' }}</div>
                </div>
            </div>
        </div>
    
   

</header> --}}

<header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-20">
    <!-- Tombol Hamburger (Sisi Kiri) -->
    <div>
        <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-emerald-500 rounded-xl hover:bg-slate-100 transition">
            <i class="ri-menu-2-line text-xl"></i>
        </button>
    </div>

    <!-- Area Profil & Notifikasi (Sisi Kanan) -->
    <div class="flex items-center gap-4">
        
        <!-- Ikon Lonceng Notifikasi -->
        <button class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-50 transition">
            <i class="ri-notification-3-line text-xl"></i>
        </button>

        <!-- Dropdown Menu Profil -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            
            <!-- Tombol Trigger Profil -->
            <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition group focus:outline-none">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold">
                    <i class="ri-user-3-line text-lg"></i>
                </div>
                <div class="text-left">
                    <div class="font-semibold text-xs text-slate-800 flex items-center gap-1 group-hover:text-emerald-600 transition">
                        <span>{{ Auth::user()->name ?? 'Developer' }}</span>
                        <i class="ri-arrow-down-s-line text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium capitalize">{{ Auth::user()->role ?? 'Admin' }}</div>
                </div>
            </button>

            <!-- Popup Menu Dropdown -->
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50"
                style="display: none;"
            >
                <div class="px-4 py-1.5 mb-1">
                    <p class="text-[11px] text-slate-400 font-medium">Menu Akun</p>
                </div>

                <!-- Link Ganti Password -->
                <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-emerald-600 transition">
                    <i class="ri-lock-2-line text-base text-slate-400"></i>
                    <span>Ganti Password</span>
                </a>

                <div class="border-t border-slate-100 my-1"></div>

                <!-- Form Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-red-500 hover:bg-red-50 transition">
                        <i class="ri-logout-box-r-line text-base text-red-400"></i>
                        <span>Logout Aplikasi</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</header>