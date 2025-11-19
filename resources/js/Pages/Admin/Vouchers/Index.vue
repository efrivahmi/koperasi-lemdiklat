<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    vouchers: Object,
    filters: Object,
});

const statusFilter = ref(props.filters.status || '');

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
</script>

<template>
    <Head title="Voucher" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Voucher</h2>
                <div class="flex gap-2">
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
                        <div class="mb-4">
                            <select
                                v-model="statusFilter"
                                @change="filterByStatus"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Semua Status</option>
                                <option value="available">Available</option>
                                <option value="used">Used</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nominal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Digunakan Oleh</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="voucher in vouchers.data" :key="voucher.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ voucher.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ formatCurrency(voucher.nominal) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadge(voucher.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ voucher.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ voucher.student ? voucher.student.user.name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button v-if="voucher.status === 'available'" @click="deleteVoucher(voucher.id)" class="text-red-600 hover:text-red-900">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="vouchers.data.length === 0 && statusFilter">
                                        <td colspan="5" class="px-6 py-12 text-center">
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
                            <Link
                                v-for="link in vouchers.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 rounded',
                                    link.active ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
