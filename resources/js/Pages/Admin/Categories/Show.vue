<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    category: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const totalProducts = props.category.products?.length || 0;
const totalStock = props.category.products?.reduce((sum, product) => sum + product.stock, 0) || 0;
const totalValue = props.category.products?.reduce((sum, product) => sum + (product.stock * product.harga_jual), 0) || 0;
</script>

<template>
    <Head :title="`Detail Kategori - ${category.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Kategori
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('categories.edit', category.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('categories.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Category Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kategori</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm text-gray-500">Nama Kategori</label>
                                        <p class="text-lg font-semibold text-gray-900">{{ category.name }}</p>
                                    </div>
                                    <div v-if="category.description">
                                        <label class="text-sm text-gray-500">Deskripsi</label>
                                        <p class="text-gray-700">{{ category.description }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Dibuat Pada</label>
                                        <p class="text-gray-700">{{ new Date(category.created_at).toLocaleDateString('id-ID', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <p class="text-sm text-blue-600">Total Produk</p>
                                        <p class="text-2xl font-bold text-blue-900">{{ totalProducts }}</p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <p class="text-sm text-green-600">Total Stok</p>
                                        <p class="text-2xl font-bold text-green-900">{{ totalStock }} unit</p>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <p class="text-sm text-purple-600">Nilai Inventaris</p>
                                        <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(totalValue) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Produk</h3>
                            <Link :href="route('products.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                + Tambah Produk
                            </Link>
                        </div>

                        <div v-if="category.products && category.products.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in category.products" :key="product.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img v-if="product.image_path"
                                                :src="`/storage/${product.image_path}`"
                                                :alt="product.name"
                                                class="h-12 w-12 object-cover rounded"
                                            />
                                            <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                                <span class="text-gray-400 text-xs">No Img</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ product.barcode }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(product.harga_beli) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            {{ formatCurrency(product.harga_jual) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="[
                                                    product.stock === 0 ? 'bg-red-100 text-red-800' :
                                                    product.stock <= 5 ? 'bg-orange-100 text-orange-800' :
                                                    'bg-green-100 text-green-800'
                                                ]"
                                            >
                                                {{ product.stock }} unit
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('products.show', product.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                                Detail
                                            </Link>
                                            <Link :href="route('products.edit', product.id)" class="text-indigo-600 hover:text-indigo-900">
                                                Edit
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
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada produk</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan produk pertama untuk kategori ini.</p>
                            <div class="mt-6">
                                <Link :href="route('products.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                    + Tambah Produk Pertama
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
