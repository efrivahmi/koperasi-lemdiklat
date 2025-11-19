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
    router.get(route('products.barcode-generator'), {
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

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('products.print-barcodes');
    form.target = '_blank';

    // CSRF Token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.appendChild(csrfInput);

    // Product IDs
    selectedProducts.value.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'product_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    // Quantity
    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = labelQuantity.value;
    form.appendChild(qtyInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};
</script>

<template>
    <Head title="Generate Barcode" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Generate Barcode Produk
                </h2>
                <Link
                    :href="route('products.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    ← Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Top Controls -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🔍 Cari Produk
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="search"
                                    @keyup.enter="handleSearch"
                                    type="text"
                                    placeholder="Cari nama produk atau barcode..."
                                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    @click="handleSearch"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    Cari
                                </button>
                            </div>
                        </div>

                        <!-- Label Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                🏷️ Jumlah Label per Produk
                            </label>
                            <input
                                v-model.number="labelQuantity"
                                type="number"
                                min="1"
                                max="20"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mt-6 p-4 bg-indigo-50 dark:bg-indigo-900 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-indigo-800 dark:text-indigo-200">
                                <strong>{{ selectedProducts.size }}</strong> produk dipilih × <strong>{{ labelQuantity }}</strong> label =
                                <strong class="text-lg">{{ totalLabels }}</strong> label total
                            </div>
                            <button
                                @click="generateBarcodes"
                                :disabled="selectedProducts.size === 0"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white rounded-lg font-semibold transition-colors disabled:cursor-not-allowed"
                            >
                                🖨️ Generate & Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Select All -->
                        <div class="mb-4 flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <input
                                v-model="selectAll"
                                @change="toggleSelectAll"
                                type="checkbox"
                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pilih Semua Produk di Halaman Ini
                            </label>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Pilih
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Produk
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Barcode
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Kategori
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Harga
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Stok
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="product in products.data"
                                        :key="product.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        :class="{ 'bg-indigo-50 dark:bg-indigo-900': isSelected(product.id) }"
                                    >
                                        <td class="px-6 py-4">
                                            <input
                                                :checked="isSelected(product.id)"
                                                @change="toggleProduct(product.id)"
                                                type="checkbox"
                                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ product.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded inline-block">
                                                {{ product.barcode }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                {{ product.category?.name || 'Tanpa Kategori' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                {{ formatCurrency(product.harga_jual) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="[
                                                    product.stock === 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                                    product.stock <= 5 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                ]"
                                            >
                                                {{ product.stock }} unit
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.links && products.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="flex gap-2">
                                <Link
                                    v-for="(link, index) in products.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    v-html="link.label"
                                    preserve-state
                                    preserve-scroll
                                >
                                </Link>
                            </nav>
                        </div>

                        <!-- Empty State -->
                        <div v-if="products.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Produk tidak ditemukan</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Coba gunakan kata kunci pencarian yang berbeda
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
