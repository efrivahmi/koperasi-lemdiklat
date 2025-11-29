<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentSales: Array,
    lowStockList: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dashboard Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Dashboard Kasir</h2>
                    <p class="text-sm text-gray-600 mt-1">Selamat datang kembali! Berikut ringkasan operasional hari ini.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Quick Access -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="text-white mb-4 md:mb-0">
                            <h3 class="text-lg font-semibold">Akses Cepat</h3>
                            <p class="text-blue-100 text-sm">Mulai transaksi penjualan</p>
                        </div>
                        <div>
                            <Link :href="route('pos.index')"
                                class="bg-white text-blue-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-blue-50 inline-flex items-center gap-3 shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Buka POS
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Today Revenue -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pendapatan Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.todayRevenue) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ stats.todayTransactions }} transaksi</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- My Sales -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Penjualan Saya</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.myRevenue) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ stats.myTransactions }} transaksi</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.totalProducts }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ stats.totalCategories }} kategori</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Stok Menipis</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.lowStockCount }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ stats.outOfStockProducts }} habis</p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products Alert -->
                <div v-if="lowStockList.length > 0" class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-red-50 border-l-4 border-red-500 p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-semibold text-red-800">Peringatan Stok Menipis</h3>
                                <p class="text-sm text-red-700 mt-1">Produk berikut perlu segera diisi ulang stoknya:</p>
                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-red-200">
                                        <thead class="bg-red-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Produk</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Kategori</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Stok</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-800 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-red-100">
                                            <tr v-for="product in lowStockList" :key="product.id" class="hover:bg-red-50">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ product.name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.category?.name || '-' }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span :class="product.stock === 0 ? 'text-red-700 font-bold' : 'text-orange-600 font-semibold'">
                                                        {{ product.stock }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span v-if="product.stock === 0" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        HABIS
                                                    </span>
                                                    <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        RENDAH
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Penjualan Terbaru Hari Ini</h3>
                        <p class="text-sm text-gray-600">Transaksi penjualan terakhir</p>
                    </div>
                    <div v-if="recentSales.length === 0" class="p-8 text-center">
                        <div class="text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm text-gray-500">Belum ada penjualan hari ini</p>
                        </div>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ sale.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ new Date(sale.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ sale.student?.user?.name || 'Cash' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ sale.items.length }} item
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(sale.total) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span v-if="sale.payment_method === 'cash'" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            TUNAI
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            SALDO
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ sale.creator?.name || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
