<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({ transactions: Object, filters: Object, stats: Object });

const searchForm = ref({
    search: props.filters?.search || '', type: props.filters?.type || '',
    transaction_method: props.filters?.transaction_method || '',
    date_from: props.filters?.date_from || '', date_to: props.filters?.date_to || '',
});

const search = () => router.get(route('transactions.index'), searchForm.value, { preserveScroll: true });
const resetFilters = () => { searchForm.value = { search: '', type: '', transaction_method: '', date_from: '', date_to: '' }; search(); };

const getMethodBadge = (m) => ({ 'manual': 'bg-blue-500/20 text-blue-300 border-blue-500/30', 'rfid': 'bg-purple-500/20 text-purple-300 border-purple-500/30', 'barcode': 'bg-orange-500/20 text-orange-300 border-orange-500/30', 'voucher': 'bg-amber-500/20 text-amber-300 border-amber-500/30', 'system': 'bg-slate-500/20 text-slate-300 border-slate-500/30' }[m] || 'bg-slate-500/20 text-slate-300 border-slate-500/30');
const getMethodIcon = (m) => ({ 'manual': '✍️', 'rfid': '📡', 'barcode': '📊', 'voucher': '🎫', 'system': '⚙️' }[m] || '📝');
const getMethodText = (m) => ({ 'manual': 'Manual', 'rfid': 'RFID', 'barcode': 'Barcode', 'voucher': 'Voucher', 'system': 'System' }[m] || m);
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const getTypeIcon = (t) => ({ 'topup': '↑', 'redeem': '↑', 'return': '↑', 'purchase': '↓', 'debit': '↓' }[t] || '•');
const getTypeColor = (t) => ['topup', 'redeem', 'return'].includes(t) ? 'text-emerald-300' : 'text-rose-300';
const getTypeBadge = (t) => ['topup', 'redeem', 'return'].includes(t) ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30';
const getTypeText = (t) => ({ 'topup': 'Top-up', 'purchase': 'Pembelian', 'redeem': 'Voucher', 'return': 'Return', 'debit': 'Debit' }[t] || t);
const getReferenceIcon = (t) => ({ 'sale': '🛒', 'topup': '💰', 'voucher': '🎫' }[t] || '📝');
const getReferenceText = (t) => ({ 'sale': 'Pembelian', 'topup': 'Top-up', 'voucher': 'Voucher' }[t] || t);

const printReceipt = (t) => {
    const w = window.open('', '_blank', 'width=300,height=500');
    w.document.write(`<!DOCTYPE html><html><head><title>Receipt</title><style>body{font:10px monospace;margin:0;padding:8px;width:270px;text-align:center}.line{border-top:1px dashed #000;margin:4px 0}.left{text-align:left}.right{text-align:right}.bold{font-weight:bold}table{width:100%}td{padding:1px 0}</style></head><body><div class="bold">KOPERASI LEMDIKLAT</div><div class="line"></div><table><tr><td class="left">Tanggal</td><td class="right">${new Date(t.created_at).toLocaleDateString('id-ID')}</td></tr><tr><td class="left">Tipe</td><td class="right">${getTypeText(t.type)}</td></tr><tr><td class="left">Metode</td><td class="right">${getMethodText(t.transaction_method)}</td></tr><tr><td class="left">Siswa</td><td class="right">${t.student?.user?.name || '-'}</td></tr><tr><td class="left">NIS</td><td class="right">${t.student?.nis || '-'}</td></tr></table><div class="line"></div><div class="bold" style="font-size:14px">${formatCurrency(t.amount)}</div><div class="line"></div><div>Saldo Akhir: ${formatCurrency(t.ending_balance)}</div><div style="margin-top:8px;font-size:8px">Terima Kasih</div><script>window.print()<\/script></body></html>`);
    w.document.close();
};
</script>

<template>
    <Head title="Transaksi" />
    <AuthenticatedLayout>
        <template #mobileTitle>Transaksi</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Riwayat Transaksi</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-5 bg-indigo-500/10">
                            <p class="text-sm text-indigo-300">Total Transaksi</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ transactions.total }}</p>
                            <p class="text-xs text-indigo-400 mt-1">Hari ini: {{ stats.total_manual + stats.total_rfid + stats.total_barcode + stats.total_voucher + stats.total_system }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-5 bg-emerald-500/10">
                            <p class="text-sm text-emerald-300">Total Top Up (Masuk)</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(stats.summary_topup) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-5 bg-rose-500/10">
                            <p class="text-sm text-rose-300">Total Belanja (Keluar)</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(stats.summary_purchase) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-5 bg-amber-500/10">
                            <p class="text-sm text-amber-300">Total Voucher Redeem</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ formatCurrency(stats.summary_voucher) }}</p>
                            <p class="text-xs text-amber-400 mt-1">{{ stats.total_voucher }} Voucher</p>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-xl p-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <input v-model="searchForm.search" @input="search" type="text" placeholder="Cari siswa/transaksi..." class="block w-full pl-10 pr-3 py-2.5 bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm placeholder-slate-400" />
                            </div>
                            <select v-model="searchForm.type" @change="search" class="bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5">
                                <option value="">Semua Tipe</option>
                                <option value="purchase">Pembelian</option>
                                <option value="topup">Top Up</option>
                                <option value="redeem">Redeem Voucher</option>
                                <option value="return">Retur</option>
                            </select>
                            <select v-model="searchForm.transaction_method" @change="search" class="bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5">
                                <option value="">Semua Metode</option>
                                <option value="manual">Manual</option>
                                <option value="rfid">RFID Card</option>
                                <option value="barcode">Barcode</option>
                                <option value="voucher">Voucher Code</option>
                            </select>
                            <button @click="resetFilters" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 font-medium rounded-lg transition-colors border border-white/10">Reset Filter</button>
                        </div>
                        <div class="flex-shrink-0">
                            <Link v-if="can('transactions.topup')" :href="route('transactions.topup.form')" class="w-full lg:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                <span class="hidden sm:inline">Top Up Saldo</span>
                                <span class="sm:hidden">Top Up</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState v-if="transactions.data.length === 0" icon="receipt" title="Belum Ada Transaksi" description="Belum ada riwayat transaksi yang tercatat." />

                <!-- Date Filter + Table -->
                <div v-else class="space-y-4">
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-4 sm:p-6">
                        <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Dari Tanggal</label>
                                <input type="date" v-model="searchForm.date_from" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Sampai Tanggal</label>
                                <input type="date" v-model="searchForm.date_to" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" />
                            </div>
                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold text-sm shadow-lg shadow-indigo-500/20">🔍 Cari</button>
                                <button type="button" @click="resetFilters" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm border border-white/10">↺ Reset</button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-4 sm:p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-white/10">
                                    <thead class="bg-slate-900/50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Siswa</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tipe</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Metode</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Referensi</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Deskripsi</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Nominal</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo Akhir</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        <tr v-for="t in transactions.data" :key="t.id" class="hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">
                                                <div>{{ new Date(t.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</div>
                                                <div class="text-xs text-slate-500">{{ new Date(t.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-white">{{ t.student?.user?.name || '-' }}</div>
                                                <div class="text-xs text-slate-400">{{ t.student ? 'NIS: ' + t.student.nis : '' }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span :class="getTypeBadge(t.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                                    <span class="mr-1" :class="getTypeColor(t.type)">{{ getTypeIcon(t.type) }}</span>
                                                    {{ getTypeText(t.type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span :class="getMethodBadge(t.transaction_method)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                                    <span class="mr-1">{{ getMethodIcon(t.transaction_method) }}</span>
                                                    {{ getMethodText(t.transaction_method) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">
                                                <span>{{ getReferenceIcon(t.reference_type) }} {{ getReferenceText(t.reference_type) }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-slate-300">
                                                <div class="max-w-xs truncate" :title="t.description">{{ t.description }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-bold" :class="getTypeColor(t.type)">
                                                    {{ ['topup', 'redeem', 'return'].includes(t.type) ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-bold text-white">{{ formatCurrency(t.ending_balance) }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                                <AuditInfo :user="t.creator" :timestamp="t.created_at" label="Dibuat" />
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                                <AuditInfo :user="t.updater" :timestamp="t.updated_at" label="Diubah" />
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-2">
                                                    <Link v-if="t.student" :href="route('transactions.student', t.student.id)" class="text-sky-400 hover:text-sky-300 transition-colors" title="Riwayat Siswa">📋</Link>
                                                    <button @click="printReceipt(t)" class="text-purple-400 hover:text-purple-300 transition-colors" title="Cetak Struk">🖨️</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div v-if="transactions.data.length > 0" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                                <p class="text-sm text-slate-400">
                                    Menampilkan <span class="font-semibold text-slate-200">{{ transactions.from }}</span> - <span class="font-semibold text-slate-200">{{ transactions.to }}</span> dari <span class="font-semibold text-slate-200">{{ transactions.total }}</span> transaksi
                                </p>
                                <div v-if="transactions.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                    <template v-for="link in transactions.links" :key="link.label">
                                        <Link v-if="link.url" :href="link.url" v-html="link.label" :preserve-scroll="true" :class="['px-3 py-2 rounded-lg text-sm transition-all', link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border border-white/10 hover:bg-slate-700 text-slate-300']" />
                                        <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm bg-slate-800/50 text-slate-500 opacity-50 cursor-not-allowed" />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
