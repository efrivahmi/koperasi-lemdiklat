<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({ vouchers: Object, filters: Object });

const statusFilter = ref(props.filters.status || '');
const selectedVouchers = ref([]);

const filterByStatus = () => {
    router.get(route('kasir.vouchers.index'), { status: statusFilter.value }, { preserveState: true, replace: true });
};

const deleteVoucher = (id) => { if (confirm('Hapus voucher ini?')) router.delete(route('kasir.vouchers.destroy', id)); };
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const getStatusBadge = (s) => ({ 'available': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30', 'used': 'bg-blue-500/20 text-blue-300 border-blue-500/30' }[s] || 'bg-rose-500/20 text-rose-300 border-rose-500/30');
const getStatusText = (s) => ({ 'available': 'Tersedia', 'used': 'Terpakai', 'expired': 'Kadaluarsa' }[s] || s);
const printVoucher = (id) => window.open(route('kasir.vouchers.print', id), '_blank');

const toggleSelectVoucher = (id) => {
    const i = selectedVouchers.value.indexOf(id);
    if (i > -1) selectedVouchers.value.splice(i, 1);
    else selectedVouchers.value.push(id);
};

const toggleSelectAll = () => {
    const avail = props.vouchers.data.filter(v => v.status === 'available');
    selectedVouchers.value = selectedVouchers.value.length === avail.length ? [] : avail.map(v => v.id);
};

const printSelectedVouchers = () => {
    if (selectedVouchers.value.length === 0) { alert('Pilih minimal 1 voucher'); return; }
    const url = new URL(route('kasir.vouchers.print.batch'));
    selectedVouchers.value.forEach(id => url.searchParams.append('voucher_ids[]', id));
    window.open(url.toString(), '_blank');
    selectedVouchers.value = [];
};

const availableVouchersCount = computed(() => props.vouchers.data.filter(v => v.status === 'available').length);
</script>

<template>
    <Head title="Manajemen Voucher" />
    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Voucher Belanja</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Toolbar -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-xl p-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <div class="flex-1 flex flex-col sm:flex-row gap-4">
                            <div class="sm:w-48">
                                <select v-model="statusFilter" @change="filterByStatus" class="block w-full py-2.5 bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                    <option value="">Semua Status</option>
                                    <option value="available">Tersedia</option>
                                    <option value="used">Terpakai</option>
                                    <option value="expired">Kadaluarsa</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button v-if="selectedVouchers.length > 0" @click="printSelectedVouchers" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-500 hover:to-fuchsia-500 text-white font-semibold rounded-lg shadow-lg shadow-purple-500/20 transition-all text-sm">
                                🖨️ <span class="ml-1">Cetak Terpilih ({{ selectedVouchers.length }})</span>
                            </button>
                            <Link :href="route('kasir.vouchers.redeem.form')" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold rounded-lg shadow-lg shadow-emerald-500/20 transition-all text-sm">
                                ⚡ <span class="hidden sm:inline ml-1">Redeem Voucher</span><span class="sm:hidden ml-1">Redeem</span>
                            </Link>
                            <Link :href="route('kasir.vouchers.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                                ➕ <span class="hidden sm:inline ml-1">Generate Voucher</span><span class="sm:hidden ml-1">Generate</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState v-if="vouchers.data.length === 0 && !statusFilter" icon="ticket" title="Belum Ada Voucher" description="Generate voucher belanja pertama Anda." :action-url="route('kasir.vouchers.create')" action-text="Generate Voucher" />

                <!-- Table -->
                <div v-else class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-4 sm:p-6">
                        <div v-if="availableVouchersCount > 0" class="mb-4 flex justify-end items-center">
                            <div class="text-sm text-slate-400">
                                <button @click="toggleSelectAll" class="text-indigo-400 hover:text-indigo-300 font-medium">{{ selectedVouchers.length === availableVouchersCount ? 'Batalkan' : 'Pilih Semua' }}</button>
                                <span class="ml-2">({{ selectedVouchers.length }} terpilih)</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-300 uppercase">
                                            <input v-if="availableVouchersCount > 0" type="checkbox" @change="toggleSelectAll" :checked="selectedVouchers.length === availableVouchersCount && availableVouchersCount > 0" class="w-4 h-4 rounded border-slate-600 text-indigo-600 focus:ring-indigo-500 bg-slate-800" />
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Kode</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Nominal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Digunakan Oleh</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="v in vouchers.data" :key="v.id" :class="selectedVouchers.includes(v.id) ? 'bg-indigo-500/10' : 'hover:bg-white/5'" class="transition-colors">
                                        <td class="px-3 py-4 text-center">
                                            <input v-if="v.status === 'available'" type="checkbox" :value="v.id" v-model="selectedVouchers" class="w-4 h-4 rounded border-slate-600 text-indigo-600 focus:ring-indigo-500 bg-slate-800" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-white">{{ v.code }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-white">{{ formatCurrency(v.nominal) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadge(v.status)" class="px-2 py-1 text-xs font-semibold rounded-full border">{{ getStatusText(v.status) }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-slate-400">{{ v.student?.user?.name || '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="v.creator" :timestamp="v.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="v.updater" :timestamp="v.updated_at" label="Diubah" />
                                        </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <!-- Detail -->
                                                    <Link :href="route('kasir.vouchers.show', v.id)" class="text-sky-400 hover:text-sky-300 transition-colors">Detail</Link>
                                                    <!-- Edit -->
                                                    <Link :href="route('kasir.vouchers.edit', v.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</Link>
                                                    <!-- Print -->
                                                    <button @click="printVoucher(v.id)" class="text-purple-400 hover:text-purple-300 transition-colors">Cetak</button>
                                                    <!-- Delete -->
                                                    <button @click="deleteVoucher(v.id)" class="text-rose-400 hover:text-rose-300 transition-colors">Hapus</button>
                                                </div>
                                            </td>
                                    </tr>
                                    <tr v-if="vouchers.data.length === 0 && statusFilter">
                                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">Tidak ditemukan voucher dengan filter yang dipilih</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                            <p class="text-sm text-slate-400">
                                Menampilkan <span class="font-semibold text-slate-200">{{ vouchers.from || 0 }}</span> - <span class="font-semibold text-slate-200">{{ vouchers.to || 0 }}</span> dari <span class="font-semibold text-slate-200">{{ vouchers.total }}</span> voucher
                            </p>
                            <div v-if="vouchers.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                <template v-for="link in vouchers.links" :key="link.label">
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
