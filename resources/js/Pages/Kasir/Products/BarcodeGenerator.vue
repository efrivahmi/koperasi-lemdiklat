<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedProducts = ref(new Set());
const selectAll = ref(false);
const labelQuantity = ref(1);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        props.products.data.forEach(product => {
            selectedProducts.value.add(product.id);
        });
    } else {
        selectedProducts.value.clear();
    }
};

const toggleProduct = (productId) => {
    if (selectedProducts.value.has(productId)) {
        selectedProducts.value.delete(productId);
    } else {
        selectedProducts.value.add(productId);
    }
    selectAll.value = selectedProducts.value.size === props.products.data.length;
};

const isSelected = (productId) => {
    return selectedProducts.value.has(productId);
};

const totalLabels = computed(() => {
    return selectedProducts.value.size * labelQuantity.value;
});

const handleSearch = () => {
    router.get(route('kasir.products.barcode-generator'), {
        search: search.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const generateBarcodes = () => {
    if (selectedProducts.value.size === 0) {
        alert('Pilih minimal 1 produk untuk generate barcode');
        return;
    }

    // Build URL with query parameters instead of POST
    const productIds = Array.from(selectedProducts.value);
    const params = new URLSearchParams();

    productIds.forEach(id => {
        params.append('product_ids[]', id);
    });
    params.append('quantity', labelQuantity.value);

    // Open in new window with GET request
    window.open(
        route('kasir.products.print-barcodes') + '?' + params.toString(),
        '_blank',
        'width=1200,height=800'
    );
};
</script>

<template>
    <Head title="Generate Barcode Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Generate Barcode</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Generate Barcode</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                
                <!-- Page Controls -->
                <div class="flex justify-between items-center mb-6">
                    <div></div> <!-- Spacer -->
                    <Link
                        :href="route('kasir.products.index')"
                        class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-lg transition-colors border border-white/10 shadow-lg"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke Produk
                    </Link>
                </div>

                <!-- Top Controls -->
                <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-300 mb-2">
                                🔍 Cari Produk
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        v-model="search"
                                        @keyup.enter="handleSearch"
                                        type="text"
                                        placeholder="Cari nama produk atau barcode..."
                                        class="w-full pl-4 pr-10 py-2.5 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    />
                                    <button 
                                        @click="handleSearch"
                                        class="absolute right-2 top-1.5 p-1 text-slate-400 hover:text-white"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Label Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">
                                🏷️ Jumlah Label per Produk
                            </label>
                            <input
                                v-model.number="labelQuantity"
                                type="number"
                                min="1"
                                max="20"
                                class="w-full px-4 py-2.5 bg-slate-900/50 border border-slate-600 rounded-lg text-white font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            />
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mt-6 p-4 bg-indigo-900/30 border border-indigo-500/30 rounded-xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-500/5 pattern-grid-lg"></div>
                        <div class="relative flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="text-indigo-200">
                                <span class="text-white font-bold text-lg">{{ selectedProducts.size }}</span> produk dipilih × <span class="text-white font-bold text-lg">{{ labelQuantity }}</span> label =
                                <span class="text-white font-bold text-xl ml-2">{{ totalLabels }}</span> <span class="text-sm opacity-80">label total</span>
                            </div>
                            <button
                                @click="generateBarcodes"
                                :disabled="selectedProducts.size === 0"
                                class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Generate & Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/5">
                        <!-- Select All -->
                        <div class="flex items-center gap-3">
                            <input
                                v-model="selectAll"
                                @change="toggleSelectAll"
                                type="checkbox"
                                class="w-5 h-5 rounded border-slate-600 text-indigo-600 focus:ring-indigo-500 bg-slate-900/50"
                            />
                            <label class="text-sm font-medium text-slate-300 select-none cursor-pointer" @click="selectAll = !selectAll; toggleSelectAll()">
                                Pilih Semua Produk di Halaman Ini
                            </label>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-300">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-900/50 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-4 font-medium tracking-wider w-16">Pilih</th>
                                    <th class="px-6 py-4 font-medium tracking-wider">Produk</th>
                                    <th class="px-6 py-4 font-medium tracking-wider">Barcode</th>
                                    <th class="px-6 py-4 font-medium tracking-wider">Kategori</th>
                                    <th class="px-6 py-4 font-medium tracking-wider text-right">Harga</th>
                                    <th class="px-6 py-4 font-medium tracking-wider text-right">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr
                                    v-for="product in products.data"
                                    :key="product.id"
                                    class="hover:bg-slate-700/30 transition-colors cursor-pointer"
                                    :class="{ 'bg-indigo-900/20': isSelected(product.id) }"
                                    @click="toggleProduct(product.id)"
                                >
                                    <td class="px-6 py-4" @click.stop>
                                        <input
                                            :checked="isSelected(product.id)"
                                            @change="toggleProduct(product.id)"
                                            type="checkbox"
                                            class="w-5 h-5 rounded border-slate-600 text-indigo-600 focus:ring-indigo-500 bg-slate-900/50"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-white">{{ product.name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-indigo-300 bg-indigo-900/30 px-2 py-0.5 rounded inline-block text-xs border border-indigo-500/20">
                                            {{ product.barcode || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                         <span 
                                            v-if="product.category"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                        >
                                            {{ product.category.name }}
                                        </span>
                                        <span v-else class="text-slate-500 italic text-xs">Tanpa Kategori</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-amber-400 font-medium">
                                            {{ formatCurrency(product.harga_jual) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                            :class="product.stock <= 5 
                                                ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' 
                                                : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'"
                                        >
                                            {{ product.stock }} {{ product.unit || 'Unit' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex justify-center">
                         <div class="flex gap-1" v-if="products.links && products.links.length > 3">
                            <template v-for="(link, k) in products.links" :key="k">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-colors"
                                    :class="[
                                        link.active 
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white border border-white/5'
                                    ]"
                                    preserve-state
                                    preserve-scroll
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs font-medium rounded-md bg-slate-900/50 text-slate-600 border border-white/5 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.data.length === 0" class="text-center py-16">
                        <div class="rounded-full bg-slate-800/50 p-6 inline-flex mb-4">
                            <svg class="h-10 w-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-white mb-2">Produk tidak ditemukan</h3>
                        <p class="text-slate-400 max-w-sm mx-auto">
                            Tidak ada produk yang cocok dengan pencarian "{{ search }}"
                        </p>
                         <button @click="search = ''; handleSearch()" class="mt-4 text-indigo-400 hover:text-indigo-300 font-medium text-sm">
                            Bersihkan pencarian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
