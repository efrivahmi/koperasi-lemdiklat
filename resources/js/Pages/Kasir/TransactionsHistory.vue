<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    sales: Object,
    summary: Object,
    filters: Object,
    error: String,
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    payment_method: props.filters?.payment_method || '',
    status: props.filters?.status || '',
    search: props.filters?.search || '',
});

const applyFilters = () => {
    router.get(route('pos.transactions-history'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchForm.value = {
        date_from: '',
        date_to: '',
        payment_method: '',
        status: '',
        search: '',
    };
    applyFilters();
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

const getStatusBadge = (status) => {
    if (status === 'completed') {
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    }
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};

const getStatusText = (status) => {
    if (status === 'completed') {
        return 'Selesai';
    }
    return 'Dibatalkan';
};

const getPaymentBadge = (method) => {
    if (method === 'cash') {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
    }
    return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
};

const getPaymentText = (method) => {
    if (method === 'cash') {
        return 'Tunai';
    }
    return 'Saldo';
};

const printReceipt = (saleId) => {
    window.open(route('pos.receipt', saleId), '_blank');
};
</script>

<template>
    <Head title="Riwayat Transaksi Lengkap" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Riwayat Transaksi Lengkap
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        Semua transaksi dari awal operasi
                    </p>
                </div>
                <Link
                    :href="route('pos.index')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke POS
                </Link>
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <!-- Total Transactions -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-xs sm:text-sm font-medium">Total Transaksi</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.total_transactions }}</h3>
                                <p class="text-indigo-100 text-xs mt-1">Semua transaksi</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">🧾</div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-xs sm:text-sm font-medium">Total Pendapatan</p>
                                <h3 class="text-lg sm:text-2xl font-bold mt-1 sm:mt-2">{{ formatCurrency(summary.total_revenue) }}</h3>
                                <p class="text-green-100 text-xs mt-1">Transaksi selesai</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">💰</div>
                        </div>
                    </div>

                    <!-- Cash Transactions -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-xs sm:text-sm font-medium">Transaksi Tunai</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.cash_transactions }}</h3>
                                <p class="text-blue-100 text-xs mt-1">{{ formatCurrency(summary.cash_revenue) }}</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">💵</div>
                        </div>
                    </div>

                    <!-- Balance Transactions -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-xs sm:text-sm font-medium">Transaksi Saldo</p>
                                <h3 class="text-xl sm:text-3xl font-bold mt-1 sm:mt-2">{{ summary.balance_transactions }}</h3>
                                <p class="text-purple-100 text-xs mt-1">{{ formatCurrency(summary.balance_revenue) }}</p>
                            </div>
                            <div class="text-3xl sm:text-5xl opacity-50">💳</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="font-semibold text-base sm:text-lg mb-4">Filter & Pencarian</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
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

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Metode Pembayaran
                                </label>
                                <select
                                    v-model="searchForm.payment_method"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                >
                                    <option value="">Semua Metode</option>
                                    <option value="cash">Tunai</option>
                                    <option value="balance">Saldo</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status
                                </label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="completed">Selesai</option>
                                    <option value="voided">Dibatalkan</option>
                                </select>
                            </div>

                        </div>

                        <!-- Search -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pencarian
                            </label>
                            <input
                                type="text"
                                v-model="searchForm.search"
                                placeholder="Cari invoice ID, nama siswa, atau NIS..."
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                @keyup.enter="applyFilters"
                            />
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
                                Cari Transaksi
                            </button>

                            <button
                                @click="resetFilters"
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Reset Filter
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
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Pelanggan
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Kasir
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Metode
                                        </th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total
                                        </th>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="sales.data.length === 0">
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-lg font-medium">Tidak ada transaksi ditemukan</p>
                                                <p class="text-sm">Coba ubah filter atau periode waktu</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <!-- Invoice -->
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                #{{ String(sale.id).padStart(6, '0') }}
                                            </div>
                                        </td>
                                        <!-- Tanggal -->
                                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">
                                            {{ formatDate(sale.created_at) }}
                                        </td>
                                        <!-- Pelanggan -->
                                        <td class="px-3 py-3">
                                            <div v-if="sale.student" class="text-sm">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ sale.student.user.name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ sale.student.nis }} - {{ sale.student.kelas }}</div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                                Umum (Tunai)
                                            </div>
                                        </td>
                                        <!-- Kasir -->
                                        <td class="px-3 py-3 whitespace-nowrap text-xs">
                                            <div class="text-gray-900 dark:text-gray-100 font-medium">{{ sale.user?.name || '-' }}</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs uppercase">{{ sale.user?.role || '-' }}</div>
                                        </td>
                                        <!-- Metode -->
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span :class="getPaymentBadge(sale.payment_method)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                                {{ getPaymentText(sale.payment_method) }}
                                            </span>
                                        </td>
                                        <!-- Total -->
                                        <td class="px-3 py-3 whitespace-nowrap text-right">
                                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ formatCurrency(sale.total) }}
                                            </div>
                                        </td>
                                        <!-- Status -->
                                        <td class="px-3 py-3 whitespace-nowrap text-center">
                                            <span :class="getStatusBadge(sale.status)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">
                                                {{ getStatusText(sale.status) }}
                                            </span>
                                        </td>
                                        <!-- Aksi -->
                                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            <button
                                                @click="printReceipt(sale.id)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Cetak Struk"
                                            >
                                                🖨️
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="sales.data.length > 0" class="mt-6 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4 gap-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300 text-center sm:text-left">
                                Menampilkan <span class="font-medium">{{ sales.from }}</span> sampai <span class="font-medium">{{ sales.to }}</span> dari <span class="font-medium">{{ sales.total }}</span> transaksi
                            </div>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <template v-for="(link, index) in sales.links" :key="index">
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
