<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">💰 Laporan Penjualan</h2>
                <button @click="exportToExcel" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-xs uppercase">
                    📥 Export Excel
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                        <p class="text-sm opacity-90">Total Transaksi</p>
                        <p class="text-3xl font-bold">{{ summary.total_sales }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
                        <p class="text-sm opacity-90">Total Pendapatan</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(summary.total_revenue) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-lg shadow-lg">
                        <p class="text-sm opacity-90">Tunai</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(summary.cash_sales) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                        <p class="text-sm opacity-90">Saldo</p>
                        <p class="text-2xl font-bold">{{ formatCurrency(summary.balance_sales) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Filter Laporan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                            <select v-model="searchForm.payment_method" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Semua</option>
                                <option value="cash">Tunai</option>
                                <option value="balance">Saldo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Cari Siswa</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama/NIS..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                    </div>
                    <button @click="search" class="mt-4 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold">🔍 Tampilkan</button>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">QTY</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Metode</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ new Date(sale.created_at).toLocaleString('id-ID') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div v-if="sale.student" class="font-semibold">{{ sale.student.user.name }}</div>
                                    <div v-if="sale.student" class="text-xs text-gray-500">{{ sale.student.nis }} - {{ sale.student.kelas }}</div>
                                    <div v-else class="text-sm text-gray-500">-</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ sale.sale_items.map(i => i.product.name).join(', ') }}</td>
                                <td class="px-6 py-4 text-sm">{{ sale.sale_items.reduce((sum, i) => sum + i.quantity, 0) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="sale.payment_method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'" class="px-2 py-1 rounded text-xs font-semibold">
                                        {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-green-600">{{ formatCurrency(sale.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination -->
                    <div class="px-6 py-4 flex justify-between items-center border-t">
                        <div class="text-sm text-gray-600">Menampilkan {{ sales.from }} - {{ sales.to }} dari {{ sales.total }}</div>
                        <div class="flex gap-2">
                            <Link v-for="link in sales.links" :key="link.label" :href="link.url" :class="['px-3 py-2 text-sm rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 hover:bg-gray-50']" v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
