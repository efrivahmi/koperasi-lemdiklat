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
    if (status === 'available') return 'bg-green-100 text-green-800';
    if (status === 'used') return 'bg-blue-100 text-blue-800';
    return 'bg-red-100 text-red-800';
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
    <Head title="Voucher" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Voucher</h2>
                <div class="flex gap-2">
                    <button
                        v-if="selectedVouchers.length > 0"
                        @click="printSelectedVouchers"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak {{ selectedVouchers.length }} Voucher
                    </button>
                    <Link :href="route('vouchers.redeem.form')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Redeem Voucher
                    </Link>
                    <Link :href="route('vouchers.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Generate Voucher
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Empty State -->
                <EmptyState
                    v-if="vouchers.data.length === 0 && !statusFilter"
                    icon="tag"
                    title="Belum Ada Voucher"
                    description="Generate voucher pertama untuk top-up saldo siswa."
                    :action-url="route('vouchers.create')"
                    action-text="Generate Voucher Pertama"
                />

                <!-- Table with Data or Filter Results -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex justify-between items-center">
                            <div>
                                <label for="status-filter" class="sr-only">Filter Status Voucher</label>
                                <select
                                    id="status-filter"
                                    v-model="statusFilter"
                                    @change="filterByStatus"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    aria-label="Filter status voucher"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="available">Available</option>
                                    <option value="used">Used</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>

                            <div v-if="availableVouchersCount > 0" class="text-sm text-gray-600">
                                <button
                                    @click="toggleSelectAll"
                                    class="text-indigo-600 hover:text-indigo-800 font-medium"
                                >
                                    {{ selectedVouchers.length === availableVouchersCount ? 'Batalkan Semua' : 'Pilih Semua Available' }}
                                </button>
                                <span class="ml-2">
                                    ({{ selectedVouchers.length }} terpilih)
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                            <input
                                                v-if="availableVouchersCount > 0"
                                                type="checkbox"
                                                @change="toggleSelectAll"
                                                :checked="selectedVouchers.length === availableVouchersCount && availableVouchersCount > 0"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nominal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Digunakan Oleh</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden xl:table-cell">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="voucher in vouchers.data" :key="voucher.id"
                                        :class="selectedVouchers.includes(voucher.id) ? 'bg-indigo-50' : ''">
                                        <td class="px-3 py-4 text-center">
                                            <input
                                                v-if="voucher.status === 'available'"
                                                type="checkbox"
                                                :checked="selectedVouchers.includes(voucher.id)"
                                                @change="toggleSelectVoucher(voucher.id)"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ voucher.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ formatCurrency(voucher.nominal) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadge(voucher.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ voucher.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ voucher.student?.user?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="voucher.creator" :timestamp="voucher.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="voucher.updater" :timestamp="voucher.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    v-if="voucher.status === 'available'"
                                                    @click="printVoucher(voucher.id)"
                                                    class="text-purple-600 hover:text-purple-900 flex items-center gap-1"
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
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="vouchers.data.length === 0 && statusFilter">
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500">Tidak ditemukan voucher dengan filter yang dipilih</p>
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
