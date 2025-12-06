<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';

const props = defineProps({
    products: Object,
    summary: Object,
    categories: Array,
    filters: Object,
});

const expandedProduct = ref(null);

const toggleExpand = (productId) => {
    expandedProduct.value = expandedProduct.value === productId ? null : productId;
};

const searchForm = ref({
    category_id: props.filters?.category_id || '',
    stock_status: props.filters?.stock_status || '',
    search: props.filters?.search || '',
});

const search = () => {
    router.get(route('reports.inventory'), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route('reports.inventory.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getStockBadge = (stock) => {
    if (stock === 0) return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    if (stock <= 5) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
    if (stock <= 10) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
};

const getStockText = (stock) => {
    if (stock === 0) return 'HABIS';
    if (stock <= 5) return 'KRITIS';
    if (stock <= 10) return 'RENDAH';
    return 'TERSEDIA';
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

const getPurposeLabel = (purpose) => {
    const labels = {
        'sale': 'Penjualan (Transaksi)',
        'internal_use': 'Keperluan Internal/Kantor',
        'personal_use': 'Keperluan Pribadi',
        'damage': 'Kerusakan Barang',
        'expired': 'Barang Kadaluarsa',
        'return_to_supplier': 'Retur ke Supplier',
        'other': 'Lainnya'
    };
    return labels[purpose] || 'Tidak Diketahui';
};
</script>

<template>
    <Head title="Laporan Inventaris" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">Laporan Inventaris</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Total Produk</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.total_products }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Nilai Stok</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_stock_value) }}</p>
                        <p class="text-xs opacity-80 mt-1">Harga Beli</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Potensi Revenue</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_potential_revenue) }}</p>
                        <p class="text-xs opacity-80 mt-1">Jika semua terjual</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-4 sm:p-6 rounded-lg shadow-lg">
                        <p class="text-xs sm:text-sm opacity-90">Perlu Perhatian</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.out_of_stock + summary.low_stock }}</p>
                        <p class="text-xs opacity-80 mt-1">Habis: {{ summary.out_of_stock }}, Rendah: {{ summary.low_stock }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-gray-900 dark:text-gray-100">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Kategori</label>
                            <select v-model="searchForm.category_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2">
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Status Stok</label>
                            <select v-model="searchForm.stock_status" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2">
                                <option value="">Semua Status</option>
                                <option value="out">Habis (0)</option>
                                <option value="low">Rendah (≤10)</option>
                                <option value="available">Tersedia (>10)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Cari Produk</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama produk..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm sm:text-base py-2" />
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stok</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Harga Beli</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Harga Jual</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nilai Stok</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Pot. Revenue</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Dibuat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="products.data.length === 0">
                                    <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data produk
                                    </td>
                                </tr>
                                <template v-for="product in products.data" :key="product.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="toggleExpand(product.id)">
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 transition-transform" :class="expandedProduct === product.id ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ product.barcode || '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ product.category.name }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-bold" :class="product.stock === 0 ? 'text-red-600' : product.stock <= 10 ? 'text-yellow-600' : 'text-green-600'">
                                                {{ product.stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">{{ formatCurrency(product.harga_beli) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100">{{ formatCurrency(product.harga_jual) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-blue-600 dark:text-blue-400">
                                            {{ formatCurrency(product.stock * product.harga_beli) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(product.stock * product.harga_jual) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStockBadge(product.stock)" class="px-3 py-1 rounded-full text-xs font-bold">
                                                {{ getStockText(product.stock) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <!-- Stock Movement History Row -->
                                    <tr v-if="expandedProduct === product.id" class="bg-gray-50 dark:bg-gray-900">
                                        <td colspan="10" class="px-6 py-4">
                                            <div class="space-y-4">
                                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 mb-3">📊 History Pergerakan Stok</h4>

                                                <!-- Stock Adjustments -->
                                                <div v-if="product.stock_movements && product.stock_movements.adjustments && product.stock_movements.adjustments.length > 0">
                                                    <h5 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">🔧 Penyesuaian Manual</h5>
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                                            <thead class="bg-gray-100 dark:bg-gray-800">
                                                                <tr>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Tipe</th>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tujuan</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Stok Sebelum</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Stok Sesudah</th>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Oleh</th>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                                <tr v-for="adj in product.stock_movements.adjustments" :key="adj.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ formatDate(adj.created_at) }}</td>
                                                                    <td class="px-3 py-2 text-center">
                                                                        <span :class="adj.type === 'addition' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'" class="px-2 py-1 rounded text-xs font-medium">
                                                                            {{ adj.type === 'addition' ? '➕ Tambah' : '➖ Kurang' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ getPurposeLabel(adj.purpose) }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold" :class="adj.type === 'addition' ? 'text-green-600' : 'text-red-600'">
                                                                        {{ adj.type === 'addition' ? '+' : '-' }}{{ adj.quantity_adjusted }}
                                                                    </td>
                                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-gray-100">{{ adj.quantity_before }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold text-gray-900 dark:text-gray-100">{{ adj.quantity_after }}</td>
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ adj.adjusted_by?.name || '-' }}</td>
                                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ adj.notes || '-' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Sales Transactions -->
                                                <div v-if="product.stock_movements && product.stock_movements.sales && product.stock_movements.sales.length > 0" class="mt-4">
                                                    <h5 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">💰 Transaksi Penjualan</h5>
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                                                            <thead class="bg-gray-100 dark:bg-gray-800">
                                                                <tr>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Qty Terjual</th>
                                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Harga Jual</th>
                                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Metode</th>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Siswa</th>
                                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Kasir</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                                <tr v-for="(sale, idx) in product.stock_movements.sales" :key="idx" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ formatDate(sale.created_at) }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold text-red-600">-{{ sale.quantity }}</td>
                                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-100">{{ formatCurrency(sale.price) }}</td>
                                                                    <td class="px-3 py-2 text-center">
                                                                        <span :class="sale.payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'" class="px-2 py-1 rounded text-xs font-medium">
                                                                            {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ sale.student_name || '-' }}</td>
                                                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ sale.cashier_name || '-' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div v-if="(!product.stock_movements || (!product.stock_movements.adjustments || product.stock_movements.adjustments.length === 0) && (!product.stock_movements.sales || product.stock_movements.sales.length === 0))" class="text-center py-4 text-gray-500 dark:text-gray-400 text-xs">
                                                    Belum ada riwayat pergerakan stok
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links && products.links.length > 3" class="px-6 py-4 flex justify-between items-center border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan {{ products.from }} - {{ products.to }} dari {{ products.total }} produk
                        </div>
                        <div class="flex gap-2">
                            <template v-for="link in products.links" :key="link.label">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    :class="['px-3 py-2 text-sm rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600']"
                                    v-html="link.label"
                                    :preserve-scroll="true" />
                                <span v-else
                                    :class="['px-3 py-2 text-sm rounded bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-50']"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
