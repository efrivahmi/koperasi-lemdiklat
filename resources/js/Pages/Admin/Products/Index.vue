<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    products: Object,
    allProducts: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'name_asc');
const showAdjustmentModal = ref(false);
const selectedProduct = ref(null);
const customReason = ref('');
const isLoading = ref(false);

// Watch sort changes
watch(sortBy, (newSort) => {
    router.get(route('products.index'), { search: search.value, sort: newSort }, { preserveState: true, replace: true });
});

const adjustmentForm = useForm({
    type: 'deduction',
    quantity: 1,
    purpose: 'other',
    notes: '',
    client_name: '',
});

// Expandable Rows Logic
const expandedRows = ref([]);
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
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
    if (!product.stock_ins_count || product.stock_ins_count === 0) {
        alert('Produk ini belum memiliki riwayat Barang Masuk (Stock In). Sila lakukan input stok awal melalui menu "Barang Masuk" terlebih dahulu.');
        return;
    }

    selectedProduct.value = product;
    adjustmentForm.reset();
    adjustmentForm.type = 'deduction';
    adjustmentForm.purpose = 'damage';
    adjustmentForm.client_name = '';
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
        if (!adjustmentForm.type) {
             alert('Silakan pilih Jenis Penyesuaian (Pengurangan atau Penambahan).');
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

const deleteProduct = (product) => {
    if (confirm(`Apakah Anda yakin ingin menghapus produk "${product.name}"?`)) {
        router.delete(route('products.destroy', product.id));
    }
};
</script>

<template>
    <Head title="Manajemen Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Produk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Manajemen Produk</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                
                <!-- Toolbar -->
                <div class="relative z-30 mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl p-4">
                    <TableToolbar
                        title="Daftar Produk"
                        description="Kelola data produk, harga, dan stok koperasi"
                        :search-term="filters.search"
                        search-route="products.index"
                        class="text-white"
                    >
                        <template #search-input>
                            <!-- Search Input (User can revert to simple input if SearchableSelect fails again, but trying here) -->
                             <SearchableSelect
                                v-if="allProducts"
                                :model-value="filters.search"
                                :options="allProducts"
                                placeholder="Cari produk..."
                                search-placeholder="Ketik nama atau barcode..."
                                label-key="name"
                                value-key="name"
                                @update:model-value="val => router.get(route('products.index'), { search: val }, { preserveState: true, replace: true })"
                                class="w-full"
                            />
                            <div v-else class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input 
                                    v-model="search"
                                    @keydown.enter="router.get(route('products.index'), { search: search }, { preserveState: true, replace: true })"
                                    type="text" 
                                    placeholder="Cari..." 
                                    class="w-full pl-10 pr-4 py-2 border border-slate-600 rounded-lg bg-slate-900 text-white focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                        </template>
                        <template #actions>
                            <Link v-if="can('products.create')" :href="route('products.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Produk
                            </Link>
                             <Link v-if="can('products.create')" :href="route('products.barcode-generator')" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg shadow-lg border border-white/10 transition-all duration-200 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Barcode Generator
                            </Link>
                        </template>
                    </TableToolbar>
                </div>

                <!-- Sort Filter Bar -->
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span class="text-sm text-slate-400 hidden sm:inline">Urutkan:</span>
                    </div>
                    <select 
                        v-model="sortBy"
                        class="appearance-none pl-3 pr-8 py-2 bg-slate-800/50 border border-white/10 rounded-lg text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer backdrop-blur-sm"
                    >
                        <option value="name_asc">Nama A - Z</option>
                        <option value="name_desc">Nama Z - A</option>
                        <option value="newest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="price_asc">Harga Termurah</option>
                        <option value="price_desc">Harga Termahal</option>
                        <option value="stock_asc">Stok Terkecil</option>
                        <option value="stock_desc">Stok Terbanyak</option>
                    </select>
                </div>

                <!-- Product Table -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-slate-300">
                            <thead class="text-sm font-semibold text-slate-300 uppercase bg-slate-900/50 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-6 py-4 tracking-wider w-12"></th>
                                    <th scope="col" class="px-6 py-4 tracking-wider">Produk</th>
                                    <th scope="col" class="px-6 py-4 tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-4 tracking-wider text-right">Harga Beli</th>
                                    <th scope="col" class="px-6 py-4 tracking-wider text-right">Harga Jual</th>
                                    <th scope="col" class="px-6 py-4 tracking-wider text-center">Stok</th>
                                    <th scope="col" class="px-6 py-4 tracking-wider text-center">Unit / Netto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <template v-for="product in products.data" :key="product.id">
                                    <!-- Main Row -->
                                    <tr 
                                        class="group hover:bg-slate-700/30 transition-colors duration-150 cursor-pointer"
                                        :class="{'bg-slate-700/20': expandedRows.includes(product.id)}"
                                        @click="toggleRow(product.id)"
                                    >
                                        <td class="px-6 py-4">
                                             <button 
                                                class="text-slate-400 hover:text-white transition-transform duration-200 focus:outline-none"
                                                :class="{'rotate-90': expandedRows.includes(product.id)}"
                                            >
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <img 
                                                        v-if="product.image_path" 
                                                        :src="`/storage/${product.image_path}`" 
                                                        class="h-12 w-12 rounded-lg object-cover border border-white/10 bg-slate-700"
                                                        alt="Product Image" 
                                                    />
                                                    <div v-else class="h-12 w-12 rounded-lg bg-slate-700 border border-white/10 flex items-center justify-center text-slate-500">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-base font-semibold text-white tracking-wide">{{ product.name }}</div>
                                                    <div class="text-sm text-indigo-300 font-mono mt-1 font-medium">{{ product.barcode || '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="product.category">
                                                <div v-if="product.category.parent" class="flex items-center text-sm text-slate-300 mb-1">
                                                    <span>{{ product.category.parent.name }}</span>
                                                    <svg class="w-4 h-4 mx-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                </div>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                                    {{ product.category.name }}
                                                </span>
                                            </div>
                                            <span v-else class="text-slate-400 italic text-sm">Tanpa Kategori</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-slate-300 text-base font-medium">{{ formatCurrency(product.harga_beli) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="text-amber-400 text-base font-bold font-mono tracking-tight">{{ formatCurrency(product.harga_jual) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span 
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold border"
                                                :class="product.stock <= 5 
                                                    ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' 
                                                    : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'"
                                            >
                                                {{ product.stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div>
                                                <div class="font-semibold text-white text-sm">{{ product.unit || '-' }}</div>
                                                <div v-if="product.netto" class="text-slate-400 text-xs mt-0.5">{{ product.netto }}</div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Expanded Details Row -->
                                    <tr v-if="expandedRows.includes(product.id)" class="bg-slate-800/80 border-b border-white/5">
                                        <td colspan="7" class="px-6 py-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fadeIn pl-16">
                                                <!-- Operations -->
                                                <div>
                                                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Tindakan</h4>
                                                    <div class="flex flex-wrap gap-2">
                                                        <Link v-if="can('products.show')" :href="route('products.show', product.id)" class="inline-flex items-center px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-medium rounded-md transition-colors shadow-sm ring-1 ring-white/10">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                            Detail
                                                        </Link>
                                                        <Link v-if="can('products.edit')" :href="route('products.edit', product.id)" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-md transition-colors shadow-sm ring-1 ring-white/10">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                            Edit
                                                        </Link>
                                                        <button 
                                                            v-if="can('products.stock')" 
                                                            @click.stop="openAdjustmentModal(product)"
                                                            class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-medium rounded-md transition-colors shadow-sm ring-1 ring-white/10"
                                                        >
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            Stok Adjustment
                                                        </button>
                                                        <button 
                                                            v-if="can('products.delete')" 
                                                            @click.stop="deleteProduct(product)" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-medium rounded-md transition-colors shadow-sm ring-1 ring-white/10"
                                                        >
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Info & Meta -->
                                                <div class="space-y-4">
                                                    <div v-if="product.description">
                                                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</h4>
                                                        <p class="text-sm text-slate-300 leading-relaxed">{{ product.description }}</p>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-white/5">
                                                        <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                                                        <AuditInfo :user="product.updater" :timestamp="product.updated_at" label="Diubah" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty State -->
                                <tr v-if="products.data.length === 0">
                                    <td colspan="5" class="px-6 py-12">
                                        <EmptyState
                                            title="Belum ada produk"
                                            description="Mulai tambahkan produk baru untuk inventaris Anda"
                                        >
                                            <template v-if="filters.search" #action>
                                                 <button @click="router.get(route('products.index'))" class="mt-4 text-indigo-400 hover:text-indigo-300 font-medium text-sm">
                                                    Bersihkan pencarian
                                                </button>
                                            </template>
                                        </EmptyState>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex items-center justify-between">
                         <div class="text-sm text-slate-400">
                            Menampilkan <span class="font-medium text-white">{{ products.from || 0 }}</span> sampai <span class="font-medium text-white">{{ products.to || 0 }}</span> dari <span class="font-medium text-white">{{ products.total }}</span> hasil
                        </div>
                        <div class="flex gap-1">
                             <template v-for="(link, k) in products.links" :key="k">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs font-medium rounded-md transition-colors"
                                    :class="[
                                        link.active 
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white border border-white/5',
                                        k !== 0 && k !== products.links.length - 1 ? 'hidden sm:inline-flex' : ''
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs font-medium rounded-md bg-slate-900/50 text-slate-600 border border-white/5 cursor-not-allowed"
                                    :class="[
                                        k !== 0 && k !== products.links.length - 1 ? 'hidden sm:inline-flex' : ''
                                    ]"
                                />
                             </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div v-if="showAdjustmentModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                                    
                                    <!-- Inline Validation Errors -->
                                    <div v-if="adjustmentForm.errors.quantity || adjustmentForm.errors.error || adjustmentForm.errors.notes" class="mb-4 bg-rose-900/40 border border-rose-500/40 text-rose-200 px-3 py-2 rounded-lg text-sm">
                                        <p v-if="adjustmentForm.errors.quantity">{{ adjustmentForm.errors.quantity }}</p>
                                        <p v-if="adjustmentForm.errors.notes">{{ adjustmentForm.errors.notes }}</p>
                                        <p v-if="adjustmentForm.errors.error">{{ adjustmentForm.errors.error }}</p>
                                    </div>

                                    <!-- Current Stock Info -->
                                    <div class="mb-4 p-3 bg-slate-900/50 rounded-lg border border-white/5">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-slate-400">Stok Saat Ini:</span>
                                            <span class="text-lg font-bold text-white">{{ selectedProduct?.stock || 0 }} {{ selectedProduct?.unit || 'Pcs' }}</span>
                                        </div>
                                    </div>
                                    
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

                                    <!-- Client Name -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Nama Klien / Tujuan Penyesuaian (Opsional)</label>
                                        <input 
                                            type="text" 
                                            v-model="adjustmentForm.client_name" 
                                            class="block w-full py-2.5 px-3 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                            placeholder="Contoh: Nama Siswa, Divisi, dll"
                                        >
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
