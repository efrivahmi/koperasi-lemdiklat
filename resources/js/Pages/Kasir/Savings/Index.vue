<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    savings: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const saverType = ref(props.filters.saver_type || '');

const applyFilters = () => {
    router.get(route('kasir.savings.index'), { search: search.value, type: type.value, saver_type: saverType.value }, { preserveState: true, replace: true });
};

watch([search, type, saverType], () => applyFilters());

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Data Tabungan" />
    <AuthenticatedLayout>
        <template #mobileTitle>Tabungan</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Tabungan</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
                        <h3 class="text-sm font-medium opacity-90">Total Setoran</h3>
                        <p class="text-3xl font-bold mt-2">{{ formatCurrency(stats?.total_deposits || 0) }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-red-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                        <h3 class="text-sm font-medium opacity-90">Total Penarikan</h3>
                        <p class="text-3xl font-bold mt-2">{{ formatCurrency(stats?.total_withdrawals || 0) }}</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="mb-6 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-800 dark:to-gray-800 border border-purple-200 dark:border-purple-500/30 rounded-lg shadow-sm p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input v-model="search" type="text" placeholder="Cari nama penabung..." class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" />
                        <select v-model="type" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            <option value="">Semua Jenis</option>
                            <option value="deposit">Setoran</option>
                            <option value="withdrawal">Penarikan</option>
                        </select>
                        <select v-model="saverType" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            <option value="">Semua Penabung</option>
                            <option value="App\\Models\\Student">Siswa</option>
                            <option value="App\\Models\\Teacher">Guru</option>
                        </select>
                        <Link v-if="can('savings.manage')" :href="route('kasir.savings.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Transaksi Baru
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Penabung</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jenis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Saldo Akhir</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diproses</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="saving in savings.data" :key="saving.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(saving.transaction_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ saving.saver?.user?.name || saving.saver?.name }}</div>
                                            <div class="text-xs text-gray-500">{{ saving.saver_type === 'App\\Models\\Student' ? 'Siswa' : 'Guru' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            <span :class="saving.type === 'deposit' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'" class="px-2 py-1 rounded-full font-semibold">
                                                {{ saving.type === 'deposit' ? 'Setoran' : 'Penarikan' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ saving.saver_type === 'App\\Models\\Student' ? 'Siswa' : 'Guru' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="saving.type === 'deposit' ? 'text-green-600' : 'text-red-600'">
                                            {{ saving.type === 'deposit' ? '+' : '-' }} {{ formatCurrency(saving.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ formatCurrency(saving.balance_after) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ saving.processed_by?.name || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="saving.creator" :timestamp="saving.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="saving.updater" :timestamp="saving.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <tr v-if="savings.data.length === 0">
                                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">Belum ada data tabungan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="savings.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in savings.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" :class="['px-3 py-2 rounded', link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300']" />
                                <span v-else v-html="link.label" :class="'px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-400 opacity-50 cursor-not-allowed'" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
