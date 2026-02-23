<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const features = [
    {
        icon: '🏪',
        title: 'Point of Sale (POS)',
        description: 'Sistem kasir modern dengan scan barcode & RFID untuk transaksi cepat dan akurat',
        color: 'from-blue-500 to-cyan-500'
    },
    {
        icon: '👥',
        title: 'Manajemen Siswa',
        description: 'Kelola data siswa, saldo, dan kartu RFID dengan mudah dan terorganisir',
        color: 'from-purple-500 to-pink-500'
    },
    {
        icon: '📦',
        title: 'Manajemen Stok',
        description: 'Pantau stok produk real-time, termasuk barang masuk dan notifikasi stok menipis',
        color: 'from-green-500 to-emerald-500'
    },
    {
        icon: '📊',
        title: 'Laporan Lengkap',
        description: 'Dashboard analitik dengan laporan penjualan, keuangan, dan transaksi siswa',
        color: 'from-orange-500 to-red-500'
    },
    {
        icon: '💳',
        title: 'Sistem Voucher',
        description: 'Buat dan kelola voucher diskon untuk promosi dan reward siswa',
        color: 'from-indigo-500 to-purple-500'
    },
    {
        icon: '🖨️',
        title: 'Print & Export',
        description: 'Cetak struk, barcode, kartu siswa, dan export laporan ke Excel/PDF',
        color: 'from-pink-500 to-rose-500'
    },
    {
        icon: '👤',
        title: 'User Management',
        description: 'Kelola pengguna dengan role-based access (Admin, Staf Koperasi, dll)',
        color: 'from-teal-500 to-cyan-500'
    },
    {
        icon: '💰',
        title: 'Top-up Saldo',
        description: 'Sistem top-up saldo siswa yang mudah dan aman dengan tracking lengkap',
        color: 'from-yellow-500 to-orange-500'
    }
];

const workflow = [
    {
        step: 1,
        title: 'Pendaftaran Siswa',
        description: 'Admin mendaftarkan siswa dan menerbitkan kartu RFID',
        icon: '📝'
    },
    {
        step: 2,
        title: 'Top-up Saldo',
        description: 'Siswa atau orangtua melakukan top-up saldo ke akun siswa',
        icon: '💵'
    },
    {
        step: 3,
        title: 'Belanja di Koperasi',
        description: 'Siswa scan kartu RFID atau barcode produk di kasir',
        icon: '🛒'
    },
    {
        step: 4,
        title: 'Transaksi Otomatis',
        description: 'Sistem memproses pembayaran dan mengurangi saldo otomatis',
        icon: '⚡'
    },
    {
        step: 5,
        title: 'Struk Digital',
        description: 'Cetak struk transaksi sebagai bukti pembelian',
        icon: '🧾'
    },
    {
        step: 6,
        title: 'Laporan Real-time',
        description: 'Admin monitoring transaksi dan keuangan secara real-time',
        icon: '📈'
    }
];

const stats = [
    { value: '100%', label: 'Cashless', icon: '💳' },
    { value: '< 5s', label: 'Transaksi', icon: '⚡' },
    { value: 'Real-time', label: 'Monitoring', icon: '📊' },
    { value: '24/7', label: 'Akses', icon: '🌐' }
];

// Shooting stars animation
let starInterval;
const createShootingStar = () => {
    const star = document.createElement('div');
    star.className = 'shooting-star';
    star.style.top = Math.random() * 50 + '%';
    star.style.right = '0';
    star.style.animationDelay = Math.random() * 3 + 's';
    document.querySelector('.galaxy-bg')?.appendChild(star);

    setTimeout(() => star.remove(), 3000);
};

// Intersection Observer for scroll animations
let observer;

onMounted(() => {
    starInterval = setInterval(createShootingStar, 2000);

    // Setup Intersection Observer for scroll animations
    const animationOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15 // Trigger when 15% visible
    };

    observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Optional: Stop observing once animated if we only want it to happen once
                // observer.unobserve(entry.target); 
            }
        });
    }, animationOptions);

    // Select all elements with scroll-animate class
    const elementsToAnimate = document.querySelectorAll('.scroll-animate');
    elementsToAnimate.forEach(el => observer.observe(el));
});

onUnmounted(() => {
    clearInterval(starInterval);
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <Head title="Koperasi Lemdiklat Taruna Nusantara Indonesia - Sistem Manajemen Koperasi Modern" />

    <div class="min-h-screen galaxy-bg text-white relative flex flex-col overflow-x-hidden">
        <!-- Planets / Abstract Background Shapes -->
        <div class="planet planet-1 opacity-60"></div>
        <div class="planet planet-2 opacity-60"></div>
        
        <!-- Big abstract glowing blobs for ultra-modern feel -->
        <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-purple-600/30 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/30 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-pink-600/20 rounded-full mix-blend-screen filter blur-[120px] animate-blob animation-delay-4000"></div>

        <!-- Navigation (Heavy Glassmorphism) -->
        <nav class="sticky top-0 z-50 bg-[#0f172a]/40 backdrop-blur-2xl border-b border-white/10 shadow-[0_4px_30px_rgba(0,0,0,0.1)] transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20 md:h-24">
                    <div class="flex items-center space-x-3 md:space-x-4 scroll-animate slide-down">
                        <div class="w-10 h-10 md:w-14 md:h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-[0_0_20px_rgba(99,102,241,0.4)] shrink-0">
                            <span class="text-xl md:text-3xl">🏪</span>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0 pr-2">
                            <div class="font-bold text-sm sm:text-lg md:text-xl text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-purple-200 leading-tight truncate">
                                Koperasi Lemdiklat
                            </div>
                            <div class="text-[9px] sm:text-[10px] md:text-xs text-blue-200/60 uppercase tracking-widest truncate">
                                Taruna Nusantara Indonesia
                            </div>
                        </div>
                    </div>
                    <div v-if="canLogin" class="flex items-center space-x-2 sm:space-x-3 md:space-x-4 scroll-animate slide-down delay-200">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 sm:px-5 sm:py-2.5 md:px-6 md:py-3 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 transition-all duration-300 text-xs sm:text-sm md:text-base font-semibold shadow-lg"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 sm:px-5 sm:py-2.5 md:px-6 md:py-3 rounded-xl bg-white/5 hover:bg-white/15 backdrop-blur-md border border-white/10 transition-all duration-300 text-xs sm:text-sm md:text-base shadow-lg"
                            >
                                Login
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="hidden sm:inline-flex px-5 py-2.5 md:px-6 md:py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold transition-all duration-300 text-sm md:text-base shadow-[0_0_20px_rgba(99,102,241,0.4)] hover:shadow-[0_0_30px_rgba(99,102,241,0.6)] hover:-translate-y-0.5"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative flex-1 flex items-center py-12 md:py-20 px-4 sm:px-6 lg:px-8 z-10 min-h-[calc(100vh-6rem)]">
            <div class="max-w-7xl mx-auto w-full">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left space-y-6 md:space-y-8">
                        <div class="scroll-animate fade-up inline-flex items-center px-4 py-2 rounded-full bg-white/5 backdrop-blur-xl border border-white/10 text-xs md:text-sm shadow-xl">
                            <span class="w-2 h-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
                            <span class="text-blue-200">Powered by Koperasi Lemdiklat Taruna Nusantara Indonesia</span>
                        </div>

                        <h1 class="scroll-animate fade-up delay-100 text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1]">
                            <span class="block text-white">Digitalisasi</span>
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400">Koperasi Modern</span>
                        </h1>

                        <p class="scroll-animate fade-up delay-200 text-lg md:text-xl text-blue-100/70 max-w-2xl mx-auto lg:mx-0 font-light leading-relaxed">
                            Platform manajemen koperasi revolusioner untuk SMA Taruna Nusantara Indonesia. Dilengkapi dengan teknologi RFID, cashless payment, dan real-time analytics.
                        </p>

                        <div class="scroll-animate fade-up delay-300 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="group relative px-8 py-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl overflow-hidden hover:bg-white/20 transition-all duration-500 shadow-[0_8px_32px_rgba(0,0,0,0.3)] hover:shadow-[0_8px_32px_rgba(99,102,241,0.4)] hover:-translate-y-1"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/50 to-purple-500/50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <span class="relative flex items-center justify-center font-bold text-lg">
                                    Mulai Sekarang 
                                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </span>
                            </Link>
                            <a
                                href="#features"
                                class="px-8 py-4 bg-transparent border border-white/10 hover:border-white/30 hover:bg-white/5 rounded-2xl transition-all duration-300 font-semibold text-lg flex items-center justify-center backdrop-blur-sm"
                            >
                                Eksplor Fitur
                            </a>
                        </div>
                    </div>

                    <!-- Right Content - Floating UI Elements -->
                    <div class="relative h-[400px] md:h-[600px] hidden lg:block perspective-1000">
                        <!-- Main Dashboard Glass Plate -->
                        <div class="absolute inset-0 bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] transform -rotate-y-12 rotate-x-12 translate-z-0 scroll-animate popup overflow-hidden">
                            <!-- Mock UI Header -->
                            <div class="h-16 border-b border-white/10 flex items-center px-6">
                                <div class="flex space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500/50"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500/50"></div>
                                </div>
                            </div>
                            <!-- Mock Content -->
                            <div class="p-6 grid grid-cols-2 gap-4">
                                <div class="h-24 bg-white/5 rounded-2xl border border-white/5"></div>
                                <div class="h-24 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-2xl border border-purple-500/20"></div>
                                <div class="col-span-2 h-48 bg-white/5 rounded-2xl border border-white/5 flex items-end p-4 space-x-2">
                                    <!-- Bar chart mock -->
                                    <div class="w-full bg-blue-500/40 rounded-t-sm h-[40%]"></div>
                                    <div class="w-full bg-purple-500/40 rounded-t-sm h-[70%]"></div>
                                    <div class="w-full bg-blue-500/40 rounded-t-sm h-[55%]"></div>
                                    <div class="w-full bg-indigo-500/40 rounded-t-sm h-[90%]"></div>
                                    <div class="w-full bg-pink-500/40 rounded-t-sm h-[30%]"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Notification Card 1 -->
                        <div class="absolute -right-10 top-20 bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl shadow-[0_20px_40px_rgba(0,0,0,0.4)] transform translate-z-50 float w-64 scroll-animate slide-left delay-400">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center border border-green-500/30">
                                    <span class="text-green-400">✓</span>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Pembayaran Sukses</div>
                                    <div class="text-xs text-blue-200/60">Siswa: Budi Santoso</div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Notification Card 2 -->
                        <div class="absolute -left-12 bottom-32 bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl shadow-[0_20px_40px_rgba(0,0,0,0.4)] transform translate-z-50 float delay-1000 w-56 scroll-animate slide-right delay-500">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center border border-purple-500/30">
                                    <span>💳</span>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">RFID Detected</div>
                                    <div class="text-xs text-blue-200/60">Saldo: Rp 150.000</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="relative z-10 py-10 px-4 sm:px-6 lg:px-8 border-y border-white/5 bg-white/[0.02] backdrop-blur-3xl">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-10">
                    <div v-for="(stat, index) in stats" :key="stat.label" class="scroll-animate fade-up text-center" :style="`transition-delay: ${index * 150}ms`">
                        <div class="text-4xl mb-3 opactiy-80">{{ stat.icon }}</div>
                        <div class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-indigo-300">{{ stat.value }}</div>
                        <div class="text-xs md:text-sm font-medium text-blue-100/50 uppercase tracking-widest mt-1">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20 scroll-animate fade-up">
                    <h2 class="text-4xl lg:text-5xl font-extrabold mb-6 text-white">
                        Dirancang untuk <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">Efisiensi</span>
                    </h2>
                    <p class="text-lg text-blue-100/60 max-w-3xl mx-auto font-light">
                        Meninggalkan cara konvensional. Mengadopsi teknologi modern untuk operasional koperasi yang transparan, cepat, dan akurat.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="scroll-animate fade-up group relative p-8 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-3xl backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)]"
                        :style="`transition-delay: ${index * 100}ms`"
                    >
                        <!-- Glow effect behind icon -->
                        <div class="absolute top-10 left-10 w-16 h-16 bg-gradient-to-br rounded-2xl filter blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500" :class="feature.color"></div>
                        
                        <div class="relative w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform duration-500 z-10">
                            {{ feature.icon }}
                        </div>
                        <h3 class="text-xl font-bold mb-4 text-white group-hover:text-blue-200 transition-colors">{{ feature.title }}</h3>
                        <p class="text-blue-100/50 text-sm leading-relaxed font-light">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section class="py-24 px-4 sm:px-6 lg:px-8 relative z-10 bg-black/20">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20 scroll-animate fade-up">
                    <h2 class="text-4xl lg:text-5xl font-extrabold mb-6 text-white">
                        Alur Transaksi <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-blue-500">Mulus</span>
                    </h2>
                    <p class="text-lg text-blue-100/60 max-w-3xl mx-auto font-light">
                        Proses operasional end-to-end yang dirancang untuk kecepatan tinggi pada jam istirahat.
                    </p>
                </div>

                <div class="relative">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12 relative z-10">
                        <div
                            v-for="(step, index) in workflow"
                            :key="index"
                            class="scroll-animate fade-up relative p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-lg hover:bg-white/10 hover:border-white/30 transition-all duration-500 group"
                            :style="`transition-delay: ${index * 150}ms`"
                        >
                            <div class="absolute -top-6 left-8 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-black text-xl shadow-[0_10px_20px_rgba(99,102,241,0.5)] group-hover:-translate-y-2 transition-transform duration-500 border border-white/20">
                                {{ step.step }}
                            </div>
                            <div class="text-5xl mb-6 mt-4 opacity-80 group-hover:scale-110 group-hover:opacity-100 origin-left transition-all duration-500">{{ step.icon }}</div>
                            <h3 class="text-2xl font-bold mb-3 text-white">{{ step.title }}</h3>
                            <p class="text-blue-100/50 text-sm leading-relaxed font-light">
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modern CTA Section -->
        <section class="py-24 px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-5xl mx-auto scroll-animate popup">
                <div class="relative p-10 md:p-16 rounded-[40px] bg-gradient-to-br from-indigo-900/40 to-purple-900/40 border border-white/10 backdrop-blur-3xl overflow-hidden shadow-[0_0_100px_rgba(99,102,241,0.2)]">
                    <!-- Decorative background elements inside CTA -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/20 rounded-full filter blur-[80px]"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-pink-500/20 rounded-full filter blur-[80px]"></div>
                    
                    <div class="relative z-10 text-center">
                        <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
                            Siap <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">Digitalisasi?</span>
                        </h2>
                        <p class="text-lg md:text-xl text-blue-100/70 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
                            Mulai transformasi koperasi sekolah Anda menuju sistem digital modern yang transparan dan akuntabel.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-5 justify-center">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="px-10 py-5 rounded-2xl bg-white text-indigo-950 font-black text-lg shadow-[0_10px_30px_rgba(255,255,255,0.2)] hover:shadow-[0_10px_40px_rgba(255,255,255,0.4)] hover:-translate-y-1 transition-all duration-300 transform flex items-center justify-center"
                            >
                                Akses Portal Login
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="px-10 py-5 rounded-2xl bg-white text-indigo-950 font-black text-lg shadow-[0_10px_30px_rgba(255,255,255,0.2)] hover:shadow-[0_10px_40px_rgba(255,255,255,0.4)] hover:-translate-y-1 transition-all duration-300 transform flex items-center justify-center"
                            >
                                Masuk Dashboard
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-black/30 backdrop-blur-xl border-t border-white/5 py-10 px-4 mt-auto relative z-10">
            <div class="max-w-7xl mx-auto text-center scroll-animate fade-up">
                <div class="flex items-center justify-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center border border-white/20">
                        <span class="text-xl">🏪</span>
                    </div>
                    <span class="font-bold text-lg text-white tracking-widest uppercase">Lemdiklat Koperasi</span>
                </div>
                <div class="space-y-2 mb-8">
                    <p class="text-blue-100/40 text-sm font-medium">SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya</p>
                    <p class="text-blue-100/40 text-sm">Kab. Bandung Barat, Jawa Barat</p>
                </div>
                <div class="w-full h-px bg-gradient-to-r from-transparent via-white/10 to-transparent my-8"></div>
                <p class="text-white/20 text-xs font-semibold tracking-wider">
                    &copy; {{ new Date().getFullYear() }} KOPERASI LEMDIKLAT TARUNA NUSANTARA INDONESIA. ALL RIGHTS RESERVED.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* 
 * POWERPOINT-STYLE ANIMATIONS & TRANSITIONS 
 * Fully responsive, hardware-accelerated CSS animations.
 */
html {
    scroll-behavior: smooth;
}

/* Base state for scroll animation elements */
.scroll-animate {
    opacity: 0;
    will-change: transform, opacity;
}

/* 1. Fade Up (Slide up + Fade) */
.scroll-animate.fade-up {
    transform: translateY(60px);
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 1s ease-out;
}
.scroll-animate.fade-up.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* 2. Slide Down (Top-Nav Entrance) */
.scroll-animate.slide-down {
    transform: translateY(-40px);
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.8s ease-out;
}
.scroll-animate.slide-down.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* 3. Slide Left (Entering from Right edge) */
.scroll-animate.slide-left {
    transform: translateX(80px);
    transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1), opacity 1s ease-out;
}
.scroll-animate.slide-left.is-visible {
    opacity: 1;
    transform: translateX(0);
}

/* 4. Slide Right (Entering from Left edge) */
.scroll-animate.slide-right {
    transform: translateX(-80px);
    transition: transform 1s cubic-bezier(0.16, 1, 0.3, 1), opacity 1s ease-out;
}
.scroll-animate.slide-right.is-visible {
    opacity: 1;
    transform: translateX(0);
}

/* 5. Popup / Scale Up (Center focal points) */
.scroll-animate.popup {
    transform: scale(0.9) translateY(40px);
    transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 1s ease-out;
}
.scroll-animate.popup.is-visible {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* Delay Helpers */
.delay-100 { transition-delay: 100ms; }
.delay-200 { transition-delay: 200ms; }
.delay-300 { transition-delay: 300ms; }
.delay-400 { transition-delay: 400ms; }
.delay-500 { transition-delay: 500ms; }
.delay-1000 { transition-delay: 1000ms; }

/* Existing Float & Hover Animations */
.float {
    animation: float 8s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) translateZ(50px); }
    50% { transform: translateY(-20px) translateZ(50px); }
}

@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 15s infinite alternate;
}
.animation-delay-2000 { animation-delay: 2s; }
.animation-delay-4000 { animation-delay: 4s; }

/* 3D Perspective Utils */
.perspective-1000 {
    perspective: 1000px;
}
.transform-3d {
    transform-style: preserve-3d;
}
.translate-z-0 { transform: translateZ(0); }
.translate-z-50 { transform: translateZ(50px); }
</style>
