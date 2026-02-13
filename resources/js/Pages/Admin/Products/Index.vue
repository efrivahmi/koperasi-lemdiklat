<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    products: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showAdjustmentModal = ref(false);
const selectedProduct = ref(null);

const adjustmentForm = useForm({
    quantity: '',
    type: 'deduction',
    purpose: 'other',
    notes: '',
});

watch(search, (value) => {
    router.get(route('products.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteProduct = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        router.delete(route('products.destroy', id));
    }
};

const openAdjustmentModal = (product) => {
    selectedProduct.value = product;
    adjustmentForm.reset();
    adjustmentForm.type = 'deduction';
    showAdjustmentModal.value = true;
};

const closeAdjustmentModal = () => {
    showAdjustmentModal.value = false;
    selectedProduct.value = null;
    adjustmentForm.reset();
};

const submitAdjustment = () => {
    adjustmentForm.post(route('products.adjust-stock', selectedProduct.value.id), {
        onSuccess: () => {
            closeAdjustmentModal();
        },
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Produk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Produk</h2>
        </template>

        <div class="py-6 sm:py-12 bg-gray-100 dark:bg-slate-900 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar -->
                <div class="mb-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-sm p-4 transition-colors duration-200">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    id="product-search"
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari produk (nama atau barcode)..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors duration-200"
                                    aria-label="Cari produk berdasarkan nama atau barcode"
                                />
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link v-if="can('products.barcode')" :href="route('products.barcode-generator')" class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-slate-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Generator Barcode
                            </Link>
                            <Link v-if="can('products.create')" :href="route('products.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Tambah Produk</span>
                                <span class="sm:hidden">Tambah</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="products.data.length === 0 && !search"
                    icon="box"
                    title="Belum Ada Produk"
                    description="Mulai tambahkan produk untuk mengelola inventaris koperasi."
                    :action-url="route('products.create')"
                    action-text="Tambah Produk Pertama"
                />

                <!-- Table -->
                <div v-else class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-slate-700 transition-colors duration-200">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead class="bg-gray-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Info Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stok</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img class="h-10 w-10 rounded-lg object-cover bg-gray-100 dark:bg-slate-700" :src="product.image_url || '/images/no-image.png'" :alt="product.name">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ product.name }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ product.barcode || '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                {{ product.category?.name || 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900 dark:text-white font-semibold">{{ formatCurrency(product.harga_jual) }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Beli: {{ formatCurrency(product.harga_beli) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">{{ product.stok }} {{ product.unit }}</div>
                                            <button v-if="can('products.stock')" @click="openAdjustmentModal(product)" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 underline">
                                                Sesuaikan
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="product.stok <= product.min_stok" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                Low Stock
                                            </span>
                                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                In Stock
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                            <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <Link :href="route('products.edit', product.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</Link>
                                                <button v-if="can('products.delete')" @click="deleteProduct(product.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0 && search">
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            Tidak ada produk yang cocok dengan pencarian "{{ search }}"
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in products.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded text-sm font-medium transition-colors duration-150',
                                        link.active 
                                            ? 'bg-indigo-600 text-white' 
                                            : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-600 border border-gray-300 dark:border-slate-600'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded text-sm font-medium bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-600 border border-gray-200 dark:border-slate-700 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div v-if="showAdjustmentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-slate-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity" aria-hidden="true" @click="closeAdjustmentModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-slate-700">
                    <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white" id="modal-title">
                                    Sesuaikan Stok: {{ selectedProduct?.name }}
                                </h3>
                                <div class="mt-4">
                                    <form @submit.prevent="submitAdjustment">
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Penyesuaian</label>
                                            <select v-model="adjustmentForm.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="addition">Penambahan (+)</option>
                                                <option value="deduction">Pengurangan (-)</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Jumlah</label>
                                            <input type="number" v-model="adjustmentForm.quantity" min="1" class="mt-1 block w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alasan</label>
                                            <select v-model="adjustmentForm.purpose" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="restock" v-if="adjustmentForm.type === 'addition'">Restock</option>
                                                <option value="correction" v-if="adjustmentForm.type === 'addition'">Koreksi Stok</option>
                                                <option value="return" v-if="adjustmentForm.type === 'addition'">Retur Pelanggan</option>
                                                <option value="damage" v-if="adjustmentForm.type === 'deduction'">Rusak/Expired</option>
                                                <option value="loss" v-if="adjustmentForm.type === 'deduction'">Hilang</option>
                                                <option value="usage" v-if="adjustmentForm.type === 'deduction'">Pemakaian Internal</option>
                                                <option value="correction" v-if="adjustmentForm.type === 'deduction'">Koreksi Stok</option>
                                                <option value="other">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Catatan (Opsional)</label>
                                            <textarea v-model="adjustmentForm.notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                        </div>
                                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Simpan
                                            </button>
                                            <button type="button" @click="closeAdjustmentModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
