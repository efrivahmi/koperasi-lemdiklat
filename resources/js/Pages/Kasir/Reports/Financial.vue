<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

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
    router.get(route(route().current()), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route(route().current() + '.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatPercent = (value) => {
    return value.toFixed(2) + '%';
};

import ThermalPrintLayout from '@/Components/ThermalPrintLayout.vue';

const printThermal = () => {
    window.print();
};
</script>

<template>
    <Head title="Laporan Keuangan" />
    <AuthenticatedLayout>
        <template #mobileTitle>Laporan</template>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-white leading-tight">Laporan Keuangan</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Date Filter -->
                <div class="bg-slate-800/50 backdrop-blur-md p-4 sm:p-6 rounded-xl shadow-xl border border-white/10">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-white">Periode Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <button @click="search" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Tampilkan
                        </button>
                        <button v-if="can('reports.print')" @click="printThermal" class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-700 hover:bg-slate-600 border border-white/10 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Thermal
                        </button>
                        <button v-if="can('reports.export')" @click="exportToExcel" class="inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Financial Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Revenue Card -->
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-4 sm:p-6 lg:p-8 rounded-xl shadow-lg shadow-emerald-500/20 border border-white/10">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Total Pendapatan</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">💰</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.total_revenue) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">{{ data.total_transactions }} transaksi</p>
                    </div>

                    <!-- Gross Profit Card -->
                    <div class="bg-gradient-to-br from-indigo-500 to-blue-600 text-white p-4 sm:p-6 lg:p-8 rounded-xl shadow-lg shadow-indigo-500/20 border border-white/10">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Laba Kotor</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">📊</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.gross_profit) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">Revenue - COGS</p>
                    </div>

                    <!-- Net Profit Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-pink-600 text-white p-4 sm:p-6 lg:p-8 rounded-xl shadow-lg shadow-purple-500/20 border border-white/10">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold opacity-90">Laba Bersih</h3>
                            <span class="text-2xl sm:text-3xl lg:text-4xl">💎</span>
                        </div>
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold mb-1 sm:mb-2">{{ formatCurrency(data.net_profit) }}</p>
                        <p class="text-xs sm:text-sm opacity-80">Margin: {{ formatPercent(data.profit_margin) }}</p>
                    </div>
                </div>

                <!-- Profit & Loss Statement -->
                <div class="bg-slate-800/50 backdrop-blur-md p-8 rounded-xl shadow-lg border border-white/10">
                    <h3 class="text-xl font-bold mb-6 text-white border-b border-white/10 pb-3">📋 Laporan Laba Rugi</h3>

                    <div class="space-y-4">
                        <!-- Revenue -->
                        <div class="flex justify-between items-center py-3 border-b border-white/5">
                            <span class="font-semibold text-slate-300">Pendapatan (Revenue)</span>
                            <span class="text-xl font-bold text-emerald-400">{{ formatCurrency(data.total_revenue) }}</span>
                        </div>

                        <!-- COGS -->
                        <div class="flex justify-between items-center py-3 border-b border-white/5">
                            <span class="font-semibold text-slate-400">Harga Pokok Penjualan (COGS)</span>
                            <span class="text-xl font-bold text-rose-400">- {{ formatCurrency(data.total_cogs) }}</span>
                        </div>

                        <!-- Gross Profit -->
                        <div class="flex justify-between items-center py-3 bg-indigo-500/10 border border-indigo-500/20 px-4 rounded-xl mt-2">
                            <span class="font-bold text-indigo-300">Laba Kotor</span>
                            <span class="text-2xl font-bold text-indigo-400">{{ formatCurrency(data.gross_profit) }}</span>
                        </div>

                        <!-- Expenses -->
                        <div class="flex justify-between items-center py-3 border-b border-white/5 mt-2">
                            <span class="font-semibold text-slate-400">Biaya Operasional</span>
                            <span class="text-xl font-bold text-rose-400">- {{ formatCurrency(data.total_expenses) }}</span>
                        </div>

                        <!-- Net Profit -->
                        <div class="flex justify-between items-center py-4 bg-gradient-to-r from-purple-500/20 to-pink-500/20 shadow-inner border border-purple-500/30 px-4 rounded-xl mt-4">
                            <div>
                                <span class="font-bold text-xl text-white">Laba Bersih</span>
                                <p class="text-sm text-purple-300 mt-1">Profit Margin: {{ formatPercent(data.profit_margin) }}</p>
                            </div>
                            <span class="text-3xl font-bold" :class="data.net_profit >= 0 ? 'text-purple-400' : 'text-rose-400'">
                                {{ formatCurrency(data.net_profit) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Expenses Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Expenses by Category -->
                    <div class="bg-slate-800/50 backdrop-blur-md p-6 rounded-xl shadow-lg border border-white/10">
                        <h3 class="text-lg font-bold mb-4 text-white">📊 Pengeluaran per Kategori</h3>
                        <div class="space-y-3">
                            <div v-if="expensesByCategory.length === 0" class="text-center py-8 text-slate-500">
                                Tidak ada data pengeluaran
                            </div>
                            <div v-for="cat in expensesByCategory" :key="cat.category" class="flex justify-between items-center p-3 bg-slate-900/50 border border-white/5 rounded-lg transition-colors hover:border-white/10">
                                <span class="font-semibold text-slate-200">{{ cat.category }}</span>
                                <span class="font-bold text-rose-400">{{ formatCurrency(cat.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Expenses -->
                    <div class="bg-slate-800/50 backdrop-blur-md p-6 rounded-xl shadow-lg border border-white/10">
                        <h3 class="text-lg font-bold mb-4 text-white">💸 Pengeluaran Terbaru</h3>
                        <div class="overflow-x-auto max-h-96 rounded-lg border border-white/10">
                            <table v-if="expenses.length > 0" class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Kategori</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-transparent divide-y divide-white/5">
                                    <tr v-for="expense in expenses.slice(0, 10)" :key="expense.id" class="hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-300">
                                            {{ new Date(expense.expense_date).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-white">
                                            {{ expense.description }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-400">
                                            {{ expense.category }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-rose-400">
                                            {{ formatCurrency(expense.amount) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-400">
                                            <AuditInfo :user="expense.creator" :timestamp="expense.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-400">
                                            <AuditInfo :user="expense.updater" :timestamp="expense.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center py-8 text-slate-500">
                                Tidak ada pengeluaran di periode ini
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-indigo-500/10 border-l-4 border-indigo-500 p-4 rounded-xl shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-indigo-200">
                            <p class="font-medium text-indigo-100">Informasi Perhitungan:</p>
                            <p class="mt-1 opacity-90">• <strong>Laba Kotor</strong> = Total Pendapatan - Harga Pokok Penjualan (COGS)</p>
                            <p class="opacity-90">• <strong>Laba Bersih</strong> = Laba Kotor - Total Biaya Operasional</p>
                            <p class="opacity-90">• <strong>Profit Margin</strong> = (Laba Bersih / Total Pendapatan) × 100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thermal Print Layout -->
        <ThermalPrintLayout
            title="LAPORAN KEUANGAN"
            subtitle="Ringkasan Profit/Loss"
            :user="$page.props.auth.user"
        >
            <div style="border-bottom: 1px dashed black; padding-bottom: 10px; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-weight: bold;">Pendapatan</span>
                    <span style="font-weight: bold;">{{ formatCurrency(data.total_revenue) }}</span>
                </div>
                <div style="font-size: 9px; color: #555; margin-bottom: 8px;">
                    Total Transaksi: {{ data.total_transactions }}
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>COGS</span>
                    <span>- {{ formatCurrency(data.total_cogs) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: bold; border-top: 1px solid #ddd; padding-top: 2px;">
                    <span>Laba Kotor</span>
                    <span>{{ formatCurrency(data.gross_profit) }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>Biaya Ops.</span>
                    <span>- {{ formatCurrency(data.total_expenses) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; border-top: 2px solid black; padding-top: 4px; font-weight: bold; font-size: 13px;">
                    <span>LABA BERSIH</span>
                    <span>{{ formatCurrency(data.net_profit) }}</span>
                </div>
                 <div style="font-size: 9px; text-align: right; margin-top: 2px;">
                    Margin: {{ formatPercent(data.profit_margin) }}
                </div>
            </div>

            <div v-if="expensesByCategory.length > 0">
                 <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px; text-align: center;">PENGELUARAN PER KATEGORI</div>
                 <div v-for="cat in expensesByCategory" :key="cat.category" style="display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 2px;">
                    <span>{{ cat.category }}</span>
                    <span>{{ formatCurrency(cat.total) }}</span>
                 </div>
            </div>
        </ThermalPrintLayout>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .thermal-print-container, .thermal-print-container * {
        visibility: visible;
    }
    .thermal-print-container {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
