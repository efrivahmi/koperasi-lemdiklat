<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';

const props = defineProps({
    data: Object,
    expenses: Object,
    expensesByCategory: Object,
    filters: Object,
});

const searchForm = ref({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const search = () => {
    router.get(route('reports.financial'), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route('reports.financial.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatPercent = (value) => {
    return value.toFixed(2) + '%';
};
</script>

<template>
    <Head title="Laporan Keuangan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">Laporan Keuangan</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Date Filter -->
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-gray-900 dark:text-gray-100">Periode Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1 flex items-end">
                            <div class="w-full flex flex-col sm:flex-row gap-2">
                                <button @click="search" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Tampilkan
                                </button>
                                <button @click="exportToExcel" class="inline-flex items-center justify-center px-6 py-2.5 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Revenue Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 sm:p-6 lg:p-8 rounded-lg shadow-lg">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Total Pendapatan</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">💰</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.total_revenue) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">{{ data.total_transactions }} transaksi</p>
                    </div>

                    <!-- Gross Profit Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 sm:p-6 lg:p-8 rounded-lg shadow-lg">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Laba Kotor</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">📊</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.gross_profit) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">Revenue - COGS</p>
                    </div>

                    <!-- Net Profit Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 sm:p-6 lg:p-8 rounded-lg shadow-lg">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Laba Bersih</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">💎</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.net_profit) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">Margin: {{ formatPercent(data.profit_margin) }}</p>
                    </div>
                </div>

                <!-- Profit & Loss Statement -->
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow">
                    <h3 class="text-xl font-bold mb-6 text-gray-900 dark:text-gray-100 border-b pb-3">📋 Laporan Laba Rugi</h3>

                    <div class="space-y-4">
                        <!-- Revenue -->
                        <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">Pendapatan (Revenue)</span>
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(data.total_revenue) }}</span>
                        </div>

                        <!-- COGS -->
                        <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Harga Pokok Penjualan (COGS)</span>
                            <span class="text-xl font-bold text-red-600 dark:text-red-400">- {{ formatCurrency(data.total_cogs) }}</span>
                        </div>

                        <!-- Gross Profit -->
                        <div class="flex justify-between items-center py-3 bg-blue-50 dark:bg-blue-900/20 px-4 rounded">
                            <span class="font-bold text-gray-900 dark:text-gray-100">Laba Kotor</span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(data.gross_profit) }}</span>
                        </div>

                        <!-- Expenses -->
                        <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Biaya Operasional</span>
                            <span class="text-xl font-bold text-red-600 dark:text-red-400">- {{ formatCurrency(data.total_expenses) }}</span>
                        </div>

                        <!-- Net Profit -->
                        <div class="flex justify-between items-center py-4 bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 px-4 rounded-lg">
                            <div>
                                <span class="font-bold text-xl text-gray-900 dark:text-gray-100">Laba Bersih</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Profit Margin: {{ formatPercent(data.profit_margin) }}</p>
                            </div>
                            <span class="text-3xl font-bold" :class="data.net_profit >= 0 ? 'text-purple-600 dark:text-purple-400' : 'text-red-600 dark:text-red-400'">
                                {{ formatCurrency(data.net_profit) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Expenses Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Expenses by Category -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">📊 Pengeluaran per Kategori</h3>
                        <div class="space-y-3">
                            <div v-if="expensesByCategory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Tidak ada data pengeluaran
                            </div>
                            <div v-for="cat in expensesByCategory" :key="cat.category" class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ cat.category }}</span>
                                <span class="font-bold text-red-600 dark:text-red-400">{{ formatCurrency(cat.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Expenses -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100">💸 Pengeluaran Terbaru</h3>
                        <div class="overflow-x-auto max-h-96">
                            <table v-if="expenses.length > 0" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Deskripsi</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kategori</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jumlah</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Dibuat</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="expense in expenses.slice(0, 10)" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">
                                            {{ new Date(expense.expense_date).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ expense.description }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                            {{ expense.category }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-red-600 dark:text-red-400">
                                            {{ formatCurrency(expense.amount) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <AuditInfo :user="expense.creator" :timestamp="expense.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <AuditInfo :user="expense.updater" :timestamp="expense.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Tidak ada pengeluaran di periode ini
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-blue-700 dark:text-blue-300">
                            <p class="font-medium">Informasi Perhitungan:</p>
                            <p class="mt-1">• <strong>Laba Kotor</strong> = Total Pendapatan - Harga Pokok Penjualan (COGS)</p>
                            <p>• <strong>Laba Bersih</strong> = Laba Kotor - Total Biaya Operasional</p>
                            <p>• <strong>Profit Margin</strong> = (Laba Bersih / Total Pendapatan) × 100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
