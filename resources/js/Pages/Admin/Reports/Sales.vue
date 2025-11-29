<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';

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
    router.get(route('reports.sales'), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route('reports.sales.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Laporan Penjualan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">Laporan Penjualan</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Total Transaksi</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.total_sales }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Total Pendapatan</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_revenue) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Tunai</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.cash_sales) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Saldo</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.balance_sales) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-gray-800 dark:text-gray-200">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Metode Pembayaran</label>
                            <select v-model="searchForm.payment_method" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2">
                                <option value="">Semua</option>
                                <option value="cash">Tunai</option>
                                <option value="balance">Saldo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Cari Siswa</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama/NIS..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
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

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">QTY</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Metode</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ new Date(sale.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <div v-if="sale.student" class="font-semibold text-gray-900 dark:text-gray-100">{{ sale.student.user.name }}</div>
                                        <div v-if="sale.student" class="text-xs text-gray-500 dark:text-gray-400">{{ sale.student.nis }} - {{ sale.student.kelas }}</div>
                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ sale.sale_items.map(i => i.product.name).join(', ') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ sale.sale_items.reduce((sum, i) => sum + i.quantity, 0) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span :class="sale.payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'" class="px-2 py-1 rounded text-xs font-semibold">
                                            {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-bold text-green-600 dark:text-green-400">{{ formatCurrency(sale.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <AuditInfo :user="sale.creator" :timestamp="sale.created_at" label="Dibuat" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <AuditInfo :user="sale.updater" :timestamp="sale.updated_at" label="Diubah" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="px-6 py-4 flex justify-between items-center border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Menampilkan {{ sales.from }} - {{ sales.to }} dari {{ sales.total }}</div>
                        <div class="flex gap-2">
                            <template v-for="link in sales.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" :class="['px-3 py-2 text-sm rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600']" v-html="link.label" />
                                <span v-else :class="'px-3 py-2 text-sm rounded bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed'" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
