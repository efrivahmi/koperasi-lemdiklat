<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: Number,
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4">
        <Head title="404 - Halaman Tidak Ditemukan" />

        <div class="max-w-2xl w-full text-center">
            <!-- Animated 404 -->
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-gray-300 dark:text-gray-700 animate-pulse">
                    404
                </h1>
            </div>

            <!-- Icon -->
            <div class="mb-6">
                <div class="w-32 h-32 mx-auto bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-3">
                    Halaman Tidak Ditemukan
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                    Maaf, halaman yang Anda cari tidak dapat ditemukan. Halaman mungkin telah dipindahkan atau dihapus.
                </p>
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

            <!-- Additional Help -->
            <div class="mt-12 text-sm text-gray-500 dark:text-gray-400">
                <p>Jika masalah berlanjut, silakan hubungi administrator sistem.</p>
            </div>
        </div>
    </div>
</template>
