<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    teacher: Object,
    transactions: Object,
    filters: Object,
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
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
                <!-- Transactions List -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-2xl border border-white/10">
                    <div class="p-6 bg-purple-900/20 border-b border-white/10 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-purple-400">📝</span>
                            Daftar Transaksi
                        </h3>
                        <p class="text-xs font-medium text-purple-200 uppercase tracking-widest">
                            Total: {{ transactions.total }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Waktu</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Tipe</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-200 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-purple-200 uppercase tracking-widest">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                        Tidak ada transaksi ditemukan
                                    </td>
                                </tr>
                                <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-white">{{ formatDate(tx.created_at) }}</div>
                                        <div class="text-[10px] text-slate-400 group-hover:text-purple-300 transition-colors uppercase tracking-wider">
                                            {{ new Date(tx.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="[
                                            'px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm border',
                                            tx.type === 'topup' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/20' : 
                                            tx.type === 'purchase' ? 'bg-rose-500/20 text-rose-300 border-rose-500/20' : 
                                            'bg-blue-500/20 text-blue-300 border-blue-500/20'
                                        ]">
                                            {{ tx.type === 'topup' ? '💳 Top Up' : tx.type === 'purchase' ? '🛒 Pembelian' : tx.type }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        {{ tx.description || '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="text-base font-bold tabular-nums" :class="['topup', 'redeem', 'credit'].includes(tx.type) ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ ['topup', 'redeem', 'credit'].includes(tx.type) ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
