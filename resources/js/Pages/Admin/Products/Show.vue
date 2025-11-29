<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
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

// Statistik
const totalSold = props.product.sale_items?.reduce((sum, item) => sum + item.quantity, 0) || 0;
const totalRevenue = props.product.sale_items?.reduce((sum, item) => sum + (item.quantity * item.price), 0) || 0;
const stockValue = props.product.stock * props.product.harga_jual;
const totalStockIn = props.product.stock_ins?.reduce((sum, item) => sum + item.quantity, 0) || 0;
</script>

<template>
    <Head :title="`Detail Produk - ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Produk
                </h2>
                <div class="flex gap-2">
                    <button @click="window.print()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Cetak Barcode
                    </button>
                    <Link :href="route('products.edit', product.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('products.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Product Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Image Section -->
                            <div class="md:col-span-1">
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                    <img v-if="product.image_path"
                                        :src="`/storage/${product.image_path}`"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <span class="text-gray-400 text-4xl">No Image</span>
                                    </div>
                                </div>
                                <!-- Barcode -->
                                <div class="mt-4 bg-gray-50 rounded-lg p-4 text-center">
                                    <label class="text-xs text-gray-500 block mb-2">Barcode</label>
                                    <p class="text-2xl font-mono font-bold text-gray-900 tracking-wider">{{ product.barcode }}</p>
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div class="md:col-span-2">
                                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ product.name }}</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm text-gray-500">Kategori</label>
                                            <p class="text-lg font-semibold text-gray-900">
                                                <Link :href="route('categories.show', product.category?.id)" class="text-blue-600 hover:text-blue-800">
                                                    {{ product.category?.name || 'N/A' }}
                                                </Link>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-500">Harga Beli</label>
                                            <p class="text-lg text-gray-900">{{ formatCurrency(product.harga_beli) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-500">Harga Jual</label>
                                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(product.harga_jual) }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm text-gray-500">Stok Tersedia</label>
                                            <div class="flex items-center gap-2">
                                                <p class="text-3xl font-bold"
                                                    :class="[
                                                        product.stock === 0 ? 'text-red-600' :
                                                        product.stock <= 5 ? 'text-orange-600' :
                                                        'text-green-600'
                                                    ]"
                                                >
                                                    {{ product.stock }}
                                                </p>
                                                <span class="text-gray-500">unit</span>
                                            </div>
                                        </div>
                                        <div v-if="product.description">
                                            <label class="text-sm text-gray-500">Deskripsi</label>
                                            <p class="text-gray-700">{{ product.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-blue-50">
                            <p class="text-sm text-blue-600 mb-1">Total Terjual</p>
                            <p class="text-3xl font-bold text-blue-900">{{ totalSold }}</p>
                            <p class="text-xs text-blue-600 mt-1">unit</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-green-50">
                            <p class="text-sm text-green-600 mb-1">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-900">{{ formatCurrency(totalRevenue) }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-purple-50">
                            <p class="text-sm text-purple-600 mb-1">Nilai Stok</p>
                            <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(stockValue) }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-orange-50">
                            <p class="text-sm text-orange-600 mb-1">Total Stock In</p>
                            <p class="text-3xl font-bold text-orange-900">{{ totalStockIn }}</p>
                            <p class="text-xs text-orange-600 mt-1">unit</p>
                        </div>
                    </div>
                </div>

                <!-- Stock In History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Stock In</h3>

                        <div v-if="product.stock_ins && product.stock_ins.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli/Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stockIn in product.stock_ins" :key="stockIn.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(stockIn.tanggal) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-lg font-semibold text-gray-900">{{ stockIn.quantity }}</span>
                                            <span class="text-sm text-gray-500"> unit</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(stockIn.harga_beli_satuan) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(stockIn.total_cost) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ stockIn.supplier || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('stock-ins.show', stockIn.id)" class="text-blue-600 hover:text-blue-900">
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat stock in</h3>
                            <p class="mt-1 text-sm text-gray-500">Stock in untuk produk ini akan muncul di sini.</p>
                        </div>
                    </div>
                </div>

                <!-- Sales History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Penjualan</h3>

                        <div v-if="product.sale_items && product.sale_items.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Transaksi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in product.sale_items" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(item.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('sales.show', item.sale_id)" class="text-blue-600 hover:text-blue-900 font-mono text-sm">
                                                #{{ item.sale_id }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-lg font-semibold text-gray-900">{{ item.quantity }}</span>
                                            <span class="text-sm text-gray-500"> unit</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(item.price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            {{ formatCurrency(item.quantity * item.price) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada penjualan</h3>
                            <p class="mt-1 text-sm text-gray-500">Penjualan produk ini akan muncul di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
