<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, reactive } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    stock_ins: Object,
    filters: Object,
    selectedProduct: Object,
});

const filters = reactive({
    product_id: props.filters.product_id || '',
    search: props.filters.search || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showDateFilter = ref(false);

// Watch for changes and fetch data
watch(filters, (newFilters) => {
    // Debounce or immediate depending on field? 
    // For Select and Date, immediate is usually fine.
    // Clean up empty values
    const query = {};
    for (const key in newFilters) {
        if (newFilters[key]) {
            query[key] = newFilters[key];
        }
    }

    router.get(route('kasir.stock-ins.index'), query, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

const resetFilters = () => {
    filters.product_id = '';
    filters.search = '';
    filters.start_date = '';
    filters.end_date = '';
    showDateFilter.value = false;
};

const deleteStockIn = (id) => {
    if (confirm('Hapus data barang masuk? Stok akan dikurangi kembali.')) {
        router.delete(route('kasir.stock-ins.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Stok Masuk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Stok Masuk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Stok Masuk</h2>
        </template>

        <div class="max-w-7xl mx-auto text-white">
                <!-- Toolbar Section -->
                <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                         <!-- Search & Filter -->
                        <div class="flex-1 max-w-2xl flex gap-3">
                            <div class="flex-1">
                                <SearchableSelect
                                    v-model="filters.product_id"
                                    :api-url="route('api.products.search')"
                                    :options="props.selectedProduct ? [props.selectedProduct] : []"
                                    placeholder="Cari Produk (Ketik nama/barcode)..."
                                    search-placeholder="Ketik nama atau barcode..."
                                    class="w-full"
                                />
                            </div>
                            
                            <!-- Date Filter Button -->
                            <div class="relative">
                                <button 
                                    @click="showDateFilter = !showDateFilter"
                                    class="px-4 py-2.5 bg-slate-900/50 border border-slate-700 text-white rounded-xl hover:bg-slate-800 focus:ring-2 focus:ring-indigo-500 transition-all flex items-center gap-2"
                                    :class="{'border-indigo-500 ring-1 ring-indigo-500': showDateFilter || filters.start_date || filters.end_date}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Filter Tanggal</span>
                                </button>

                                <!-- Date Filter Dropdown -->
                                <div v-if="showDateFilter" class="absolute z-50 top-full right-0 mt-2 w-72 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl p-4">
                                    <h4 class="text-sm font-semibold text-white mb-3">Filter Tanggal</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-xs text-slate-400 mb-1 block">Dari Tanggal</label>
                                            <input 
                                                v-model="filters.start_date"
                                                type="date" 
                                                class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="text-xs text-slate-400 mb-1 block">Sampai Tanggal</label>
                                            <input 
                                                v-model="filters.end_date"
                                                type="date" 
                                                class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <div class="pt-2 border-t border-slate-700 flex justify-between">
                                            <button 
                                                @click="resetFilters"
                                                class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                            >
                                                Reset Filter
                                            </button>
                                            <button 
                                                @click="showDateFilter = false"
                                                class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                                            >
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <Link v-if="can('stock_ins.create')" :href="route('kasir.stock-ins.create')" class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-200 transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Tambah Stok Masuk</span>
                            <span class="sm:hidden">Tambah</span>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="stock_ins.data.length === 0 && !filters.product_id && !filters.search && !filters.start_date && !filters.end_date"
                    icon="box"
                    title="Belum Ada Barang Masuk"
                    description="Catat barang masuk untuk tracking inventaris koperasi."
                    :action-url="route('kasir.stock-ins.create')"
                    action-text="Tambah Barang Masuk Pertama"
                    class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-12"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-white/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Harga (Beli / Jual)</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Supplier</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Audit</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="stock in stock_ins.data" :key="stock.id" class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ formatDate(stock.created_at) }}</div>
                                        <div class="text-xs text-slate-400">
                                            {{ new Date(stock.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-white">{{ stock.product.name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ stock.product.barcode }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                            +{{ stock.quantity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-1">
                                            <div class="text-slate-300">
                                                <span class="text-xs text-slate-500 w-8 inline-block">Beli:</span>
                                                {{ formatCurrency(stock.product.harga_beli) }}
                                            </div>
                                            <div class="text-emerald-300">
                                                <span class="text-xs text-emerald-500/70 w-8 inline-block">Jual:</span>
                                                {{ formatCurrency(stock.product.harga_jual) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-400">{{ formatCurrency(stock.quantity * stock.product.harga_beli) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ stock.supplier || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <AuditInfo :user="stock.creator" :timestamp="stock.created_at" label="Oleh" class="text-xs" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link v-if="can('stock_ins.show')" :href="route('kasir.stock-ins.show', stock.id)" class="p-2 text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 rounded-lg transition-colors" title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </Link>
                                            <Link v-if="can('stock_ins.edit')" :href="route('kasir.stock-ins.edit', stock.id)" class="p-2 text-amber-400 hover:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 rounded-lg transition-colors" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-.5-9l4.5 4.5M6 17v4m-1-2h2" /></svg>
                                            </Link>
                                            <button v-if="can('stock_ins.delete')" @click="deleteStockIn(stock.id)" class="p-2 text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="stock_ins.data.length === 0 && (filters.product_id || filters.search || filters.start_date || filters.end_date)">
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="text-slate-400">
                                            <svg class="mx-auto h-12 w-12 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                            <h3 class="text-sm font-medium text-white mb-1">Tidak ada hasil</h3>
                                            <p class="text-sm text-slate-500">Tidak ditemukan barang masuk dengan kriteria pencarian yang dipilih.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="stock_ins.links.length > 3" class="px-6 py-4 bg-white/5 border-t border-white/10 flex justify-center">
                        <div class="flex gap-1">
                             <template v-for="link in stock_ins.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm font-medium rounded-lg transition-all"
                                    :class="link.active 
                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                        : 'text-slate-400 hover:text-white hover:bg-white/10'"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-2 text-sm font-medium text-slate-600 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            <!-- End Content -->
        </div>
    </AuthenticatedLayout>
</template>
