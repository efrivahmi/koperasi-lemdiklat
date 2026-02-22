<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({ savings: Object, filters: Object, stats: Object });

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const saverType = ref(props.filters.saver_type || '');

const applyFilters = () => {
    router.get(route('savings.index'), { search: search.value, type: type.value, saver_type: saverType.value }, { preserveState: true, replace: true });
};

watch([search, type, saverType], () => applyFilters());

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Data Tabungan" />
    <AuthenticatedLayout>
        <template #mobileTitle>Tabungan</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Data Tabungan</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-emerald-500/10">
                            <h3 class="text-sm font-medium text-emerald-300">Total Setoran</h3>
                            <p class="text-3xl font-bold text-white mt-2">{{ formatCurrency(stats?.total_deposits || 0) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-rose-500/10">
                            <h3 class="text-sm font-medium text-rose-300">Total Penarikan</h3>
                            <p class="text-3xl font-bold text-white mt-2">{{ formatCurrency(stats?.total_withdrawals || 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-xl p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <input v-model="search" type="text" placeholder="Cari nama penabung..." class="bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm placeholder-slate-400" />
                        <select v-model="type" class="bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            <option value="">Semua Jenis</option>
                            <option value="deposit">Setoran</option>
                            <option value="withdrawal">Penarikan</option>
                        </select>
                        <select v-model="saverType" class="bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                            <option value="">Semua Penabung</option>
                            <option value="App\Models\Student">Siswa</option>
                            <option value="App\Models\Teacher">Guru</option>
                        </select>
                        <Link v-if="can('savings.manage')" :href="route('savings.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Transaksi Baru
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-4 sm:p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Penabung</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jenis</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo Akhir</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Diproses</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="saving in savings.data" :key="saving.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ formatDate(saving.transaction_date) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ saving.saver?.user?.name || saving.saver?.name }}</div>
                                            <div class="text-xs text-slate-400">{{ saving.saver_type === 'App\\Models\\Student' ? 'Siswa' : 'Guru' }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span :class="saving.type === 'deposit' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30'" class="px-2 py-1 text-xs font-semibold rounded-full border">
                                                {{ saving.type === 'deposit' ? 'Setoran' : 'Penarikan' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ saving.saver_type === 'App\\Models\\Student' ? 'Siswa' : 'Guru' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-bold" :class="saving.type === 'deposit' ? 'text-emerald-300' : 'text-rose-300'">
                                            {{ saving.type === 'deposit' ? '+' : '-' }} {{ formatCurrency(saving.amount) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-white">{{ formatCurrency(saving.balance_after) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">{{ saving.processed_by?.name || '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="saving.creator" :timestamp="saving.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="saving.updater" :timestamp="saving.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <tr v-if="savings.data.length === 0">
                                        <td colspan="9" class="px-6 py-12 text-center text-slate-500">Belum ada data tabungan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                            <p class="text-sm text-slate-400">
                                Menampilkan <span class="font-semibold text-slate-200">{{ savings.from || 0 }}</span> - <span class="font-semibold text-slate-200">{{ savings.to || 0 }}</span> dari <span class="font-semibold text-slate-200">{{ savings.total }}</span> tabungan
                            </p>
                            <div v-if="savings.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                <template v-for="link in savings.links" :key="link.label">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" :class="['px-3 py-2 rounded-lg text-sm transition-all', link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border border-white/10 hover:bg-slate-700 text-slate-300']" />
                                    <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm bg-slate-800/50 text-slate-500 opacity-50 cursor-not-allowed" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
