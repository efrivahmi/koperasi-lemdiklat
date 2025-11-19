<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: Number,
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
        <Head title="500 - Kesalahan Server" />

        <div class="max-w-2xl w-full text-center">
            <!-- Animated 500 -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-purple-300 dark:text-purple-900 animate-pulse">
                    500
                </h1>
            </div>

            <!-- Icon -->
            <div class="mb-6">
                <div class="w-32 h-32 mx-auto bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-3">
                    Terjadi Kesalahan Server
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">
                    Maaf, terjadi kesalahan pada server kami. Tim teknis kami telah diberitahu dan sedang bekerja untuk memperbaiki masalah ini.
                </p>
                <div class="inline-block px-4 py-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <p class="text-sm text-purple-800 dark:text-purple-200">
                        Error Code: {{ status || 500 }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button
                    @click="$inertia.reload()"
                    class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Coba Lagi
                </button>

                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg shadow-md border border-gray-300 dark:border-gray-600 transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Kembali ke Dashboard
                </Link>
            </div>

            <!-- Troubleshooting Tips -->
            <div class="mt-12 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">
                    Apa yang bisa Anda lakukan?
                </h3>
                <ul class="text-sm text-gray-600 dark:text-gray-400 text-left space-y-2">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tunggu beberapa saat dan coba refresh halaman
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Periksa koneksi internet Anda
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Hubungi administrator sistem jika masalah berlanjut
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                <p>Jika Anda perlu bantuan segera, silakan hubungi tim support.</p>
            </div>
        </div>
    </div>
</template>
