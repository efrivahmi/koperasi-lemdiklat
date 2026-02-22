<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ student: Object, savings: Object, stats: Object, filters: Object });
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Tabungan Saya" />
    <AuthenticatedLayout>
        <template #mobileTitle>Riwayat Tabungan</template>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">Riwayat Tabungan</h2>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-emerald-950/40 backdrop-blur-md p-6 rounded-2xl border border-emerald-500/20 shadow-xl shadow-emerald-500/5 group hover:border-emerald-500/40 transition-all">
                        <h3 class="text-xs font-bold text-emerald-300 uppercase tracking-widest mb-1">Total Setoran</h3>
                        <p class="text-3xl font-bold text-white drop-shadow-md tabular-nums mt-2">{{ formatCurrency(stats.totalDeposits) }}</p>
                    </div>
                    <div class="bg-rose-950/40 backdrop-blur-md p-6 rounded-2xl border border-rose-500/20 shadow-xl shadow-rose-500/5 group hover:border-rose-500/40 transition-all">
                        <h3 class="text-xs font-bold text-rose-300 uppercase tracking-widest mb-1">Total Penarikan</h3>
                        <p class="text-3xl font-bold text-white drop-shadow-md tabular-nums mt-2">{{ formatCurrency(stats.totalWithdrawals) }}</p>
                    </div>
                    <div class="bg-blue-950/40 backdrop-blur-md p-6 rounded-2xl border border-blue-500/20 shadow-xl shadow-blue-500/5 group hover:border-blue-500/40 transition-all">
                        <h3 class="text-xs font-bold text-blue-300 uppercase tracking-widest mb-1">Total Tabungan</h3>
                        <p class="text-3xl font-bold text-white drop-shadow-md tabular-nums mt-2">{{ formatCurrency(stats.netSavings) }}</p>
                    </div>
                </div>

                <div class="bg-slate-800/50 backdrop-blur-md p-6 rounded-2xl border border-white/10 shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent pointer-events-none"></div>
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2 relative z-10">
                        <span class="text-purple-400">📝</span>
                        Riwayat Transaksi Tabungan
                    </h3>
                    <div class="space-y-3 relative z-10">
                        <div v-for="saving in savings.data" :key="saving.id" 
                            class="flex items-center justify-between p-4 bg-slate-900/50 border border-white/5 rounded-xl hover:bg-white/5 transition-all group">
                            <div class="flex items-center gap-4">
                                <div :class="[
                                    'w-10 h-10 rounded-xl flex items-center justify-center text-lg border shadow-inner transition-colors',
                                    saving.type === 'deposit' ? 'bg-emerald-500/20 border-emerald-500/20 group-hover:bg-emerald-500/30' : 'bg-rose-500/20 border-rose-500/20 group-hover:bg-rose-500/30'
                                ]">
                                    {{ saving.type === 'deposit' ? '➕' : '➖' }}
                                </div>
                                <div>
                                    <p :class="[
                                        'font-bold text-sm tracking-wide transition-colors',
                                        saving.type === 'deposit' ? 'text-emerald-300' : 'text-rose-300'
                                    ]">
                                        {{ saving.type === 'deposit' ? 'Setoran Tabungan' : 'Penarikan Tabungan' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5 font-medium uppercase tracking-wider">{{ formatDate(saving.transaction_date) }}</p>
                                </div>
                            </div>
                            <p class="text-base font-bold tabular-nums" :class="saving.type === 'deposit' ? 'text-emerald-400' : 'text-rose-400'">
                                {{ saving.type === 'deposit' ? '+' : '-' }} {{ formatCurrency(saving.amount) }}
                            </p>
                        </div>
                        <div v-if="savings.data.length === 0" class="text-center text-slate-500 py-12 italic border-2 border-dashed border-white/5 rounded-2xl">
                            Belum ada transaksi tabungan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
