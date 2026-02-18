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
        <template #mobileTitle>Stok Masuk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Detail Stock In
            </h2>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-white">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-20 pattern-grid-lg"></div>
                    <div class="relative z-10 flex items-center gap-6">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold tracking-tight">Informasi Stok Masuk</h3>
                            <p class="text-blue-100 text-sm mt-1">Detail transaksi barang masuk: <span class="font-mono font-semibold">{{ stockIn.product?.name }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Product & Stock Details -->
                    <div class="lg:col-span-2 space-y-6">
                         <!-- Stock In Details -->
                         <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl overflow-hidden p-6 md:p-8">
                            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                Detail Transaksi
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Info -->
                                <div class="md:col-span-2 bg-slate-900/50 rounded-xl p-6 border border-white/5">
                                    <label class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-3 block">Produk</label>
                                    <div class="flex items-center gap-4">
                                        <div v-if="stockIn.product?.image_path" class="flex-shrink-0">
                                            <img
                                                :src="`/storage/${stockIn.product.image_path}`"
                                                :alt="stockIn.product.name"
                                                class="h-16 w-16 object-cover rounded-lg border border-white/10"
                                            />
                                        </div>
                                        <div>
                                            <Link :href="route('products.show', stockIn.product?.id)" class="text-xl font-bold text-white hover:text-indigo-400 transition-colors">
                                                {{ stockIn.product?.name }}
                                            </Link>
                                            <p class="text-sm text-slate-400 font-mono mt-1">{{ stockIn.product?.barcode }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div class="bg-slate-900/50 rounded-xl p-6 border border-white/5">
                                    <label class="text-xs font-semibold text-green-400 uppercase tracking-wider mb-2 block">Quantity Masuk</label>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-4xl font-bold text-white">+{{ stockIn.quantity }}</p>
                                        <span class="text-sm text-slate-400">unit</span>
                                    </div>
                                </div>

                                <!-- Tanggal -->
                                <div class="bg-slate-900/50 rounded-xl p-6 border border-white/5">
                                    <label class="text-xs font-semibold text-blue-400 uppercase tracking-wider mb-2 block">Tanggal</label>
                                    <p class="text-lg font-bold text-white">{{ formatDate(stockIn.created_at) }}</p>
                                    <p class="text-sm text-slate-400 mt-1">
                                        {{ new Date(stockIn.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>

                                <!-- Supplier -->
                                <div class="md:col-span-2 bg-slate-900/50 rounded-xl p-6 border border-white/5">
                                    <label class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2 block">Supplier / Pemasok</label>
                                    <p class="text-2xl font-bold text-white">{{ stockIn.supplier || '-' }}</p>
                                </div>

                                <!-- Keterangan -->
                                <div v-if="stockIn.notes" class="md:col-span-2 bg-slate-900/50 rounded-xl p-6 border border-white/5 border-l-4 border-l-slate-600">
                                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 block">Catatan / Keterangan</label>
                                    <p class="text-slate-300">{{ stockIn.notes }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons moved to body -->
                            <div class="mt-8 flex gap-4 border-t border-white/10 pt-6">
                                <Link :href="route('stock-ins.edit', stockIn.id)" class="inline-flex items-center justify-center px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl shadow-lg shadow-amber-500/20 transition-all transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-.5-9l4.5 4.5M6 17v4m-1-2h2" /></svg>
                                    Edit Data
                                </Link>
                                <Link :href="route('stock-ins.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl border border-white/10 transition-all hover:bg-slate-600">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                    Kembali
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Current Stock, Audit Info -->
                    <div class="space-y-6">
                        <!-- Product Current Stock -->
                        <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6">
                            <h4 class="text-lg font-semibold text-white mb-4">Status Stok Produk</h4>
                            
                            <div class="space-y-4">
                                <div class="bg-slate-900/50 rounded-xl p-4 border border-white/5">
                                    <div class="text-sm text-slate-400 mb-1">Stok Tersedia Saat Ini</div>
                                    <div class="text-3xl font-bold"
                                        :class="[
                                            stockIn.product?.stock === 0 ? 'text-red-400' :
                                            stockIn.product?.stock <= 5 ? 'text-orange-400' :
                                            'text-emerald-400'
                                        ]"
                                    >
                                        {{ stockIn.product?.stock || 0 }} <span class="text-sm font-normal text-slate-500">unit</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-slate-900/50 rounded-xl p-4 border border-white/5">
                                        <div class="text-xs text-slate-400 mb-1">Harga Jual</div>
                                        <div class="text-lg font-bold text-white">{{ formatCurrency(stockIn.product?.harga_jual || 0) }}</div>
                                    </div>
                                     <div class="bg-slate-900/50 rounded-xl p-4 border border-white/5">
                                        <div class="text-xs text-slate-400 mb-1">Nilai Stok</div>
                                        <div class="text-lg font-bold text-indigo-400">{{ formatCurrency((stockIn.product?.stock || 0) * (stockIn.product?.harga_jual || 0)) }}</div>
                                    </div>
                                </div>
                            </div>

                             <div class="mt-6 pt-4 border-t border-white/10 text-center">
                                <Link :href="route('products.show', stockIn.product?.id)" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium flex items-center justify-center gap-1 transition-colors">
                                    Lihat Detail Produk Lengkap
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </Link>
                            </div>
                        </div>

                         <!-- Audit Info -->
                        <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6">
                             <h4 class="text-lg font-semibold text-white mb-4">Informasi Audit</h4>
                             
                             <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-slate-700/50 rounded-lg">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-400">Diinput Oleh</div>
                                        <div class="font-medium text-white">{{ stockIn.user?.name || 'Sistem' }}</div>
                                        <div class="text-xs text-slate-500">{{ stockIn.user?.email }}</div>
                                    </div>
                                </div>

                                <div class="h-px bg-white/10"></div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs text-slate-400 mb-1">Dibuat</div>
                                        <div class="text-sm text-white">{{ formatDate(stockIn.created_at) }}</div>
                                    </div>
                                     <div>
                                        <div class="text-xs text-slate-400 mb-1">Diupdate</div>
                                        <div class="text-sm text-white">{{ formatDate(stockIn.updated_at) }}</div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
