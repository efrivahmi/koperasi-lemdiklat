<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    products: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showAdjustmentModal = ref(false);
const selectedProduct = ref(null);
const customReason = ref('');
const isLoading = ref(false);

const adjustmentForm = useForm({
    type: 'deduction',
    quantity: 1,
    purpose: 'other',
    notes: '',
});

// Watch search to debounce and reload
const handleSearch = () => {
    isLoading.value = true;
    router.get(route('products.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
        onFinish: () => isLoading.value = false,
    });
};

const clearSearch = () => {
    search.value = '';
    handleSearch();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const openAdjustmentModal = (product) => {
    // Enforce First Stock In Rule
    if (!product.stock_ins_count || product.stock_ins_count === 0) {
        alert('Produk ini belum memiliki riwayat Barang Masuk (Stock In). Sila lakukan input stok awal melalui menu "Barang Masuk" terlebih dahulu.');
        return;
    }

    selectedProduct.value = product;
    adjustmentForm.reset();
    adjustmentForm.type = 'deduction'; // Default safety
    adjustmentForm.purpose = 'damage'; // Default common reason
    customReason.value = '';
    showAdjustmentModal.value = true;
};

const closeAdjustmentModal = () => {
    showAdjustmentModal.value = false;
    selectedProduct.value = null;
    adjustmentForm.reset();
    customReason.value = '';
};

// Auto-set type based on purpose
watch(() => adjustmentForm.purpose, (newPurpose) => {
    if (['sale', 'internal_use', 'personal_use', 'damage', 'expired', 'return_to_supplier'].includes(newPurpose)) {
        adjustmentForm.type = 'deduction';
    } else if (newPurpose === 'other') {
        // For 'other', allow manual selection, default to deduction but don't force it if user changed it
        // We can leave it as current or set it to 'deduction' as a starting point
    }
});

const submitAdjustment = () => {
    if (adjustmentForm.purpose === 'other' && customReason.value) {
        // Validation for 'Other' type selection
        if (!adjustmentForm.type) {
             alert('Silakan pilih Jenis Penyesuaian (Pengurangan atau Penambahan) untuk alasan Lainnya.');
             return;
        }
        adjustmentForm.notes = `Custom Reason: ${customReason.value}. ${adjustmentForm.notes}`;
    }
    
    adjustmentForm.post(route('products.adjust-stock', selectedProduct.value.id), {
        onSuccess: () => {
            closeAdjustmentModal();
        },
    });
};

const deleteProduct = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        router.delete(route('products.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Produk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Produk</h2>
        </template>

        <!-- Galaxy Theme Container -->
        <!-- Removed conflicting background to use AuthenticatedLayout's theme -->
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                
                <!-- Toolbar with Glassmorphism -->
                <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl p-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                        
                        <!-- Search Bar -->
                        <div class="flex-1 max-w-lg flex gap-2">
                            <div class="relative group flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-indigo-400 group-focus-within:text-indigo-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="search"
                                    @keyup.enter="handleSearch"
                                    type="text"
                                    placeholder="Cari produk (nama, barcode)..."
                                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-900/60 border border-indigo-500/30 text-indigo-100 placeholder-indigo-400/50 rounded-lg focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-400 focus:bg-slate-900/80 transition-all duration-200 shadow-inner"
                                />
                                <div v-if="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button @click="clearSearch" class="text-slate-400 hover:text-white transition-colors focus:outline-none">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button 
                                @click="handleSearch"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg transition-colors shadow-lg shadow-indigo-500/30"
                            >
                                Cari
                            </button>
                        </div>

                        <!-- Actions Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <Link 
                                v-if="can('products.barcode')" 
                                :href="route('products.barcode-generator')" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-slate-700/50 hover:bg-slate-700 border border-white/10 text-indigo-200 font-medium rounded-lg transition-all duration-200 hover:shadow-lg hover:border-indigo-500/30 group"
                            >
                                <svg class="w-5 h-5 mr-2 text-indigo-400 group-hover:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Generator Barcode
                            </Link>
                            
                            <Link 
                                v-if="can('products.create')" 
                                :href="route('products.create')" 
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 transform hover:-translate-y-0.5"
                            >
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Tambah Produk</span>
                                <span class="sm:hidden">Tambah</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <EmptyState
                    v-if="products.data.length === 0 && !search && !isLoading"
                    icon="box"
                    title="Belum Ada Produk"
                    description="Mulai tambahkan produk untuk mengelola inventaris koperasi."
                    :action-url="route('products.create')"
                    action-text="Tambah Produk Pertama"
                    class="bg-slate-800/50 border border-white/5 backdrop-blur-sm text-slate-300"
                />

                <div v-else class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Info Produk</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-transparent">
                                <tr v-for="product in products.data" :key="product.id" class="group hover:bg-white/5 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 flex-shrink-0 relative">
                                                <img 
                                                    class="h-12 w-12 rounded-lg object-cover bg-slate-700 ring-2 ring-white/10 group-hover:ring-indigo-500/50 transition-all" 
                                                    :src="product.image_url || '/images/no-image.png'" 
                                                    :alt="product.name"
                                                >
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-white group-hover:text-indigo-200 transition-colors">{{ product.name }}</div>
                                                <div class="text-xs text-slate-400 font-mono mt-0.5 flex items-center">
                                                    <svg class="w-3 h-3 mr-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                                    {{ product.barcode || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-full bg-indigo-900/30 text-indigo-300 border border-indigo-500/20">
                                            {{ product.category?.name || 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-white">{{ formatCurrency(product.harga_jual) }}</div>
                                        <div class="text-xs text-slate-500 mt-1">Beli: {{ formatCurrency(product.harga_beli) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                        <div class="text-sm font-bold text-white mb-1">
                                            {{ product.stock }} 
                                            <span class="text-xs font-normal text-slate-400 ml-0.5" v-if="product.unit">{{ product.unit }}</span>
                                            <span class="text-xs font-normal text-slate-400 ml-0.5" v-else>Pcs</span>
                                        </div>
                                        <div v-if="product.netto" class="text-[10px] text-slate-500 -mt-1">{{ product.netto }}</div>
                                            
                                            <div class="w-16 h-px bg-white/10 my-0.5"></div>

                                            <button 
                                                v-if="can('products.stock')" 
                                                @click="openAdjustmentModal(product)" 
                                                class="text-xs text-indigo-400 hover:text-indigo-300 hover:underline decoration-indigo-400/50 underline-offset-2 transition-colors"
                                            >
                                                Sesuaikan
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span v-if="product.stock <= 0" class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-900/30 text-rose-300 border border-rose-500/20">
                                            Habis
                                        </span>
                                        <span v-else-if="product.stock <= 5" class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-900/30 text-amber-300 border border-amber-500/20 animate-pulse">
                                            Menipis
                                        </span>
                                        <span v-else class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-900/30 text-emerald-300 border border-emerald-500/20">
                                            Tersedia
                                        </span>
                                    </td>
                                    <!-- Audit Info Columns -->
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                        <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                        <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <Link 
                                                v-if="can('products.edit')" 
                                                :href="route('products.edit', product.id)" 
                                                class="text-slate-400 hover:text-white transition-colors"
                                                title="Edit Produk"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </Link>
                                            <button 
                                                v-if="can('products.delete')" 
                                                @click="deleteProduct(product.id)" 
                                                class="text-rose-400/70 hover:text-rose-400 transition-colors"
                                                title="Hapus Produk"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                        Tidak ada produk yang cocok dengan pencarian "{{ search }}"
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.links.length > 3" class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex justify-center">
                         <div class="flex space-x-1">
                            <template v-for="(link, k) in products.links" :key="k">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200',
                                        link.active 
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white border border-white/5'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-1.5 rounded-md text-sm font-medium bg-slate-900/50 text-slate-600 border border-white/5 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal (Dark Theme) -->
        <div v-if="showAdjustmentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeAdjustmentModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10 ring-1 ring-white/5">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-xl leading-6 font-bold text-white mb-1" id="modal-title">
                                    Sesuaikan Stok
                                </h3>
                                <p class="text-sm text-slate-400 mb-6">Produk: <span class="text-indigo-400 font-semibold">{{ selectedProduct?.name }}</span></p>

                                <form @submit.prevent="submitAdjustment">
                                    
                                    <!-- Purpose Selection -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Alasan Penyesuaian</label>
                                        <select v-model="adjustmentForm.purpose" class="block w-full pl-3 pr-10 py-2.5 text-base bg-slate-900/70 border-slate-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg text-white">
                                            <option value="damage">Kerusakan Barang (Pengurangan - Rugi)</option>
                                            <option value="expired">Barang Kadaluarsa (Pengurangan - Rugi)</option>
                                            <option value="internal_use">Keperluan Internal/Kantor (Pengurangan - Rugi/Biaya)</option>
                                            <option value="personal_use">Keperluan Pribadi (Pengurangan - Prive)</option>
                                            <option value="return_to_supplier">Retur ke Supplier (Pengurangan - Refund)</option>
                                            <option value="sale">Penjualan Manual/Langsung (Pengurangan - Profit)</option>
                                            <option value="other">Lainnya (Custom)</option>
                                        </select>
                                    </div>

                                    <!-- Custom Reason Input -->
                                    <div v-if="adjustmentForm.purpose === 'other'" class="mb-5 animate-fadeIn">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Tulis Alasan Lainnya</label>
                                        <input 
                                            type="text" 
                                            v-model="customReason" 
                                            placeholder="Contoh: Salah hitung saat opname, Bonus dari supplier, dll."
                                            class="block w-full py-2.5 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm placeholder-slate-500"
                                            required
                                        >
                                    </div>

                                    <!-- Type Selection (Auto-updated but editable) -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Jenis Penyesuaian</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label 
                                                class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                                :class="[
                                                    adjustmentForm.type === 'deduction' ? 'bg-rose-900/30 border-rose-500/50 text-rose-200' : 'bg-slate-900/50 border-slate-700 text-slate-400 hover:bg-slate-800',
                                                    adjustmentForm.purpose !== 'other' ? 'opacity-50 cursor-not-allowed' : ''
                                                ]"
                                            >
                                                <input type="radio" v-model="adjustmentForm.type" value="deduction" class="sr-only" :disabled="adjustmentForm.purpose !== 'other'">
                                                <span class="block font-semibold">Pengurangan (-)</span>
                                            </label>
                                            <label 
                                                class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                                :class="[
                                                    adjustmentForm.type === 'addition' ? 'bg-emerald-900/30 border-emerald-500/50 text-emerald-200' : 'bg-slate-900/50 border-slate-700 text-slate-400 hover:bg-slate-800',
                                                    adjustmentForm.purpose !== 'other' ? 'opacity-50 cursor-not-allowed' : ''
                                                ]"
                                            >
                                                <input type="radio" v-model="adjustmentForm.type" value="addition" class="sr-only" :disabled="adjustmentForm.purpose !== 'other'">
                                                <span class="block font-semibold">Penambahan (+)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Jumlah Unit</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <input 
                                                type="number" 
                                                v-model="adjustmentForm.quantity" 
                                                min="1" 
                                                class="block w-full py-2.5 pr-12 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-lg font-bold" 
                                                required
                                            >
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 sm:text-sm">{{ selectedProduct?.unit || 'Pcs' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Catatan Tambahan (Opsional)</label>
                                        <textarea 
                                            v-model="adjustmentForm.notes" 
                                            rows="2" 
                                            class="block w-full py-2 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm placeholder-slate-500"
                                            placeholder="Detail tambahan..."
                                        ></textarea>
                                    </div>

                                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                        <button 
                                            type="submit" 
                                            :disabled="adjustmentForm.processing"
                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-lg px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-base font-medium text-white hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                        >
                                            {{ adjustmentForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="closeAdjustmentModal" 
                                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-600 shadow-sm px-4 py-2.5 bg-slate-700 text-base font-medium text-slate-200 hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:w-auto sm:text-sm transition-all"
                                        >
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
