<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';

const props = defineProps({
    adjustments: Object,
    summary: Object,
    products: Array,
    adjusters: Array,
    filters: Object,
    error: String,
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    product_id: props.filters?.product_id || '',
    type: props.filters?.type || '',
    adjusted_by: props.filters?.adjusted_by || '',
    search: props.filters?.search || '',
});

const applyFilters = () => {
    router.get(route('reports.stock-adjustments'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchForm.value = {
        date_from: '',
        date_to: '',
        product_id: '',
        type: '',
        adjusted_by: '',
        search: '',
    };
    applyFilters();
};

const exportToExcel = () => {
    const params = new URLSearchParams(searchForm.value);
    window.location.href = route('reports.stock-adjustments.export') + '?' + params.toString();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTypeBadge = (type) => {
    if (type === 'addition') {
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    }
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};

const getTypeText = (type) => {
    if (type === 'addition') {
        return 'Penambahan';
    }
    return 'Pengurangan';
};

const getTypeIcon = (type) => {
    if (type === 'addition') {
        return '➕';
    }
    return '➖';
};
</script>

<template>
    <Head title="Laporan Penyesuaian Stok" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">Laporan Penyesuaian Stok</h2>
                <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    Riwayat penyesuaian stok manual produk
                </p>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Error Message -->
                <div v-if="error" class="mb-4 sm:mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900/30 dark:border-red-700 dark:text-red-400">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"> {{ error }}</span>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">
                    <!-- Total Adjustments -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-xs sm:text-sm font-medium">Total Penyesuaian</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.total_adjustments }}</h3>
                                <p class="text-indigo-100 text-xs mt-1">Periode ini</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">📦</div>
                        </div>
                    </div>

                    <!-- Total Additions -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-xs sm:text-sm font-medium">Total Penambahan</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.total_additions }}</h3>
                                <p class="text-green-100 text-xs mt-1">{{ summary.additions_count }} transaksi</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">➕</div>
                        </div>
                    </div>

                    <!-- Total Deductions -->
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-xs sm:text-sm font-medium">Total Pengurangan</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.total_deductions }}</h3>
                                <p class="text-red-100 text-xs mt-1">{{ summary.deductions_count }} transaksi</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">➖</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="font-semibold text-base sm:text-lg mb-4">Filter Laporan</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">
                            <!-- Date From -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Dari Tanggal
                                </label>
                                <input
                                    type="date"
                                    v-model="searchForm.date_from"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Sampai Tanggal
                                </label>
                                <input
                                    type="date"
                                    v-model="searchForm.date_to"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                            </div>

                            <!-- Product -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Produk
                                </label>
                                <select
                                    v-model="searchForm.product_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                >
                                    <option value="">Semua Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.barcode }})
                                    </option>
                                </select>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tipe Penyesuaian
                                </label>
                                <select
                                    v-model="searchForm.type"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="addition">➕ Penambahan</option>
                                    <option value="deduction">➖ Pengurangan</option>
                                </select>
                            </div>

                            <!-- Adjusted By -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Disesuaikan Oleh
                                </label>
                                <select
                                    v-model="searchForm.adjusted_by"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                >
                                    <option value="">Semua User</option>
                                    <option v-for="adjuster in adjusters" :key="adjuster.id" :value="adjuster.id">
                                        {{ adjuster.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Cari Produk
                                </label>
                                <input
                                    type="text"
                                    v-model="searchForm.search"
                                    placeholder="Nama atau barcode produk..."
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button
                                @click="applyFilters"
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                                Tampilkan
                            </button>

                            <button
                                @click="resetFilters"
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Reset
                            </button>

                            <button
                                @click="exportToExcel"
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tanggal & Waktu
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Produk
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tipe
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stok Sebelum
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Disesuaikan
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Stok Sesudah
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Disesuaikan Oleh
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Catatan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="adjustments.data.length === 0">
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-lg font-medium">Tidak ada data penyesuaian stok</p>
                                                <p class="text-sm">Coba ubah filter atau periode waktu</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="adjustment in adjustments.data" :key="adjustment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatDate(adjustment.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ adjustment.product?.name || '-' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ adjustment.product?.barcode || '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getTypeBadge(adjustment.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span class="mr-1">{{ getTypeIcon(adjustment.type) }}</span>
                                                {{ getTypeText(adjustment.type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            <span class="font-semibold">{{ adjustment.quantity_before }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="adjustment.type === 'addition' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-sm font-bold">
                                                {{ adjustment.type === 'addition' ? '+' : '-' }}{{ adjustment.quantity_adjusted }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            <span class="font-semibold">{{ adjustment.quantity_after }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <AuditInfo :user="adjustment.adjusted_by" :timestamp="adjustment.created_at" label="Disesuaikan" />
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ adjustment.notes || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="adjustments.data.length > 0" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan <span class="font-medium">{{ adjustments.from }}</span> sampai <span class="font-medium">{{ adjustments.to }}</span> dari <span class="font-medium">{{ adjustments.total }}</span> data
                            </div>
                            <div class="flex gap-2">
                                <template v-for="(link, index) in adjustments.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm rounded',
                                            link.active
                                                ? 'bg-indigo-500 text-white font-semibold'
                                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                                        ]"
                                        v-html="link.label"
                                        :preserve-state="true"
                                        :preserve-scroll="true"
                                    />
                                    <span
                                        v-else
                                        :class="'px-3 py-2 text-sm rounded bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 opacity-50 cursor-not-allowed'"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
