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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Stock In
                </h2>
                <Link :href="route('stock-ins.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Product Selection -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Produk <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} ({{ product.barcode }})
                                    </option>
                                </select>
                                <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.product_id }}
                                </p>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jumlah (Quantity) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="quantity"
                                    v-model="form.quantity"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.quantity }}
                                </p>
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-2">
                                    Supplier / Pemasok
                                </label>
                                <input
                                    type="text"
                                    id="supplier"
                                    v-model="form.supplier"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Nama supplier (opsional)"
                                />
                                <p v-if="form.errors.supplier" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.supplier }}
                                </p>
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Keterangan
                                </label>
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    rows="3"
                                    placeholder="Catatan tambahan (opsional)"
                                ></textarea>
                                <p v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.keterangan }}
                                </p>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Stock in akan menambahkan stok produk yang dipilih sesuai dengan jumlah yang diinput.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-4 pt-4">
                                <Link :href="route('stock-ins.index')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded">
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50 disabled:cursor-not-allowed"
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
