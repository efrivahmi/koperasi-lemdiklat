<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Selamat Datang - Koperasi Lemdiklat TNI" />

    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        </div>

        <!-- Navigation -->
        <nav class="bg-white/10 backdrop-blur-md border-b border-white/20 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="text-white font-bold text-xl">
                            Koperasi Lemdiklat TNI
                        </div>
                    </div>
                    <div v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-6 py-2.5 rounded-lg bg-white text-blue-900 font-semibold hover:bg-blue-50 transition-all duration-300 transform hover:scale-105 shadow-lg"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-2.5 rounded-lg bg-white/10 text-white font-semibold hover:bg-white/20 transition-all duration-300 border border-white/30"
                            >
                                Masuk
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative z-10">
            <div class="text-center">
                <!-- Logo / Icon -->
                <div class="flex justify-center mb-8 animate-fade-in-down">
                    <div class="w-28 h-28 bg-gradient-to-br from-white/20 to-white/10 backdrop-blur-md rounded-3xl flex items-center justify-center shadow-2xl border border-white/30 transform hover:scale-110 transition-transform duration-300">
                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-4 tracking-tight animate-fade-in">
                    Koperasi Lemdiklat
                </h1>
                <h2 class="text-3xl md:text-5xl font-bold bg-gradient-to-r from-blue-200 to-blue-100 bg-clip-text text-transparent mb-8 animate-fade-in-up">
                    Taruna Nusantara Indonesia
                </h2>

                <!-- Description -->
                <p class="text-lg md:text-xl text-blue-100 mb-12 max-w-3xl mx-auto leading-relaxed animate-fade-in-up">
                    Sistem Point of Sale modern dengan teknologi <span class="font-semibold text-white">RFID Card</span> dan
                    <span class="font-semibold text-white">Barcode Scanner</span> untuk transaksi cepat, aman, dan praktis
                </p>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto mb-16">
                    <div class="group bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:border-white/40 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="text-5xl font-extrabold text-white mb-3 group-hover:scale-110 transition-transform">⚡</div>
                        <div class="text-2xl font-bold text-white mb-2">Fast</div>
                        <div class="text-blue-200">Transaksi kilat dengan scan RFID</div>
                    </div>
                    <div class="group bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:border-white/40 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="text-5xl font-extrabold text-white mb-3 group-hover:scale-110 transition-transform">🔒</div>
                        <div class="text-2xl font-bold text-white mb-2">Secure</div>
                        <div class="text-blue-200">Sistem keamanan berlapis</div>
                    </div>
                    <div class="group bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:border-white/40 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-2xl">
                        <div class="text-5xl font-extrabold text-white mb-3 group-hover:scale-110 transition-transform">✨</div>
                        <div class="text-2xl font-bold text-white mb-2">Easy</div>
                        <div class="text-blue-200">Interface intuitif & mudah</div>
                    </div>
                </div>

                <!-- School Info -->
                <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md rounded-3xl p-10 max-w-5xl mx-auto border border-white/30 shadow-2xl mb-12">
                    <div class="flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="text-3xl font-bold text-white">Melayani Dua Jenjang Pendidikan</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300 transform hover:scale-105">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-blue-500/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-white font-bold text-xl mb-1">SMA Taruna Nusantara Indonesia</div>
                                    <div class="text-blue-200">Melayani siswa & staff SMA dengan sistem pembayaran digital</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300 transform hover:scale-105">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-indigo-500/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-white font-bold text-xl mb-1">SMK Taruna Nusantara Jaya</div>
                                    <div class="text-blue-200">Melayani siswa & staff SMK dengan sistem pembayaran digital</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="mt-12 animate-bounce-slow">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="inline-flex items-center space-x-2 px-10 py-5 bg-white text-blue-900 font-bold text-xl rounded-2xl hover:bg-blue-50 transition-all duration-300 transform hover:scale-110 shadow-2xl hover:shadow-white/20"
                    >
                        <span>Mulai Sekarang</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-white/5 backdrop-blur-sm border-t border-white/10 relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center mb-16">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Fitur Unggulan</h3>
                    <p class="text-blue-200 text-lg max-w-2xl mx-auto">Sistem terintegrasi untuk manajemen koperasi sekolah yang modern dan efisien</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="group text-center bg-gradient-to-br from-white/10 to-white/5 rounded-3xl p-8 backdrop-blur-sm border border-white/20 hover:border-blue-400 transition-all duration-300 transform hover:-translate-y-3 hover:shadow-2xl">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-bold text-xl mb-3">Point of Sale Modern</h4>
                        <p class="text-blue-200 leading-relaxed">Interface seperti e-commerce dengan keranjang belanja digital untuk pengalaman kasir yang cepat dan intuitif</p>
                    </div>
                    <div class="group text-center bg-gradient-to-br from-white/10 to-white/5 rounded-3xl p-8 backdrop-blur-sm border border-white/20 hover:border-green-400 transition-all duration-300 transform hover:-translate-y-3 hover:shadow-2xl">
                        <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-bold text-xl mb-3">RFID Card System</h4>
                        <p class="text-blue-200 leading-relaxed">Kartu pelajar dengan chip RFID untuk pembayaran cashless yang praktis dan aman tanpa perlu bawa uang tunai</p>
                    </div>
                    <div class="group text-center bg-gradient-to-br from-white/10 to-white/5 rounded-3xl p-8 backdrop-blur-sm border border-white/20 hover:border-purple-400 transition-all duration-300 transform hover:-translate-y-3 hover:shadow-2xl">
                        <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-bold text-xl mb-3">Dashboard & Laporan</h4>
                        <p class="text-blue-200 leading-relaxed">Analitik real-time dengan chart interaktif dan export ke Excel untuk monitoring bisnis yang komprehensif</p>
                    </div>
                    <div class="group text-center bg-gradient-to-br from-white/10 to-white/5 rounded-3xl p-8 backdrop-blur-sm border border-white/20 hover:border-yellow-400 transition-all duration-300 transform hover:-translate-y-3 hover:shadow-2xl">
                        <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-bold text-xl mb-3">Flexible Payment</h4>
                        <p class="text-blue-200 leading-relaxed">Mendukung pembayaran tunai dan saldo digital siswa, lengkap dengan top-up dan riwayat transaksi</p>
                    </div>
                </div>

                <!-- Additional Benefits -->
                <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="flex items-start space-x-4 bg-white/5 rounded-2xl p-6 backdrop-blur-sm border border-white/10">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-white font-bold text-lg mb-2">Barcode Scanner</h5>
                            <p class="text-blue-200 text-sm">Scan produk dengan barcode untuk transaksi yang lebih cepat dan akurat</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4 bg-white/5 rounded-2xl p-6 backdrop-blur-sm border border-white/10">
                        <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-white font-bold text-lg mb-2">Thermal Receipt</h5>
                            <p class="text-blue-200 text-sm">Cetak struk otomatis dengan printer thermal 58mm untuk setiap transaksi</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4 bg-white/5 rounded-2xl p-6 backdrop-blur-sm border border-white/10">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-white font-bold text-lg mb-2">Role Management</h5>
                            <p class="text-blue-200 text-sm">4 tingkat akses: Master, Admin, Kasir, dan Siswa dengan permission lengkap</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-black/30 backdrop-blur-md border-t border-white/10 relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="text-white font-bold text-lg">Koperasi Lemdiklat TNI</div>
                        </div>
                        <p class="text-blue-200 text-sm">Sistem Point of Sale modern untuk melayani kebutuhan siswa dan staff dengan teknologi terkini</p>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-4">Melayani</h4>
                        <ul class="space-y-2 text-blue-200 text-sm">
                            <li class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>SMA Taruna Nusantara Indonesia</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>SMK Taruna Nusantara Jaya</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-bold mb-4">Sistem</h4>
                        <ul class="space-y-2 text-blue-200 text-sm">
                            <li>🔐 Keamanan Berlapis</li>
                            <li>📊 Dashboard Analitik</li>
                            <li>💳 RFID & Barcode</li>
                            <li>🖨️ Thermal Printer</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-8 text-center text-blue-200">
                    <p class="mb-2">&copy; 2025 Koperasi Lemdiklat Taruna Nusantara Indonesia. All rights reserved.</p>
                    <p class="text-sm opacity-75">Built with Laravel 12, Vue 3, Inertia.js & Tailwind CSS</p>
                </div>
            </div>
        </footer>
    </div>
</template>
