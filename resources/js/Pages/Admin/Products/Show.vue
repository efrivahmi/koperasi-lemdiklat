<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const printBarcode = () => {
    window.open(route('products.print-barcode', props.product.id), '_blank');
};

// Statistik
const totalSold = computed(() => props.product.saleItems?.reduce((sum, item) => sum + item.quantity, 0) || 0);
const totalRevenue = computed(() => props.product.saleItems?.reduce((sum, item) => sum + (item.quantity * item.price), 0) || 0);
const stockValue = computed(() => props.product.stock * props.product.harga_jual);
const totalStockIn = computed(() => props.product.stockIns?.reduce((sum, item) => sum + item.quantity, 0) || 0);

const activeTab = ref('stock-in');
</script>

<template>
    <Head title="Detail Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Detail Produk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Detail Produk</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header Controls -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <p class="text-sm text-indigo-200 mt-0.5">
                            <span class="opacity-70">Manajemen Produk</span> / <span class="text-white">{{ product.name }}</span>
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link 
                            :href="route('products.index')" 
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors border border-white/10 text-sm font-medium"
                        >
                            Kembali
                        </Link>
                        <button 
                            @click="printBarcode" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg transition-colors shadow-lg shadow-indigo-500/20 text-sm font-medium flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak Barcode
                        </button>
                        <Link 
                            :href="route('products.edit', product.id)" 
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white rounded-lg transition-colors shadow-lg shadow-amber-500/20 text-sm font-medium flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit
                        </Link>
                    </div>
                </div>
                
                <!-- Stats Overlay -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Stock -->
                     <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-5 relative overflow-hidden group hover:bg-slate-800/70 transition-colors">
                        <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-400">Total Stok</p>
                                <h3 class="text-2xl font-bold text-white mt-1">{{ product.stock }} <span class="text-sm font-normal text-slate-400">{{ product.unit || 'Unit' }}</span></h3>
                            </div>
                            <div class="p-2 bg-blue-500/20 rounded-lg text-blue-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                        </div>
                        <div class="w-full bg-slate-700/50 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: Math.min((product.stock / 100) * 100, 100) + '%' }"></div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-5 relative overflow-hidden group hover:bg-slate-800/70 transition-colors">
                         <div class="absolute inset-0 bg-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-2 relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-400">Harga Jual</p>
                                <h3 class="text-2xl font-bold text-emerald-400 mt-1">{{ formatCurrency(product.harga_jual) }}</h3>
                            </div>
                            <div class="p-2 bg-emerald-500/20 rounded-lg text-emerald-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Beli: <span class="text-slate-300">{{ formatCurrency(product.harga_beli) }}</span></p>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-5 relative overflow-hidden group hover:bg-slate-800/70 transition-colors">
                        <div class="absolute inset-0 bg-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-2 relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-400">Total Revenue</p>
                                <h3 class="text-2xl font-bold text-white mt-1">{{ formatCurrency(totalRevenue) }}</h3>
                            </div>
                            <div class="p-2 bg-purple-500/20 rounded-lg text-purple-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Dari <span class="text-white font-medium">{{ totalSold }}</span> unit terjual</p>
                    </div>

                    <!-- Asset Value -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-5 relative overflow-hidden group hover:bg-slate-800/70 transition-colors">
                        <div class="absolute inset-0 bg-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-2 relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-400">Nilai Aset Stok</p>
                                <h3 class="text-2xl font-bold text-amber-400 mt-1">{{ formatCurrency(stockValue) }}</h3>
                            </div>
                            <div class="p-2 bg-amber-500/20 rounded-lg text-amber-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Potensi pendapatan dari stok saat ini</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Product Info -->
                    <div class="lg:col-span-1 space-y-8">
                        <!-- Main Info Card -->
                        <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                             <div class="relative group aspect-square bg-slate-900/50">
                                <img v-if="product.image_path"
                                    :src="`/storage/${product.image_path}`"
                                    :alt="product.name"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500">
                                    <svg class="w-20 h-20 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-sm font-medium">Tidak ada gambar</span>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-transparent to-transparent opacity-60"></div>
                                <div class="absolute bottom-4 left-4 right-4 text-white">
                                    <div v-if="product.category" class="mb-2">
                                         <Link 
                                            :href="route('categories.show', product.category?.id)"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 hover:bg-white/30 text-white backdrop-blur-md transition-colors"
                                        >
                                            {{ product.category?.name }}
                                        </Link>
                                    </div>
                                    <h1 class="text-2xl font-bold leading-tight">{{ product.name }}</h1>
                                </div>
                            </div>

                            <div class="p-6 space-y-6">
                                <div>
                                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Barcode / SKU</h4>
                                    <div class="bg-slate-900/50 rounded-lg p-3 text-center border border-white/5 font-mono text-xl tracking-widest text-indigo-300">
                                        {{ product.barcode }}
                                    </div>
                                </div>

                                <div v-if="product.description">
                                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi</h4>
                                    <p class="text-sm text-slate-300 leading-relaxed">
                                        {{ product.description }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                                    <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat Oleh" />
                                    <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diupdate Oleh" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Tabs & History -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Custom Tabs -->
                        <div class="flex gap-4 border-b border-white/10 px-2">
                            <button 
                                @click="activeTab = 'stock-in'"
                                class="pb-4 px-2 text-sm font-medium transition-all relative"
                                :class="activeTab === 'stock-in' ? 'text-indigo-400' : 'text-slate-400 hover:text-slate-300'"
                            >
                                Riwayat Stock In
                                <div v-if="activeTab === 'stock-in'" class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
                            </button>
                            <button 
                                @click="activeTab = 'sales'"
                                class="pb-4 px-2 text-sm font-medium transition-all relative"
                                :class="activeTab === 'sales' ? 'text-emerald-400' : 'text-slate-400 hover:text-slate-300'"
                            >
                                Riwayat Penjualan
                                <div v-if="activeTab === 'sales'" class="absolute bottom-0 left-0 w-full h-0.5 bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                            </button>
                        </div>

                        <!-- Content Area -->
                         <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden min-h-[400px]">
                            <!-- Stock In Table -->
                            <div v-if="activeTab === 'stock-in'">
                                <div v-if="product.stockIns && product.stockIns.length > 0" class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-slate-300">
                                        <thead class="text-xs text-slate-400 uppercase bg-slate-900/50 border-b border-white/10">
                                            <tr>
                                                <th class="px-6 py-4 font-medium tracking-wider">Tanggal</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Qty</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Harga Beli</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Total</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Supplier</th>
                                                <th class="px-6 py-4 font-medium tracking-wider text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="stockIn in product.stockIns" :key="stockIn.id" class="hover:bg-slate-700/30 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                                    {{ formatDate(stockIn.tanggal) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="font-bold text-white">{{ stockIn.quantity }}</span> <span class="text-xs text-slate-500">Unit</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                                    {{ formatCurrency(stockIn.harga_beli_satuan) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-indigo-300">
                                                    {{ formatCurrency(stockIn.total_cost) }}
                                                </td>
                                                 <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                                    {{ stockIn.supplier || '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <Link :href="route('stock-ins.show', stockIn.id)" class="text-indigo-400 hover:text-indigo-300 text-xs font-semibold uppercase tracking-wide">
                                                        Detail
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-16 text-slate-400">
                                    <div class="bg-slate-800 p-4 rounded-full mb-3">
                                        <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    </div>
                                    <p>Belum ada riwayat stock in</p>
                                </div>
                            </div>

                            <!-- Sales Table -->
                             <div v-if="activeTab === 'sales'">
                                <div v-if="product.saleItems && product.saleItems.length > 0" class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-slate-300">
                                        <thead class="text-xs text-slate-400 uppercase bg-slate-900/50 border-b border-white/10">
                                            <tr>
                                                <th class="px-6 py-4 font-medium tracking-wider">Tanggal</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Transaksi</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Qty</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Harga</th>
                                                <th class="px-6 py-4 font-medium tracking-wider">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="item in product.saleItems" :key="item.id" class="hover:bg-slate-700/30 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-slate-400">
                                                    {{ formatDate(item.created_at) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="font-mono text-xs bg-slate-900 px-2 py-1 rounded text-slate-300 border border-white/10">#{{ item.sale_id }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="font-bold text-white">{{ item.quantity }}</span> <span class="text-xs text-slate-500">Unit</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                                                    {{ formatCurrency(item.price) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-emerald-400">
                                                    {{ formatCurrency(item.quantity * item.price) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center py-16 text-slate-400">
                                    <div class="bg-slate-800 p-4 rounded-full mb-3">
                                        <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <p>Belum ada riwayat penjualan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
