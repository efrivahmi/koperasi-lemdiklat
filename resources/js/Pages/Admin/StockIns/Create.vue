<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    products: Array,
});

const form = useForm({
    product_id: '',
    quantity: '',
    supplier: '',
    keterangan: '',
});

const submit = () => {
    form.post(route('stock-ins.store'));
};
</script>

<template>
    <Head title="Tambah Stok Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Barang Masuk</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Tambah Stok Masuk</h3>
                            <p class="text-purple-100 text-sm">Catat barang masuk ke inventori koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produk</label>
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                >
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} - {{ product.category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah / Quantity</label>
                                <input
                                    id="quantity"
                                    type="number"
                                    v-model="form.quantity"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    min="1"
                                    required
                                />
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier / Pemasok</label>
                                <input
                                    id="supplier"
                                    type="text"
                                    v-model="form.supplier"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    placeholder="Nama supplier (opsional)"
                                />
                                <InputError :message="form.errors.supplier" class="mt-2" />
                            </div>

                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    rows="3"
                                    placeholder="Catatan tambahan (opsional)"
                                ></textarea>
                                <InputError :message="form.errors.keterangan" class="mt-2" />
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:from-purple-800 active:to-indigo-800 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Stok Masuk</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('stock-ins.index')" class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
