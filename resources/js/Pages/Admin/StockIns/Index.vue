<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    stock_ins: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('stock-ins.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteStockIn = (id) => {
    if (confirm('Hapus data barang masuk? Stok akan dikurangi kembali.')) {
        router.delete(route('stock-ins.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stok Masuk</h2>
                <Link :href="route('stock-ins.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Tambah Stok Masuk
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Empty State -->
                <EmptyState
                    v-if="stock_ins.data.length === 0 && !search"
                    icon="box"
                    title="Belum Ada Barang Masuk"
                    description="Catat barang masuk untuk tracking inventaris koperasi."
                    :action-url="route('stock-ins.create')"
                    action-text="Tambah Barang Masuk Pertama"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari produk..."
                                class="w-full md:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Beli</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stock in stock_ins.data" :key="stock.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div>{{ formatDate(stock.created_at) }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ new Date(stock.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium">{{ stock.product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ stock.quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatCurrency(stock.product.harga_beli) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ formatCurrency(stock.quantity * stock.product.harga_beli) }}</td>
                                        <td class="px-6 py-4 text-sm">{{ stock.supplier || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="stock.creator" :timestamp="stock.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="stock.updater" :timestamp="stock.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('stock-ins.show', stock.id)" class="text-blue-600 hover:text-blue-900 mr-3">Detail</Link>
                                            <button @click="deleteStockIn(stock.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="stock_ins.data.length === 0 && search">
                                        <td colspan="9" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500">Tidak ditemukan barang masuk dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="stock_ins.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in stock_ins.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded',
                                        link.active ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
