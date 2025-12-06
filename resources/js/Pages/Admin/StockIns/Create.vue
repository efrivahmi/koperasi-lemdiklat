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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tambah Stok Masuk</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
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
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <span v-if="!form.processing">Simpan</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('stock-ins.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
