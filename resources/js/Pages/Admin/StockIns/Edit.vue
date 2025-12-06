<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stockIn: Object,
    products: Array,
});

const form = useForm({
    product_id: props.stockIn.product_id,
    quantity: props.stockIn.quantity,
    supplier: props.stockIn.supplier || '',
    keterangan: props.stockIn.notes || '',
});

const submit = () => {
    form.put(route('stock-ins.update', props.stockIn.id));
};
</script>

<template>
    <Head title="Edit Stock In" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Stock In
                </h2>
                <Link :href="route('stock-ins.index')" class="inline-flex items-center justify-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Product Selection -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Produk <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                >
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.barcode }})
                                    </option>
                                </select>
                                <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.product_id }}
                                </p>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Jumlah (Quantity) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="quantity"
                                    v-model="form.quantity"
                                    min="1"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                />
                                <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.quantity }}
                                </p>
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Supplier / Pemasok
                                </label>
                                <input
                                    type="text"
                                    id="supplier"
                                    v-model="form.supplier"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    placeholder="Nama supplier (opsional)"
                                />
                                <p v-if="form.errors.supplier" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.supplier }}
                                </p>
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Keterangan
                                </label>
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    rows="3"
                                    placeholder="Catatan tambahan (opsional)"
                                ></textarea>
                                <p v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.keterangan }}
                                </p>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-400 dark:border-indigo-600 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-indigo-400 dark:text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-indigo-700 dark:text-indigo-300">
                                            Stock in akan menambahkan stok produk yang dipilih sesuai dengan jumlah yang diinput.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col-reverse sm:flex-row items-start sm:items-center sm:justify-end gap-2 sm:gap-4 pt-4">
                                <Link :href="route('stock-ins.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                                >
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Update Stock In</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
