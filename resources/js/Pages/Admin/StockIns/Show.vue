<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stockIn: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Detail Stock In" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Stock In
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('stock-ins.edit', stockIn.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('stock-ins.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Stock In Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Informasi Stock In</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Info -->
                            <div class="md:col-span-2 bg-blue-50 rounded-lg p-4">
                                <label class="text-sm text-blue-600 mb-2 block">Produk</label>
                                <div class="flex items-center gap-4">
                                    <div v-if="stockIn.product?.image_path" class="flex-shrink-0">
                                        <img
                                            :src="`/storage/${stockIn.product.image_path}`"
                                            :alt="stockIn.product.name"
                                            class="h-16 w-16 object-cover rounded"
                                        />
                                    </div>
                                    <div>
                                        <Link :href="route('products.show', stockIn.product?.id)" class="text-xl font-bold text-blue-900 hover:text-blue-700">
                                            {{ stockIn.product?.name }}
                                        </Link>
                                        <p class="text-sm text-blue-700 font-mono mt-1">{{ stockIn.product?.barcode }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="bg-green-50 rounded-lg p-4">
                                <label class="text-sm text-green-600 mb-2 block">Quantity</label>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-4xl font-bold text-green-900">{{ stockIn.quantity }}</p>
                                    <span class="text-lg text-green-700">unit</span>
                                </div>
                            </div>

                            <!-- Tanggal Stock In -->
                            <div class="bg-blue-50 rounded-lg p-4">
                                <label class="text-sm text-blue-600 mb-2 block">Tanggal Stock In</label>
                                <p class="text-xl font-bold text-blue-900">{{ formatDate(stockIn.created_at) }}</p>
                            </div>

                            <!-- Supplier -->
                            <div class="md:col-span-2 bg-purple-50 rounded-lg p-4">
                                <label class="text-sm text-purple-600 mb-2 block">Supplier</label>
                                <p class="text-2xl font-bold text-purple-900">{{ stockIn.supplier || '-' }}</p>
                            </div>

                            <!-- Notes (if available) -->
                            <div v-if="stockIn.notes" class="md:col-span-2 bg-gray-50 rounded-lg p-4 border-l-4 border-gray-400">
                                <label class="text-sm text-gray-600 mb-2 block">Catatan</label>
                                <p class="text-gray-900">{{ stockIn.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500 mb-1">Diinput oleh</p>
                                    <p class="font-semibold text-gray-900">{{ stockIn.user?.name || 'N/A' }}</p>
                                    <p class="text-sm text-gray-600">{{ stockIn.user?.email }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500 block mb-1">Dibuat Pada</label>
                                    <p class="text-gray-900">{{ formatDate(stockIn.created_at) }}</p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500 block mb-1">Terakhir Update</label>
                                    <p class="text-gray-900">{{ formatDate(stockIn.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Current Stock Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Stok Produk Saat Ini</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <label class="text-sm text-blue-600 mb-2 block">Stok Tersedia</label>
                                <p class="text-3xl font-bold"
                                    :class="[
                                        stockIn.product?.stock === 0 ? 'text-red-600' :
                                        stockIn.product?.stock <= 5 ? 'text-orange-600' :
                                        'text-green-600'
                                    ]"
                                >
                                    {{ stockIn.product?.stock || 0 }}
                                </p>
                                <p class="text-xs text-blue-600 mt-1">unit</p>
                            </div>

                            <div class="bg-green-50 rounded-lg p-4">
                                <label class="text-sm text-green-600 mb-2 block">Harga Jual</label>
                                <p class="text-xl font-bold text-green-900">
                                    {{ formatCurrency(stockIn.product?.harga_jual || 0) }}
                                </p>
                            </div>

                            <div class="bg-purple-50 rounded-lg p-4">
                                <label class="text-sm text-purple-600 mb-2 block">Nilai Stok</label>
                                <p class="text-xl font-bold text-purple-900">
                                    {{ formatCurrency((stockIn.product?.stock || 0) * (stockIn.product?.harga_jual || 0)) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <Link :href="route('products.show', stockIn.product?.id)" class="text-blue-600 hover:text-blue-800 font-medium">
                                Lihat Detail Produk Lengkap →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
