<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    adjustments: Object,
    summary: Object,
    products: Array,
    adjusters: Array,
    filters: Object,
    error: String,
    printItems: Array,
});

// Smart customer name logic
const uniqueCustomerNames = computed(() => {
    if (!props.adjustments?.data) return [];
    const names = props.adjustments.data
        .map(item => item.client_name && item.client_name.trim() !== '' ? item.client_name : '-');
    return [...new Set(names)];
});

const hasMultipleCustomers = computed(() => {
    const named = uniqueCustomerNames.value.filter(n => n !== '-');
    return new Set(named).size > 1;
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    product_id: props.filters?.product_id || '',
    type: props.filters?.type || '',
    adjusted_by: props.filters?.adjusted_by || '',
    search: props.filters?.search || '',
    client_name: props.filters?.client_name || '',
});

const applyFilters = () => {
    router.get(route(route().current()), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchForm.value = {
        date_from: '',
        date_to: '',
        product_id: '',
        type: '',
        adjusted_by: '',
        search: '',
        client_name: '',
    };
    applyFilters();
};

const exportToExcel = () => {
    const params = new URLSearchParams(searchForm.value);
    window.location.href = route(route().current() + '.export') + '?' + params.toString();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTypeBadge = (type) => {
    if (type === 'addition') {
        return 'bg-emerald-900/50 text-emerald-200 border border-emerald-700/50';
    }
    return 'bg-rose-900/50 text-rose-200 border border-rose-700/50';
};

const getTypeText = (type) => {
    if (type === 'addition') {
        return 'Penambahan';
    }
    return 'Pengurangan';
};

const getTypeIcon = (type) => {
    if (type === 'addition') {
        return '➕';
    }
    return '➖';
};

const getPurposeLabel = (purpose) => {
    const labels = {
        'sale': 'Penjualan (Transaksi)',
        'internal_use': 'Keperluan Internal/Kantor',
        'personal_use': 'Keperluan Pribadi',
        'damage': 'Kerusakan Barang',
        'expired': 'Barang Kadaluarsa',
        'return_to_supplier': 'Retur ke Supplier',
        'other': 'Lainnya'
    };
    return labels[purpose] || 'Tidak Diketahui';
};

const getPurposeBadge = (purpose) => {
    const badges = {
        'sale': 'bg-emerald-900/50 text-emerald-200 border border-emerald-700/50',
        'internal_use': 'bg-amber-900/50 text-amber-200 border border-amber-700/50',
        'personal_use': 'bg-purple-900/50 text-purple-200 border border-purple-700/50',
        'damage': 'bg-rose-900/50 text-rose-200 border border-rose-700/50',
        'expired': 'bg-red-900/50 text-red-200 border border-red-700/50',
        'return_to_supplier': 'bg-blue-900/50 text-blue-200 border border-blue-700/50',
        'other': 'bg-slate-700/50 text-slate-200 border border-slate-600/50'
    };
    return badges[purpose] || 'bg-slate-700/50 text-slate-200 border border-slate-600/50';
};

import ThermalPrintLayout from '@/Components/ThermalPrintLayout.vue';

const showEditModal = ref(false);
const editingAdjustment = ref(null);
const editForm = useForm({
    id: null,
    quantity: 1,
    type: 'deduction',
    purpose: 'other',
    notes: '',
    client_name: '',
});

const openEditModal = (adjustment) => {
    editingAdjustment.value = adjustment;
    editForm.id = adjustment.id;
    editForm.quantity = adjustment.quantity_adjusted;
    editForm.type = adjustment.type;
    editForm.purpose = adjustment.purpose;
    editForm.notes = adjustment.notes || '';
    editForm.client_name = adjustment.client_name || '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingAdjustment.value = null;
    editForm.reset();
};

const updateAdjustment = () => {
    editForm.put(route('stock-adjustments.update', editForm.id), {
        onSuccess: () => closeEditModal(),
    });
};

const deleteAdjustment = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data penyesuaian ini? Stok akan dikembalikan ke kondisi sebelum penyesuaian.')) {
        router.delete(route('stock-adjustments.destroy', id), {
             preserveScroll: true,
        });
    }
};

const printThermal = () => {
    window.print();
};
</script>

<template>
    <Head title="Laporan Penyesuaian Stok" />

    <AuthenticatedLayout>
        <template #mobileTitle>Laporan</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Laporan Penyesuaian Stok</h2>
        </template>

        <!-- Galaxy Theme Container -->
        <!-- Removed conflicting background to use AuthenticatedLayout's theme -->
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

                <!-- Flash Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-6 bg-emerald-900/30 border border-emerald-500/30 text-emerald-200 px-4 py-3 rounded-lg relative backdrop-blur-sm">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline"> {{ $page.props.flash.success }}</span>
                </div>

                <!-- Error Message -->
                <div v-if="error || $page.props.errors?.error" class="mb-6 bg-rose-900/30 border border-rose-500/30 text-rose-200 px-4 py-3 rounded-lg relative backdrop-blur-sm">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"> {{ error || $page.props.errors?.error }}</span>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Total Adjustments -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-lg p-6 relative overflow-hidden group hover:bg-slate-800/60 transition-all">
                        <div class="absolute -right-6 -top-6 rounded-full w-24 h-24 bg-indigo-500/20 blur-xl group-hover:bg-indigo-500/30 transition-all"></div>
                        <div class="relative flex items-center justify-between z-10">
                            <div>
                                <p class="text-indigo-300 text-sm font-medium">Total Penyesuaian</p>
                                <h3 class="text-3xl font-bold text-white mt-1">{{ summary.total_adjustments }}</h3>
                                <p class="text-slate-400 text-xs mt-1">Periode ini</p>
                            </div>
                            <div class="text-4xl">📦</div>
                        </div>
                    </div>

                    <!-- Total Additions -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-lg p-6 relative overflow-hidden group hover:bg-slate-800/60 transition-all">
                        <div class="absolute -right-6 -top-6 rounded-full w-24 h-24 bg-emerald-500/20 blur-xl group-hover:bg-emerald-500/30 transition-all"></div>
                        <div class="relative flex items-center justify-between z-10">
                            <div>
                                <p class="text-emerald-300 text-sm font-medium">Total Penambahan</p>
                                <h3 class="text-3xl font-bold text-white mt-1">{{ summary.total_additions }}</h3>
                                <p class="text-slate-400 text-xs mt-1">{{ summary.additions_count }} transaksi</p>
                            </div>
                            <div class="text-4xl text-emerald-400">➕</div>
                        </div>
                    </div>

                    <!-- Total Deductions -->
                    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-lg p-6 relative overflow-hidden group hover:bg-slate-800/60 transition-all">
                        <div class="absolute -right-6 -top-6 rounded-full w-24 h-24 bg-rose-500/20 blur-xl group-hover:bg-rose-500/30 transition-all"></div>
                        <div class="relative flex items-center justify-between z-10">
                            <div>
                                <p class="text-rose-300 text-sm font-medium">Total Pengurangan</p>
                                <h3 class="text-3xl font-bold text-white mt-1">{{ summary.total_deductions }}</h3>
                                <p class="text-slate-400 text-xs mt-1">{{ summary.deductions_count }} transaksi</p>
                            </div>
                            <div class="text-4xl text-rose-400">➖</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-xl mb-6 p-4 sm:p-6">
                    <h3 class="font-semibold text-lg text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        Filter Laporan
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" />
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" />
                        </div>

                        <!-- Product -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Produk</label>
                            <select v-model="searchForm.product_id" class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all">
                                <option value="">Semua Produk</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Tipe Penyesuaian</label>
                            <select v-model="searchForm.type" class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all">
                                <option value="">Semua Tipe</option>
                                <option value="addition">➕ Penambahan</option>
                                <option value="deduction">➖ Pengurangan</option>
                            </select>
                        </div>

                        <!-- Adjusted By -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Disesuaikan Oleh</label>
                            <select v-model="searchForm.adjusted_by" class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all">
                                <option value="">Semua User</option>
                                <option v-for="adjuster in adjusters" :key="adjuster.id" :value="adjuster.id">
                                    {{ adjuster.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Cari Produk</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama atau barcode..." class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" @keyup.enter="applyFilters" />
                        </div>

                        <!-- Client Name Filter -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nama Pelanggan</label>
                            <input type="text" v-model="searchForm.client_name" placeholder="Cari nama pelanggan..." class="w-full bg-slate-900/60 border-slate-600 text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" @keyup.enter="applyFilters" />
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 mt-4 pt-4 border-t border-white/5">
                        <button @click="applyFilters" class="inline-flex items-center justify-center px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium text-sm transition-all shadow-lg hover:shadow-indigo-500/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            Tampilkan
                        </button>
                        <button @click="resetFilters" class="inline-flex items-center justify-center px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium text-sm transition-all border border-slate-600">
                            Reset
                        </button>
                        <div class="flex-1"></div>
                        <button v-if="can('reports.print')" @click="printThermal" class="inline-flex items-center justify-center px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg font-medium text-sm transition-all shadow-lg hover:shadow-sky-500/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Print Thermal
                        </button>
                        <button v-if="can('reports.export')" @click="exportToExcel" class="inline-flex items-center justify-center px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-medium text-sm transition-all shadow-lg hover:shadow-emerald-500/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tipe/Tujuan</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Harga Jual</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Oleh</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Catatan</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-transparent">
                                <tr v-if="adjustments.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <p class="text-base font-medium text-slate-400">Tidak ada data penyesuaian stok</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="adjustment in adjustments.data" :key="adjustment.id" class="hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3 text-sm text-slate-300 whitespace-nowrap">
                                        {{ formatDate(adjustment.created_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-bold text-white">{{ adjustment.product?.name || '-' }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ adjustment.product?.barcode }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span :class="getTypeBadge(adjustment.type)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit">
                                                <span class="mr-1">{{ getTypeIcon(adjustment.type) }}</span>
                                                {{ getTypeText(adjustment.type) }}
                                            </span>
                                            <span :class="getPurposeBadge(adjustment.purpose)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit">
                                                {{ getPurposeLabel(adjustment.purpose) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="text-sm font-bold" :class="adjustment.type === 'addition' ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ adjustment.type === 'addition' ? '+' : '-' }}{{ adjustment.quantity_adjusted }}
                                        </div>
                                        <div class="text-xs text-slate-500">Stok: {{ adjustment.quantity_before }} -> {{ adjustment.quantity_after }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-sm font-semibold text-amber-400">
                                            {{ formatCurrency(adjustment.product?.harga_jual ?? 0) }}
                                        </div>
                                        <div class="text-xs text-slate-500">Harga Jual</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-indigo-500/50 flex items-center justify-center text-xs font-bold text-white border border-white/10">
                                                {{ adjustment.adjusted_by?.name ? adjustment.adjusted_by.name.charAt(0).toUpperCase() : '?' }}
                                            </div>
                                            <span class="truncate max-w-[100px]">{{ adjustment.adjusted_by?.name || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-400 max-w-[150px] truncate" :title="adjustment.notes">
                                        {{ adjustment.notes || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button v-if="can('reports.stock_adjustments.edit')" @click="openEditModal(adjustment)" class="text-indigo-400 hover:text-white transition-colors" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button v-if="can('reports.stock_adjustments.delete')" @click="deleteAdjustment(adjustment.id)" class="text-rose-400 hover:text-rose-300 transition-colors" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="adjustments.data.length > 0" class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex items-center justify-between">
                        <div class="text-sm text-slate-500">
                            Showing <span class="font-medium text-white">{{ adjustments.from }}</span> to <span class="font-medium text-white">{{ adjustments.to }}</span> of <span class="font-medium text-white">{{ adjustments.total }}</span>
                        </div>
                        <div class="flex gap-1">
                            <template v-for="(link, index) in adjustments.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1.5 rounded-md text-sm font-medium transition-all',
                                        link.active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white border border-white/5'
                                    ]"
                                    v-html="link.label"
                                    preserve-state
                                    preserve-scroll
                                />
                                <span v-else :class="'px-3 py-1.5 rounded-md text-sm font-medium bg-slate-900/50 text-slate-600 border border-white/5 cursor-not-allowed'" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Adjustment Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeEditModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10 ring-1 ring-white/5">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-xl leading-6 font-bold text-white mb-1" id="modal-title">
                                    Edit Penyesuaian Stok
                                </h3>
                                <p class="text-sm text-slate-400 mb-6">Produk: <span class="text-indigo-400 font-semibold">{{ editingAdjustment?.product?.name }}</span></p>

                                <form @submit.prevent="updateAdjustment">
                                    
                                    <!-- Inline Validation Errors -->
                                    <div v-if="editForm.errors.quantity || editForm.errors.error || editForm.errors.notes" class="mb-4 bg-rose-900/40 border border-rose-500/40 text-rose-200 px-3 py-2 rounded-lg text-sm">
                                        <p v-if="editForm.errors.quantity">{{ editForm.errors.quantity }}</p>
                                        <p v-if="editForm.errors.notes">{{ editForm.errors.notes }}</p>
                                        <p v-if="editForm.errors.error">{{ editForm.errors.error }}</p>
                                    </div>

                                    <!-- Current Stock Info -->
                                    <div class="mb-4 p-3 bg-slate-900/50 rounded-lg border border-white/5">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-slate-400">Stok Saat Ini:</span>
                                            <span class="text-lg font-bold text-white">{{ editingAdjustment?.product?.stock || 0 }} {{ editingAdjustment?.product?.unit || 'Pcs' }}</span>
                                        </div>
                                    </div>

                                    <!-- Purpose Selection -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Alasan Penyesuaian</label>
                                        <select v-model="editForm.purpose" class="block w-full pl-3 pr-10 py-2.5 text-base bg-slate-900/70 border-slate-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg text-white">
                                            <option value="damage">Kerusakan Barang (Pengurangan - Rugi)</option>
                                            <option value="expired">Barang Kadaluarsa (Pengurangan - Rugi)</option>
                                            <option value="internal_use">Keperluan Internal/Kantor (Pengurangan - Rugi/Biaya)</option>
                                            <option value="personal_use">Keperluan Pribadi (Pengurangan - Prive)</option>
                                            <option value="return_to_supplier">Retur ke Supplier (Pengurangan - Refund)</option>
                                            <option value="sale">Penjualan Manual/Langsung (Pengurangan - Profit)</option>
                                            <option value="other">Lainnya (Custom)</option>
                                        </select>
                                    </div>

                                    <!-- Type Selection -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Jenis Penyesuaian</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label 
                                                class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                                :class="[
                                                    editForm.type === 'deduction' ? 'bg-rose-900/30 border-rose-500/50 text-rose-200' : 'bg-slate-900/50 border-slate-700 text-slate-400 hover:bg-slate-800',
                                                    editForm.purpose !== 'other' ? 'opacity-50 cursor-not-allowed' : ''
                                                ]"
                                            >
                                                <input type="radio" v-model="editForm.type" value="deduction" class="sr-only" :disabled="editForm.purpose !== 'other'">
                                                <span class="block font-semibold">Pengurangan (-)</span>
                                            </label>
                                            <label 
                                                class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                                :class="[
                                                    editForm.type === 'addition' ? 'bg-emerald-900/30 border-emerald-500/50 text-emerald-200' : 'bg-slate-900/50 border-slate-700 text-slate-400 hover:bg-slate-800',
                                                    editForm.purpose !== 'other' ? 'opacity-50 cursor-not-allowed' : ''
                                                ]"
                                            >
                                                <input type="radio" v-model="editForm.type" value="addition" class="sr-only" :disabled="editForm.purpose !== 'other'">
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
                                                v-model="editForm.quantity" 
                                                min="1" 
                                                class="block w-full py-2.5 pr-12 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-lg font-bold" 
                                                required
                                            >
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 sm:text-sm">{{ editingAdjustment?.product?.unit || 'Pcs' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Client Name Input -->
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Nama Klien / Tujuan Penyesuaian (Opsional)</label>
                                        <input 
                                            type="text" 
                                            v-model="editForm.client_name" 
                                            class="block w-full py-2.5 px-3 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                            placeholder="Contoh: Nama Siswa, Divisi, dll"
                                        >
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-slate-300 mb-2">Catatan Tambahan (Opsional)</label>
                                        <textarea 
                                            v-model="editForm.notes" 
                                            rows="2" 
                                            class="block w-full py-2 bg-slate-900/70 border-slate-600 rounded-lg text-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm placeholder-slate-500"
                                            placeholder="Detail tambahan..."
                                        ></textarea>
                                    </div>

                                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                        <button 
                                            type="submit" 
                                            :disabled="editForm.processing"
                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-lg px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-base font-medium text-white hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                        >
                                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="closeEditModal" 
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

        <!-- Thermal Print Layout (Hidden on Screen) -->
        <ThermalPrintLayout
            title="LAPORAN PENYESUAIAN STOK"
            :periode="`${filters?.date_from || 'Awal'} s/d ${filters?.date_to || 'Hari Ini'}`"
            :user="$page.props.auth.user"
        >
            <!-- Customer Name Section -->
            <div style="margin-bottom: 8px; border-bottom: 1px dashed black; padding-bottom: 6px;">
                <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">INFORMASI PELANGGAN:</div>
                <div v-for="(name, idx) in uniqueCustomerNames" :key="'cust-' + idx" style="font-size: 11px; margin-bottom: 2px;">
                    • {{ name }}
                </div>
            </div>

            <!-- Detail Items -->
            <div style="font-weight: bold; font-size: 12px; text-align: center; margin-bottom: 5px;">DETAIL ITEM</div>
            <div style="border-bottom: 1px dashed black; margin-bottom: 6px;"></div>

            <div v-for="(item, index) in printItems" :key="'print-' + item.id" style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed #000;">
                <!-- Product Name -->
                <div style="font-weight: bold; font-size: 13px; margin-bottom: 3px;">
                    {{ index + 1 }}. {{ item.product?.name || '-' }}
                </div>

                <!-- Customer Name -->
                <div style="font-size: 12px; margin-bottom: 3px;">
                    Pelanggan: {{ item.client_name || '-' }}
                </div>

                <!-- Harga Jual line -->
                <div style="font-size: 10px; margin-bottom: 2px;">
                    HJ: {{ formatCurrency(item.product?.harga_jual ?? 0) }} | HB: {{ formatCurrency(item.product?.harga_beli ?? 0) }}
                </div>
                <!-- Quantity x Harga Jual = Amount -->
                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: bold;">
                    <span>{{ Math.abs(item.quantity_adjusted) }} x {{ formatCurrency(item.product?.harga_jual ?? 0) }}</span>
                    <span>{{ formatCurrency(Math.abs(item.quantity_adjusted) * (item.product?.harga_jual ?? 0)) }}</span>
                </div>
            </div>

            <!-- Grand Total -->
            <div style="margin-top: 8px; border-top: 2px solid black; padding-top: 6px;">
                <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: bold;">
                    <span>GRAND TOTAL:</span>
                    <span>{{ formatCurrency((printItems || []).reduce((sum, item) => sum + (Math.abs(item.quantity_adjusted) * (item.product?.harga_jual ?? 0)), 0)) }}</span>
                </div>
            </div>
        </ThermalPrintLayout>
    </AuthenticatedLayout>
</template>

