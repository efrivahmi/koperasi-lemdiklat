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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="products.data.length === 0">
                                    <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data produk
                                    </td>
                                </tr>
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ product.barcode || '-' }}</div>
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
