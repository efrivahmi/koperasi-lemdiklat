<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';
import PaginatedProductTable from '@/Components/PaginatedProductTable.vue';

const props = defineProps({
    category: Object,
    totalProducts: Number,
    totalStock: Number,
    totalValue: Number,
});

const { can } = usePermissions();

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Detail Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Detail Kategori
            </h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-10 space-y-8">
                
                <!-- Header Actions moved to Body -->
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white">{{ category.name }}</h3>
                        <p class="text-slate-400 text-sm mt-1">Kelola informasi dan produk kategori ini</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('kasir.categories.index')" class="inline-flex items-center px-4 py-2 bg-slate-700/50 hover:bg-slate-700 border border-white/10 text-slate-300 hover:text-white rounded-lg transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Kembali
                        </Link>
                        <Link :href="route('kasir.categories.edit', category.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg shadow-lg shadow-indigo-500/30 transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit
                        </Link>
                    </div>
                </div>

                <!-- Category Info Card -->
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left: Details -->
                            <div>
                                <h3 class="text-xl font-bold text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Informasi Kategori
                                </h3>
                                <div class="space-y-6">
                                    <div class="bg-slate-900/40 rounded-xl p-5 border border-white/5">
                                        <label class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Nama Kategori</label>
                                        <p class="text-2xl font-bold text-white mt-1">{{ category.name }}</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                         <div class="bg-slate-900/40 rounded-xl p-5 border border-white/5">
                                            <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Type</label>
                                            <p class="text-lg font-medium text-slate-200 mt-1">
                                                <span v-if="!category.parent_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-900/50 text-purple-200 border border-purple-500/20">
                                                    Main Category
                                                </span>
                                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/50 text-blue-200 border border-blue-500/20">
                                                    Sub Category
                                                </span>
                                            </p>
                                        </div>
                                        <div class="bg-slate-900/40 rounded-xl p-5 border border-white/5">
                                            <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Dibuat</label>
                                            <p class="text-sm text-slate-300 mt-2">{{ new Date(category.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                                        </div>
                                    </div>

                                    <div v-if="category.description" class="bg-slate-900/40 rounded-xl p-5 border border-white/5">
                                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi</label>
                                        <p class="text-slate-300 mt-2 leading-relaxed">{{ category.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Statistics -->
                            <div>
                                <h3 class="text-xl font-bold text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                    Statistik
                                </h3>
                                <div class="space-y-4">
                                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 border border-white/5 shadow-lg relative overflow-hidden group">
                                        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                            <svg class="w-24 h-24 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" /></svg>
                                        </div>
                                        <p class="text-sm font-medium text-blue-400">Total Produk</p>
                                        <p class="text-3xl font-bold text-white mt-1">{{ totalProducts }} <span class="text-sm text-slate-500 font-normal">Item</span></p>
                                    </div>

                                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 border border-white/5 shadow-lg relative overflow-hidden group">
                                         <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                            <svg class="w-24 h-24 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" /></svg>
                                        </div>
                                        <p class="text-sm font-medium text-emerald-400">Total Stok Fisik</p>
                                        <p class="text-3xl font-bold text-white mt-1">{{ totalStock }} <span class="text-sm text-slate-500 font-normal">Unit</span></p>
                                    </div>

                                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-5 border border-white/5 shadow-lg relative overflow-hidden group">
                                         <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                            <svg class="w-24 h-24 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" /></svg>
                                        </div>
                                        <p class="text-sm font-medium text-purple-400">Estimasi Nilai Aset</p>
                                        <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(totalValue) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub Categories List (Only for Parent Categories) -->
                <div v-if="category.children && category.children.length > 0" class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-white/5">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Sub Kategori
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Nama Sub Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jumlah Produk</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-transparent">
                                <tr v-for="child in category.children" :key="child.id" class="group hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ child.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400 max-w-xs truncate">
                                        {{ child.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-500/20">
                                            {{ child.products ? child.products.length : 0 }} Item
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link v-if="can && can('categories.edit')" :href="route('kasir.categories.edit', child.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors inline-block" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>



<!-- ... (template header and info card remain unchanged) ... -->

                <!-- Products Display -->
                
                <!-- IF parent category: Show products grouped by subcategory -->
                <template v-if="category.children && category.children.length > 0">
                    <div v-for="child in category.children" :key="child.id" class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-indigo-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">Produk: {{ child.name }}</h3>
                                    <p class="text-xs text-slate-400">Daftar produk dalam sub-kategori {{ child.name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-0">
                            <PaginatedProductTable :products="child.products || []" :items-per-page="5" />
                        </div>
                    </div>
                </template>

                <!-- ELSE: It's a subcategory or standalone, show single product list -->
                <div v-else class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                     <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-500/20 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                            <h3 class="text-lg font-bold text-white">Daftar Produk</h3>
                        </div>
                         <Link v-if="can && can('products.create')" :href="route('kasir.products.create')" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-lg transition-colors shadow-lg shadow-emerald-500/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Tambah Produk
                        </Link>
                    </div>

                    <div class="p-0">
                        <PaginatedProductTable :products="category.products || []" :items-per-page="10" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
