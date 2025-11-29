<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Produk</h2>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <Link :href="route('products.barcode-generator')" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 sm:px-4 rounded flex items-center gap-2 text-sm sm:text-base flex-1 sm:flex-initial justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span class="hidden sm:inline">Cetak Barcode</span>
                        <span class="sm:hidden">Barcode</span>
                    </Link>
                    <Link :href="route('products.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base flex-1 sm:flex-initial text-center">
                        <span class="hidden sm:inline">Tambah Produk</span>
                        <span class="sm:hidden">+ Produk</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <EmptyState
                    v-if="products.data.length === 0 && !search"
                    icon="box"
                    title="Belum Ada Produk"
                    description="Mulai tambahkan produk pertama Anda untuk ditampilkan di katalog koperasi. Produk yang ditambahkan dapat langsung dijual di sistem POS."
                    :action-url="route('products.create')"
                    action-text="Tambah Produk Pertama"
                />

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-3 sm:p-6 text-gray-900">
                        <div class="mb-4">
                            <label for="product-search" class="sr-only">Cari Produk</label>
                            <input
                                id="product-search"
                                v-model="search"
                                type="text"
                                placeholder="Cari produk (nama atau barcode)..."
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm sm:text-base"
                                aria-label="Cari produk berdasarkan nama atau barcode"
                            />
                        </div>

                        <div class="overflow-x-auto -mx-3 sm:mx-0 scrollbar-thin">
                            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Gambar</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Kategori</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Barcode</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Harga Beli</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Dibuat</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Diubah</th>
                                        <th class="px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap">
                                            <img
                                                v-if="product.image_path"
                                                :src="`/storage/${product.image_path}`"
                                                :alt="product.name"
                                                class="h-8 w-8 sm:h-10 sm:w-10 lg:h-12 lg:w-12 object-cover rounded"
                                            />
                                            <div v-else class="h-8 w-8 sm:h-10 sm:w-10 lg:h-12 lg:w-12 bg-gray-200 rounded">
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 text-xs sm:text-sm font-medium text-gray-900 max-w-[120px] sm:max-w-none truncate">{{ product.name }}</td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden md:table-cell">{{ product.category?.name || '-' }}</td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 font-mono hidden lg:table-cell">{{ product.barcode || '-' }}</td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                            <span :class="product.stock < 10 ? 'text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded' : ''">
                                                {{ product.stock }}
                                            </span>
                                        </td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">{{ formatCurrency(product.harga_beli) }}</td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 font-semibold">{{ formatCurrency(product.harga_jual) }}</td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-2 sm:px-4 lg:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                                <Link :href="route('products.show', product.id)" class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm" title="Detail">
                                                    Detail
                                                </Link>
                                                <button @click="openAdjustmentModal(product)" class="text-green-600 hover:text-green-900 text-left text-xs sm:text-sm" title="Sesuaikan Stok">
                                                    Stok
                                                </button>
                                                <Link :href="route('products.edit', product.id)" class="text-indigo-600 hover:text-indigo-900 text-xs sm:text-sm" title="Edit">
                                                    Edit
                                                </Link>
                                                <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900 text-left text-xs sm:text-sm" title="Hapus">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="products.data.length === 0 && search">
                                        <td colspan="10" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500">Tidak ditemukan produk dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="products.links.length > 3" class="mt-4 flex flex-wrap justify-center gap-1 sm:gap-2">
                            <template v-for="link in products.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-2 sm:px-3 py-1.5 sm:py-2 rounded text-xs sm:text-sm',
                                        link.active ? 'bg-blue-500 text-white font-semibold' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-2 sm:px-3 py-1.5 sm:py-2 rounded text-xs sm:text-sm bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Adjustment Modal -->
        <div v-if="showAdjustmentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-3 sm:px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" @click.self="closeAdjustmentModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full sm:w-full z-50 relative">
                    <form @submit.prevent="submitAdjustment">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modal-title">
                                Sesuaikan Stok: {{ selectedProduct?.name }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stok Saat Ini</label>
                                    <p class="mt-1 text-lg font-bold text-gray-900">{{ selectedProduct?.stock }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Penyesuaian</label>
                                    <select v-model="adjustmentForm.type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="deduction">Pengurangan</option>
                                        <option value="addition">Penambahan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                    <input
                                        v-model="adjustmentForm.quantity"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Masukkan jumlah"
                                    />
                                    <div v-if="adjustmentForm.errors.quantity" class="mt-1 text-sm text-red-600">
                                        {{ adjustmentForm.errors.quantity }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                                    <textarea
                                        v-model="adjustmentForm.notes"
                                        rows="3"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        placeholder="Alasan penyesuaian stok..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                :disabled="adjustmentForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                {{ adjustmentForm.processing ? 'Memproses...' : 'Simpan' }}
                            </button>
                            <button
                                type="button"
                                @click="closeAdjustmentModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background-color: #9ca3af;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background-color: #f3f4f6;
    border-radius: 3px;
}
</style>
