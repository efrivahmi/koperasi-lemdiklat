<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    sales: Object,
    summary: Object,
    filters: Object,
});

const searchForm = ref({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    payment_method: props.filters.payment_method || '',
    search: props.filters.search || '',
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

import ThermalPrintLayout from '@/Components/ThermalPrintLayout.vue';

const printThermal = () => {
    window.print();
};
</script>

<template>
    <Head title="Laporan Penjualan" />
    <AuthenticatedLayout>
        <template #mobileTitle>Laporan</template>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-white leading-tight">Laporan Penjualan</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-indigo-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Total Transaksi</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.total_sales }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-emerald-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Total Pendapatan</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_revenue) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-cyan-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-blue-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Tunai</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.cash_sales) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-pink-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-purple-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Saldo</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.balance_sales) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-slate-800/50 backdrop-blur-md p-4 sm:p-6 rounded-xl border border-white/10 shadow-xl">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-white">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Metode Pembayaran</label>
                            <select v-model="searchForm.payment_method" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors">
                                <option value="">Semua</option>
                                <option value="cash">Tunai</option>
                                <option value="balance">Saldo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Cari Siswa</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama/NIS..." class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
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

                <!-- Table -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Siswa</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Barang</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">QTY</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Metode</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Dibuat</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-transparent divide-y divide-white/5">
                                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ new Date(sale.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <div v-if="sale.student" class="font-semibold text-white">{{ sale.student.user.name }}</div>
                                        <div v-if="sale.student" class="text-xs text-slate-400">{{ sale.student.nis }} - {{ sale.student.kelas }}</div>
                                        <div v-else class="text-sm text-slate-500">-</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-300">{{ sale.sale_items.map(i => i.product.name).join(', ') }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-300">{{ sale.sale_items.reduce((sum, i) => sum + i.quantity, 0) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span :class="sale.payment_method === 'cash' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30'" class="px-2 py-1 rounded-lg border text-xs font-medium">
                                            {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-bold text-emerald-400">{{ formatCurrency(sale.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                        <AuditInfo :user="sale.creator" :timestamp="sale.created_at" label="Dibuat" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                        <AuditInfo :user="sale.updater" :timestamp="sale.updated_at" label="Diubah" />
                                    </td>
                                </tr>
                                <tr v-if="sales.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                        Tidak ada data penjualan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div v-if="sales.links && sales.links.length > 3" class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-white/10 bg-slate-900/30">
                        <div class="text-sm text-slate-400">Menampilkan {{ sales.from }} - {{ sales.to }} dari {{ sales.total }}</div>
                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="link in sales.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" :class="['px-4 py-2 text-sm rounded-lg transition-colors border', link.active ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border-white/5 text-slate-300 hover:bg-slate-600/50']" v-html="link.label" preserve-scroll />
                                <span v-else :class="'px-4 py-2 text-sm rounded-lg border border-white/5 bg-slate-800/30 text-slate-500 cursor-not-allowed opacity-50'" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thermal Print Layout -->
        <ThermalPrintLayout
            title="LAPORAN PENJUALAN"
            subtitle="Periode: Hari Ini"
            :user="$page.props.auth.user"
        >
            <!-- Summary -->
            <div style="margin-bottom: 10px; border-bottom: 1px dashed black; padding-bottom: 5px;">
                <div style="font-weight: bold; font-size: 11px;">RINGKASAN</div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Transaksi:</span>
                    <span>{{ summary.total_sales }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Pendapatan:</span>
                    <span>{{ formatCurrency(summary.total_revenue) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 9px; margin-top: 2px;">
                    <span>Tunai: {{ formatCurrency(summary.cash_sales) }}</span>
                    <span>Saldo: {{ formatCurrency(summary.balance_sales) }}</span>
                </div>
            </div>

            <!-- List -->
            <div v-for="sale in sales.data" :key="sale.id" style="margin-bottom: 8px; border-bottom: 1px dashed #ccc; padding-bottom: 4px;">
                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                    <span>#{{ sale.id }}</span>
                    <span>{{ formatCurrency(sale.total) }}</span>
                </div>
                <div style="font-size: 9px;">
                    {{ new Date(sale.created_at).toLocaleString('id-ID') }}
                </div>
                <div v-if="sale.student" style="font-size: 9px;">
                    Siswa: {{ sale.student.user.name }}
                </div>
                <div v-else style="font-size: 9px;">
                    Pelanggan: Umum
                </div>
                <div style="font-size: 9px;">
                    {{ sale.sale_items.length }} Item ({{ sale.sale_items.reduce((sum, i) => sum + i.quantity, 0) }} Pcs)
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
