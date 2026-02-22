<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

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
    router.get(route(route().current()), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route(route().current() + '.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getStockBadge = (stock) => {
    if (stock === 0) return 'bg-rose-500/20 text-rose-400 border border-rose-500/30';
    if (stock <= 5) return 'bg-orange-500/20 text-orange-400 border border-orange-500/30';
    if (stock <= 10) return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
    return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
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

import ThermalPrintLayout from '@/Components/ThermalPrintLayout.vue';

const printThermal = () => {
    window.print();
};
</script>

<template>
    <Head title="Laporan Inventaris" />
    <AuthenticatedLayout>
        <template #mobileTitle>Laporan</template>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-white leading-tight">Laporan Inventaris</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-indigo-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Total Produk</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.total_products }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-emerald-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Nilai Stok</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_stock_value) }}</p>
                        <p class="text-xs opacity-80 mt-1">Harga Beli</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-cyan-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-blue-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Potensi Revenue</p>
                        <p class="text-base sm:text-2xl font-bold mt-1">{{ formatCurrency(summary.total_potential_revenue) }}</p>
                        <p class="text-xs opacity-80 mt-1">Jika semua terjual</p>
                    </div>
                    <div class="bg-gradient-to-br from-rose-500 to-red-600 text-white p-4 sm:p-6 rounded-xl border border-white/10 shadow-lg shadow-rose-500/20">
                        <p class="text-xs sm:text-sm opacity-90">Perlu Perhatian</p>
                        <p class="text-xl sm:text-3xl font-bold mt-1">{{ summary.out_of_stock + summary.low_stock }}</p>
                        <p class="text-xs opacity-80 mt-1">Habis: {{ summary.out_of_stock }}, Rendah: {{ summary.low_stock }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-slate-800/50 backdrop-blur-md p-4 sm:p-6 rounded-xl border border-white/10 shadow-xl">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-white">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Kategori</label>
                            <select v-model="searchForm.category_id" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors">
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Status Stok</label>
                            <select v-model="searchForm.stock_status" class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors">
                                <option value="">Semua Status</option>
                                <option value="out">Habis (0)</option>
                                <option value="low">Rendah (≤10)</option>
                                <option value="available">Tersedia (>10)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Cari Produk</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama produk..." class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors" />
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
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Harga Beli</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Harga Jual</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Nilai Stok</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Pot. Revenue</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Dibuat</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Diubah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-transparent divide-y divide-white/5">
                                <tr v-if="products.data.length === 0">
                                    <td colspan="10" class="px-6 py-12 text-center text-slate-500">
                                        Tidak ada data produk
                                    </td>
                                </tr>
                                <template v-for="product in products.data" :key="product.id">
                                    <tr class="hover:bg-slate-700/30 cursor-pointer transition-colors" @click="toggleExpand(product.id)">
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="expandedProduct === product.id ? 'rotate-90 text-indigo-400' : ''" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <div>
                                                    <div class="font-semibold text-white">{{ product.name }}</div>
                                                    <div class="text-xs text-slate-400">{{ product.barcode || '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-300">{{ product.category.name }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-bold" :class="product.stock === 0 ? 'text-rose-400' : product.stock <= 10 ? 'text-amber-400' : 'text-emerald-400'">
                                                {{ product.stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right text-slate-300">{{ formatCurrency(product.harga_beli) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-slate-300">{{ formatCurrency(product.harga_jual) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-400">
                                            {{ formatCurrency(product.stock * product.harga_beli) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right font-semibold text-emerald-400">
                                            {{ formatCurrency(product.stock * product.harga_jual) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStockBadge(product.stock)" class="px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                                {{ getStockText(product.stock) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <!-- Stock Movement History Row -->
                                    <tr v-if="expandedProduct === product.id" class="bg-slate-900/50">
                                        <td colspan="10" class="px-6 py-6 border-b border-white/5">
                                            <div class="space-y-4">
                                                <h4 class="font-semibold text-sm text-white mb-3">📊 History Pergerakan Stok</h4>

                                                <!-- Stock Adjustments -->
                                                <div v-if="product.stock_movements && product.stock_movements.adjustments && product.stock_movements.adjustments.length > 0">
                                                    <h5 class="text-xs font-semibold text-slate-300 mb-2">🔧 Penyesuaian Manual</h5>
                                                    <div class="overflow-x-auto rounded-lg border border-white/10">
                                                        <table class="min-w-full divide-y divide-white/5 text-xs">
                                                            <thead class="bg-slate-800/80">
                                                                <tr>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Tanggal</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Tipe</th>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Tujuan</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Qty</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Stok Sebelum</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Stok Sesudah</th>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Oleh</th>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-transparent divide-y divide-white/5">
                                                                <tr v-for="adj in product.stock_movements.adjustments" :key="adj.id" class="hover:bg-slate-700/30">
                                                                    <td class="px-3 py-2 text-slate-300">{{ formatDate(adj.created_at) }}</td>
                                                                    <td class="px-3 py-2 text-center">
                                                                        <span :class="adj.type === 'addition' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30'" class="px-2 py-1 border rounded text-xs font-medium">
                                                                            {{ adj.type === 'addition' ? '➕ Tambah' : '➖ Kurang' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-3 py-2 text-slate-300">{{ getPurposeLabel(adj.purpose) }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold" :class="adj.type === 'addition' ? 'text-emerald-400' : 'text-rose-400'">
                                                                        {{ adj.type === 'addition' ? '+' : '-' }}{{ adj.quantity_adjusted }}
                                                                    </td>
                                                                    <td class="px-3 py-2 text-center text-slate-300">{{ adj.quantity_before }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold text-white">{{ adj.quantity_after }}</td>
                                                                    <td class="px-3 py-2 text-slate-300">{{ adj.adjusted_by?.name || '-' }}</td>
                                                                    <td class="px-3 py-2 text-slate-400">{{ adj.notes || '-' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Sales Transactions -->
                                                <div v-if="product.stock_movements && product.stock_movements.sales && product.stock_movements.sales.length > 0" class="mt-4">
                                                    <h5 class="text-xs font-semibold text-slate-300 mb-2">💰 Transaksi Penjualan</h5>
                                                    <div class="overflow-x-auto rounded-lg border border-white/10">
                                                        <table class="min-w-full divide-y divide-white/5 text-xs">
                                                            <thead class="bg-slate-800/80">
                                                                <tr>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Tanggal</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Qty Terjual</th>
                                                                    <th class="px-3 py-2 text-right font-semibold text-slate-400 uppercase">Harga Jual</th>
                                                                    <th class="px-3 py-2 text-center font-semibold text-slate-400 uppercase">Metode</th>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Siswa</th>
                                                                    <th class="px-3 py-2 text-left font-semibold text-slate-400 uppercase">Kasir</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-transparent divide-y divide-white/5">
                                                                <tr v-for="(sale, idx) in product.stock_movements.sales" :key="idx" class="hover:bg-slate-700/30">
                                                                    <td class="px-3 py-2 text-slate-300">{{ formatDate(sale.created_at) }}</td>
                                                                    <td class="px-3 py-2 text-center font-semibold text-rose-400">-{{ sale.quantity }}</td>
                                                                    <td class="px-3 py-2 text-right text-slate-300">{{ formatCurrency(sale.price) }}</td>
                                                                    <td class="px-3 py-2 text-center">
                                                                        <span :class="sale.payment_method === 'cash' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30'" class="px-2 py-1 rounded text-xs font-medium">
                                                                            {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-3 py-2 text-slate-300">{{ sale.student_name || '-' }}</td>
                                                                    <td class="px-3 py-2 text-slate-300">{{ sale.cashier_name || '-' }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div v-if="(!product.stock_movements || (!product.stock_movements.adjustments || product.stock_movements.adjustments.length === 0) && (!product.stock_movements.sales || product.stock_movements.sales.length === 0))" class="text-center py-4 text-slate-500 text-xs">
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
                    <div v-if="products.links && products.links.length > 3" class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-white/10 bg-slate-900/30">
                        <div class="text-sm text-slate-400">
                            Menampilkan {{ products.from }} - {{ products.to }} dari {{ products.total }} produk
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="link in products.links" :key="link.label">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    :class="['px-4 py-2 text-sm rounded-lg transition-colors border', link.active ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border-white/5 text-slate-300 hover:bg-slate-600/50']"
                                    v-html="link.label"
                                    :preserve-scroll="true" />
                                <span v-else
                                    :class="['px-4 py-2 text-sm rounded-lg border border-white/5 bg-slate-800/30 text-slate-500 cursor-not-allowed opacity-50']"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thermal Print Layout -->
        <ThermalPrintLayout
            title="LAPORAN INVENTARIS"
            subtitle="Ringkasan Stok"
            :user="$page.props.auth.user"
        >
            <!-- Summary -->
            <div style="margin-bottom: 10px; border-bottom: 1px dashed black; padding-bottom: 5px;">
                <div style="font-weight: bold; font-size: 11px;">RINGKASAN</div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Produk:</span>
                    <span>{{ summary.total_products }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Nilai Stok:</span>
                    <span>{{ formatCurrency(summary.total_stock_value) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 9px; margin-top: 2px;">
                    <span>Habis: {{ summary.out_of_stock }}</span>
                    <span>Rendah: {{ summary.low_stock }}</span>
                </div>
            </div>

            <!-- List -->
            <div v-for="product in products.data" :key="product.id" style="margin-bottom: 8px; border-bottom: 1px dashed #ccc; padding-bottom: 4px;">
                <div style="font-weight: bold;">{{ product.name }}</div>
                <div style="font-size: 9px; margin-bottom: 1px;">{{ product.barcode || '-' }}</div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Stok: {{ product.stock }}</span>
                    <span>{{ formatCurrency(product.stock * product.harga_beli) }}</span>
                </div>
                <div style="font-size: 9px; color: #555;">
                    @ {{ formatCurrency(product.harga_beli) }}
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
