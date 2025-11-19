<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: Number,
    message: String,
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
        <Head title="403 - Akses Ditolak" />

        <div class="max-w-2xl w-full text-center">
            <!-- Animated 403 -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-300 dark:text-red-900 animate-pulse">
                    403
                </h1>
            </div>

            <!-- Icon -->
            <div class="mb-6">
                <div class="w-32 h-32 mx-auto bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-3">
                    Akses Ditolak
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">
                    {{ message || 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
                </p>
                <div class="inline-block px-4 py-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <p class="text-sm text-red-800 dark:text-red-200">
                        Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Kembali ke Dashboard
                </Link>

                <button
                    @click="$inertia.visit($page.props.previousUrl || '/')"
                    class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg shadow-md border border-gray-300 dark:border-gray-600 transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Halaman Sebelumnya
                </button>
            </div>

            <!-- Additional Info -->
            <div class="mt-12 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">
                    Informasi Akses
                </h3>
                <ul class="text-sm text-gray-600 dark:text-gray-400 text-left space-y-1">
                    <li>• Master: Akses penuh ke semua fitur</li>
                    <li>• Admin: Kelola produk, stok, siswa, transaksi, laporan</li>
                    <li>• Kasir: Akses POS dan transaksi dasar</li>
                    <li>• Siswa: Lihat saldo dan riwayat transaksi sendiri</li>
                </ul>
            </div>
        </div>
    </div>
</template>
