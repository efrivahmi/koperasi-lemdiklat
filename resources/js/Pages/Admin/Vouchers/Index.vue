<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    vouchers: Object,
    filters: Object,
});

const statusFilter = ref(props.filters.status || '');
const selectedVouchers = ref([]);

const filterByStatus = () => {
    router.get(route('vouchers.index'), { status: statusFilter.value }, {
        preserveState: true,
        replace: true,
    });
};

const deleteVoucher = (id) => {
    if (confirm('Hapus voucher ini?')) {
        router.delete(route('vouchers.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getStatusBadge = (status) => {
    if (status === 'available') return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    if (status === 'used') return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
    return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
};

const printVoucher = (voucherId) => {
    window.open(route('vouchers.print', voucherId), '_blank');
};

const toggleSelectVoucher = (voucherId) => {
    const index = selectedVouchers.value.indexOf(voucherId);
    if (index > -1) {
        selectedVouchers.value.splice(index, 1);
    } else {
        selectedVouchers.value.push(voucherId);
    }
};

const toggleSelectAll = () => {
    const availableVouchers = props.vouchers.data.filter(v => v.status === 'available');
    if (selectedVouchers.value.length === availableVouchers.length) {
        selectedVouchers.value = [];
    } else {
        selectedVouchers.value = availableVouchers.map(v => v.id);
    }
};

const printSelectedVouchers = () => {
    if (selectedVouchers.value.length === 0) {
        alert('Pilih minimal 1 voucher untuk dicetak');
        return;
    }

    router.post(route('vouchers.print.batch'), {
        voucher_ids: selectedVouchers.value
    }, {
        onSuccess: () => {
            // Open in new window after successful validation
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = route('vouchers.print.batch');
            form.target = '_blank';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            selectedVouchers.value.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'voucher_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            selectedVouchers.value = [];
        }
    });
};

const availableVouchersCount = computed(() => {
    return props.vouchers.data.filter(v => v.status === 'available').length;
});
</script>

<template>
    <Head title="Manajemen Voucher" />

    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Voucher Belanja</h2>
        </template>

        <div class="py-6 sm:py-12 bg-gray-100 dark:bg-slate-900 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <div class="mb-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-sm p-4 transition-colors">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <!-- Search & Filter -->
                        <div class="flex-1 flex flex-col sm:flex-row gap-4">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari kode voucher..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors"
                                />
                            </div>
                            <div class="sm:w-48">
                                <select 
                                    v-model="statusFilter" 
                                    @change="filterByStatus"
                                    class="block w-full py-2.5 pl-3 pr-10 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="available">Tersedia</option>
                                    <option value="used">Terpakai</option>
                                    <option value="expired">Kadaluarsa</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-if="can('vouchers.print') && selectedVouchers.length > 0"
                                @click="printSelectedVouchers"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold rounded-lg shadow-sm transition-colors"
                            >
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2-4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span class="hidden sm:inline">Cetak ({{ selectedVouchers.length }})</span>
                            </button>
                            
                            <Link v-if="can('vouchers.redeem')" :href="route('vouchers.redeem.form')" class="inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="hidden sm:inline">Redeem Voucher</span>
                                <span class="sm:hidden">Redeem</span>
                            </Link>

                            <Link v-if="can('vouchers.create')" :href="route('vouchers.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Generate Voucher</span>
                                <span class="sm:hidden">Generate</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="vouchers.data.length === 0 && !search && !statusFilter"
                    icon="ticket"
                    title="Belum Ada Voucher"
                    description="Generate voucher belanja pertama Anda untuk siswa."
                    :action-url="route('vouchers.create')"
                    action-text="Generate Voucher"
                />

                <!-- Table with Data -->
                <div v-else class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
                    <div class="p-6 text-slate-900 dark:text-white">
                        <div v-if="availableVouchersCount > 0" class="mb-4 flex justify-end items-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <button
                                    @click="toggleSelectAll"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                                >
                                    {{ selectedVouchers.length === availableVouchersCount ? 'Batalkan Semua' : 'Pilih Semua Available' }}
                                </button>
                                <span class="ml-2">
                                    ({{ selectedVouchers.length }} terpilih)
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            <input
                                                v-if="availableVouchersCount > 0"
                                                type="checkbox"
                                                @change="toggleSelectAll"
                                                :checked="selectedVouchers.length === availableVouchersCount && availableVouchersCount > 0"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nominal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Digunakan Oleh</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="voucher in vouchers.data" :key="voucher.id"
                                        :class="selectedVouchers.includes(voucher.id) ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700'">
                                        <td class="px-3 py-4 text-center">
                                            <input
                                                v-if="voucher.status === 'available'"
                                                type="checkbox"
                                                :checked="selectedVouchers.includes(voucher.id)"
                                                @change="toggleSelectVoucher(voucher.id)"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">{{ voucher.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(voucher.nominal) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadge(voucher.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ voucher.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ voucher.student?.user?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            <AuditInfo :user="voucher.creator" :timestamp="voucher.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            <AuditInfo :user="voucher.updater" :timestamp="voucher.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    v-if="voucher.status === 'available'"
                                                    @click="printVoucher(voucher.id)"
                                                    class="text-purple-600 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300 flex items-center gap-1"
                                                    title="Cetak Voucher"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                    Cetak
                                                </button>
                                                <button
                                                    v-if="voucher.status === 'available'"
                                                    @click="deleteVoucher(voucher.id)"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="vouchers.data.length === 0 && statusFilter">
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ditemukan voucher dengan filter yang dipilih</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="vouchers.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in vouchers.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded',
                                        link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
