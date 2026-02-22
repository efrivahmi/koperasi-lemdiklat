<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
    transactions: Object,
    filters: Object,
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    type: props.filters?.type || '',
});

const search = () => {
    router.get(route('student.transactions'), searchForm.value, { preserveState: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getTransactionIcon = (type) => {
    return type === 'topup' ? '💳' : type === 'purchase' ? '🛒' : '💵';
};

const getTransactionBadge = (type) => {
    return type === 'topup'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : type === 'purchase'
        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};

const getTransactionLabel = (type) => {
    return type === 'topup' ? 'Top Up' : type === 'purchase' ? 'Pembelian' : 'Redeem Voucher';
};
</script>

<template>
    <Head title="Riwayat Transaksi" />
    <AuthenticatedLayout>
        <template #mobileTitle>Riwayat Transaksi</template>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">Riwayat Transaksi</h2>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">
                <!-- Filters -->
                <div class="bg-slate-800/50 backdrop-blur-md p-6 rounded-2xl border border-white/10 shadow-xl shadow-purple-500/5">
                    <h3 class="font-semibold mb-4 text-white flex items-center gap-2">
                        <span class="text-purple-400">🔍</span>
                        Filter Transaksi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-purple-200/70">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-purple-500 focus:border-purple-500 transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-purple-200/70">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-purple-500 focus:border-purple-500 transition-all" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-purple-200/70">Tipe Transaksi</label>
                            <select v-model="searchForm.type" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-purple-500 focus:border-purple-500 transition-all">
                                <option value="" class="bg-slate-900">Semua Tipe</option>
                                <option value="topup" class="bg-slate-900">Top Up</option>
                                <option value="purchase" class="bg-slate-900">Pembelian</option>
                                <option value="redeem" class="bg-slate-900">Redeem Voucher</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button @click="search" 
                                class="flex-1 px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-purple-500/20 transition-all active:scale-95">
                                Tampilkan
                            </button>
                            <button @click="() => { searchForm = { date_from: '', date_to: '', type: '' }; search(); }"
                                class="px-4 py-2.5 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl font-bold border border-white/10 transition-all active:scale-95">
                                ↺
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-2xl border border-white/10">
                    <div class="p-4 bg-purple-900/20 border-b border-white/10">
                        <p class="text-sm text-purple-200">
                            <strong>Total Transaksi:</strong> {{ transactions.total }} •
                            Menampilkan {{ transactions.from }}-{{ transactions.to }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Tanggal & Waktu</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Tipe</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-purple-200 uppercase tracking-widest">Jumlah</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-purple-200 uppercase tracking-widest">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                        Tidak ada transaksi ditemukan
                                    </td>
                                </tr>
                                <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-white">{{ new Date(transaction.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</div>
                                        <div class="text-[10px] text-slate-400 group-hover:text-purple-300 transition-colors uppercase tracking-wider">{{ new Date(transaction.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="[
                                            'px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm border',
                                            transaction.type === 'topup' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/20' : 
                                            transaction.type === 'purchase' ? 'bg-rose-500/20 text-rose-300 border-rose-500/20' : 
                                            'bg-blue-500/20 text-blue-300 border-blue-500/20'
                                        ]">
                                            <span>{{ getTransactionIcon(transaction.type) }}</span>
                                            {{ getTransactionLabel(transaction.type) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        {{ transaction.description || '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="text-base font-bold tabular-nums" :class="transaction.type === 'topup' || transaction.type === 'redeem' ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ transaction.type === 'topup' || transaction.type === 'redeem' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="text-base font-bold text-indigo-300 tabular-nums">
                                            {{ formatCurrency(transaction.ending_balance) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-white/10 bg-slate-900/30">
                        <div class="text-xs font-medium text-slate-400 uppercase tracking-widest">
                            Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }}
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="link in transactions.links" :key="link.label">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-xs font-bold rounded-lg transition-all border', 
                                        link.active 
                                            ? 'bg-gradient-to-r from-purple-600 to-indigo-600 border-purple-500 text-white shadow-lg shadow-purple-500/20' 
                                            : 'bg-slate-800/50 border-white/10 text-slate-400 hover:text-white hover:bg-slate-700/50'
                                    ]"
                                    v-html="link.label"
                                    :preserve-scroll="true" />
                                <span v-else
                                    class="px-3 py-2 text-xs font-bold rounded-lg bg-slate-900/50 border border-white/5 text-slate-600 cursor-not-allowed"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
