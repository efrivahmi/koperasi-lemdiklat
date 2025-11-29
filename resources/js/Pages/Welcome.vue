<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

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
const createShootingStar = () => {
    const star = document.createElement('div');
    star.className = 'shooting-star';
    star.style.top = Math.random() * 50 + '%';
    star.style.right = '0';
    star.style.animationDelay = Math.random() * 3 + 's';
    document.querySelector('.galaxy-bg')?.appendChild(star);

    setTimeout(() => star.remove(), 3000);
};

onMounted(() => {
    // Create shooting stars periodically
    setInterval(createShootingStar, 2000);
});
</script>

<template>
    <Head title="Koperasi Lemdiklat Taruna Nusantara Indonesia - Sistem Manajemen Koperasi Modern" />

    <div class="min-h-screen galaxy-bg text-white relative">
        <!-- Planets -->
        <div class="planet planet-1"></div>
        <div class="planet planet-2"></div>

        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl gradient-purple-blue flex items-center justify-center shadow-lg">
                            <span class="text-2xl">🏪</span>
                        </div>
                        <div>
                            <div class="font-bold text-xl galaxy-text">Koperasi Lemdiklat Taruna Nusantara Indonesia</div>
                            <div class="text-xs text-gray-300">SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya</div>
                            <div class="text-xs text-gray-300">Kab. Bandung Barat</div>
                        </div>
                    </div>
                    <div v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="galaxy-btn"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-3 rounded-lg glass hover:bg-white/20 transition-all duration-300 border border-white/20"
                            >
                                Login
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="galaxy-btn"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative py-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left space-y-8 float">
                        <div class="inline-block px-4 py-2 rounded-full glass border border-purple-400/30 text-sm">
                            <span class="galaxy-text font-semibold">✨ Powered by LEMDIKLAT Taruna Nusantara Indonesia</span>
                        </div>

                        <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
                            Koperasi
                            <span class="galaxy-text block">Digital</span>
                            untuk TARUNA
                        </h1>

                        <h3 class="text-3xl lg:text-2xl font-bold leading-tight">
                            <span class="galaxy-text block">SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya</span>
                        </h3>

                        <p class="text-xl text-gray-300 max-w-2xl">
                            Sistem manajemen koperasi modern dengan teknologi RFID, cashless payment,
                            dan real-time reporting untuk operasional yang lebih efisien.
                        </p>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-8">
                            <div v-for="stat in stats" :key="stat.label" class="galaxy-card text-center">
                                <div class="text-3xl mb-2">{{ stat.icon }}</div>
                                <div class="text-2xl font-bold galaxy-text">{{ stat.value }}</div>
                                <div class="text-sm text-gray-400">{{ stat.label }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="galaxy-btn text-center"
                            >
                                Mulai Sekarang →
                            </Link>
                            <a
                                href="#features"
                                class="px-8 py-4 rounded-lg glass hover:bg-white/20 transition-all duration-300 border border-white/20 text-center font-semibold"
                            >
                                Lihat Fitur
                            </a>
                        </div>
                    </div>

                    <!-- Right Content - Floating Cards -->
                    <div class="relative h-[600px] hidden lg:block">
                        <div class="absolute top-0 right-0 galaxy-card w-64 float" style="animation-delay: 0s;">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-12 h-12 rounded-lg gradient-purple-blue flex items-center justify-center">
                                    <span class="text-2xl">💳</span>
                                </div>
                                <div>
                                    <div class="font-bold">RFID Payment</div>
                                    <div class="text-xs text-gray-400">Tap & Pay</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-300">Transaksi cepat dengan tap kartu RFID</div>
                        </div>

                        <div class="absolute top-40 right-20 galaxy-card w-72 float glow" style="animation-delay: -2s;">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-12 h-12 rounded-lg gradient-purple-blue flex items-center justify-center">
                                    <span class="text-2xl">📊</span>
                                </div>
                                <div>
                                    <div class="font-bold">Real-time Analytics</div>
                                    <div class="text-xs text-gray-400">Live Dashboard</div>
                                </div>
                            </div>
                            <div class="h-32 bg-white/5 rounded-lg mt-3 flex items-end justify-around p-2">
                                <div class="w-8 bg-gradient-to-t from-blue-500 to-purple-500 rounded" style="height: 60%"></div>
                                <div class="w-8 bg-gradient-to-t from-blue-500 to-purple-500 rounded" style="height: 80%"></div>
                                <div class="w-8 bg-gradient-to-t from-blue-500 to-purple-500 rounded" style="height: 40%"></div>
                                <div class="w-8 bg-gradient-to-t from-blue-500 to-purple-500 rounded" style="height: 90%"></div>
                            </div>
                        </div>

                        <div class="absolute bottom-20 right-10 galaxy-card w-64 float" style="animation-delay: -4s;">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-12 h-12 rounded-lg gradient-purple-blue flex items-center justify-center">
                                    <span class="text-2xl">🖨️</span>
                                </div>
                                <div>
                                    <div class="font-bold">Auto Print</div>
                                    <div class="text-xs text-gray-400">Receipt & Reports</div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-300">Cetak struk & laporan otomatis</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-4">
                        <span class="galaxy-text">Fitur Lengkap</span>
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        Semua yang Anda butuhkan untuk mengelola koperasi modern dalam satu platform
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="galaxy-card group cursor-pointer"
                    >
                        <div class="feature-icon mx-auto mb-4" :class="`bg-gradient-to-br ${feature.color}`">
                            <span class="text-3xl">{{ feature.icon }}</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-center">{{ feature.title }}</h3>
                        <p class="text-gray-400 text-center text-sm leading-relaxed">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-4">
                        <span class="galaxy-text">Cara Kerja Sistem</span>
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        Alur transaksi yang simple dan efisien dari pendaftaran hingga pelaporan
                    </p>
                </div>

                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-blue-500 to-pink-500 transform -translate-y-1/2 opacity-30"></div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 relative">
                        <div
                            v-for="(step, index) in workflow"
                            :key="index"
                            class="galaxy-card relative"
                        >
                            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-12 h-12 rounded-full gradient-purple-blue flex items-center justify-center font-bold text-lg shadow-lg glow">
                                {{ step.step }}
                            </div>
                            <div class="text-5xl text-center mb-4 mt-8">{{ step.icon }}</div>
                            <h3 class="text-lg font-bold mb-2 text-center">{{ step.title }}</h3>
                            <p class="text-gray-400 text-sm text-center leading-relaxed">
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-4xl mx-auto">
                <div class="galaxy-card text-center p-12 glow animated-border">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-6">
                        Siap untuk
                        <span class="galaxy-text block mt-2">Digitalisasi Koperasi?</span>
                    </h2>
                    <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                        Bergabunglah dengan sistem manajemen koperasi modern yang telah dipercaya
                        oleh Taruna Nusantara Indonesia untuk operasional yang lebih efisien
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('login')"
                            class="galaxy-btn text-lg px-10 py-4"
                        >
                            Login Sekarang →
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="galaxy-btn text-lg px-10 py-4"
                        >
                            Ke Dashboard →
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="glass border-t border-white/10 py-8 px-4 relative">
            <div class="max-w-7xl mx-auto text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-lg gradient-purple-blue flex items-center justify-center">
                        <span class="text-xl">🏪</span>
                    </div>
                    <span class="font-bold text-lg galaxy-text">Koperasi Lemdiklat Taruna Nusantara Indonesia</span>
                </div>
                <p class="text-gray-400 text-sm mb-2">
                    SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya
                </p>
                <p class="text-gray-400 text-sm mb-4">
                    Kab. Bandung Barat
                </p>
                <p class="text-gray-500 text-xs">
                    &copy; {{ new Date().getFullYear() }} Koperasi Lemdiklat Taruna Nusantara Indonesia. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Additional smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Delay animations */
.delay-1000 {
    animation-delay: 1s;
}
</style>
