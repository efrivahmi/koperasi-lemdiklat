<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    autoRefresh: {
        type: Boolean,
        default: true
    },
    refreshInterval: {
        type: Number,
        default: 30000 // 30 seconds
    }
});

const stockData = ref([]);
const isLoading = ref(true);
const lastUpdate = ref(null);
let refreshTimer = null;

const fetchStockData = async () => {
    try {
        const response = await fetch('/api/stock-monitor');
        const data = await response.json();
        stockData.value = data;
        lastUpdate.value = new Date();
        isLoading.value = false;
    } catch (error) {
        console.error('Error fetching stock data:', error);
        isLoading.value = false;
    }
};

const getStockStatus = (stock) => {
    if (stock === 0) return 'out';
    if (stock <= 5) return 'critical';
    if (stock <= 10) return 'low';
    return 'good';
};

const getStatusColor = (stock) => {
    const status = getStockStatus(stock);
    switch (status) {
        case 'out':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'critical':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
        case 'low':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        default:
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    }
};

const getStatusText = (stock) => {
    const status = getStockStatus(stock);
    switch (status) {
        case 'out':
            return 'HABIS';
        case 'critical':
            return 'KRITIS';
        case 'low':
            return 'RENDAH';
        default:
            return 'AMAN';
    }
};

const formatTime = (date) => {
    if (!date) return '-';
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

onMounted(() => {
    fetchStockData();

    if (props.autoRefresh) {
        refreshTimer = setInterval(() => {
            fetchStockData();
        }, props.refreshInterval);
    }
});

onUnmounted(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Monitor Stok Real-time</h3>
                        <p class="text-xs text-white/80">Update otomatis setiap 30 detik</p>
                    </div>
                </div>
                <div class="text-right">
                    <button
                        @click="fetchStockData"
                        :disabled="isLoading"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-white text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        <svg class="w-4 h-4 inline-block mr-1" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <p class="text-xs text-white/70 mt-1">
                        Update: {{ formatTime(lastUpdate) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Loading State -->
            <div v-if="isLoading && stockData.length === 0" class="text-center py-12">
                <svg class="w-12 h-12 mx-auto text-indigo-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat data stok...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="stockData.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Tidak ada data produk</p>
            </div>

            <!-- Stock List -->
            <div v-else class="space-y-3">
                <div
                    v-for="product in stockData"
                    :key="product.id"
                    class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                                :class="getStatusColor(product.stock)"
                            >
                                📦
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ product.name }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ product.category?.name || 'Tanpa Kategori' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ product.stock }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">unit</p>
                        </div>
                        <span
                            class="px-3 py-1 rounded-full text-sm font-bold"
                            :class="getStatusColor(product.stock)"
                        >
                            {{ getStatusText(product.stock) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div v-if="stockData.length > 0" class="mt-6 text-center">
                <Link
                    :href="route('stock-ins.create')"
                    class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Stok Barang
                </Link>
            </div>
        </div>
    </div>
</template>
