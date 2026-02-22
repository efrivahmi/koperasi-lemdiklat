<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    teacher: Object,
    stats: Object,
    recentTransactions: Array,
    transactionChart: Array,
});

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Portal Guru" />
    <AuthenticatedLayout>
        <template #mobileTitle>Portal Guru</template>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">Portal Guru</h2>
                    <p class="text-sm text-slate-300 mt-1">Selamat datang, {{ teacher.user.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">
                <!-- Teacher Info Card -->
                <div class="relative overflow-hidden sm:rounded-2xl p-8 text-white transform transition-all duration-300 hover:scale-[1.01] bg-purple-950/60 backdrop-blur-md border border-purple-500/40 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                    <div class="absolute inset-0 bg-purple-500/10 z-0 mix-blend-overlay"></div>
                    <div class="flex flex-col md:flex-row md:items-center justify-between relative z-10 gap-6">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20 shadow-inner">
                                <span class="text-3xl">👨‍🏫</span>
                            </div>
                            <div>
                                <p class="text-purple-200/80 text-xs font-medium uppercase tracking-wider">NIP</p>
                                <p class="text-2xl font-bold tracking-wide">{{ teacher.nip }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-md bg-white/10 text-xs font-semibold border border-white/10">{{ teacher.jabatan || 'Guru' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left md:text-right bg-black/20 p-5 rounded-2xl border border-white/10 shadow-inner">
                            <p class="text-purple-200/80 text-xs font-medium uppercase tracking-wider">Saldo Tersedia</p>
                            <p class="text-4xl font-bold mt-1 text-emerald-400 drop-shadow-md">{{ formatCurrency(stats.balance) }}</p>
                            <Link :href="route('teacher.transactions')" class="mt-4 inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white rounded-xl font-semibold transition-all shadow-lg shadow-purple-500/30 text-sm">
                                <span>Riwayat Transaksi</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Bento Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:auto-rows-[160px]">
                    <!-- Total Belanja (HERO BENTO - Span 2x2) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-8 text-white lg:col-span-2 lg:row-span-2 bg-gradient-to-br from-rose-500 via-pink-700 to-slate-900 shadow-[0_8px_30px_rgba(244,63,94,0.3)] border border-rose-400/30 group transform transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_15px_40px_rgba(244,63,94,0.4)] flex flex-col justify-between animate-fade-in-up">
                        <!-- Abstract background shapes -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-rose-400/20 rounded-full blur-3xl group-hover:bg-rose-400/30 transition-all duration-700 pointer-events-none"></div>
                        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl group-hover:bg-pink-500/30 transition-all duration-700 pointer-events-none"></div>
                        <svg class="absolute top-0 right-0 text-white/5 w-64 h-64 transform rotate-12 translate-x-12 -translate-y-12 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4l7.5 13h-15L12 6z"/></svg>

                        <div class="relative z-10 flex justify-between items-start">
                            <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 shadow-lg">
                                <span class="text-3xl">🛍️</span>
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-black/20 backdrop-blur-md border border-white/10 text-rose-100 text-xs font-semibold tracking-wider uppercase shadow-sm flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Keseluruhan
                            </span>
                        </div>

                        <div class="relative z-10 mt-8">
                            <p class="text-rose-100/80 text-sm font-medium uppercase tracking-widest mb-2">Total Belanja</p>
                            <h2 class="text-4xl sm:text-5xl lg:text-5xl font-extrabold text-white drop-shadow-xl animate-number-grow leading-tight">{{ formatCurrency(stats.totalSpent) }}</h2>
                            
                            <!-- Abstract Chart Bars -->
                            <div class="mt-8 flex items-end h-12 gap-2 w-2/3 lg:w-1/2 opacity-80">
                                <div class="w-full bg-white/20 rounded-t-md h-[40%] group-hover:h-[50%] transition-all duration-500"></div>
                                <div class="w-full bg-white/20 rounded-t-md h-[60%] group-hover:h-[75%] transition-all duration-500 delay-75"></div>
                                <div class="w-full bg-white/20 rounded-t-md h-[50%] group-hover:h-[65%] transition-all duration-500 delay-100"></div>
                                <div class="w-full bg-rose-300 rounded-t-md h-[80%] group-hover:h-[100%] transition-all duration-500 delay-150 shadow-[0_0_15px_rgba(251,113,133,0.8)]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Transaksi (WIDE BENTO - Span 1x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-1 lg:row-span-1 bg-blue-950/60 backdrop-blur-xl border border-blue-500/30 shadow-[0_8px_30px_rgba(59,130,246,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-blue-400/50 flex flex-col justify-center animate-fade-in-up animation-delay-100">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex flex-col items-start gap-4 h-full justify-center">
                            <div class="w-full flex justify-between items-center">
                                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl shadow-lg border border-blue-400/40 text-white">
                                    <span class="text-xl">📊</span>
                                </div>
                                <span class="bg-blue-500/20 px-2 py-1 rounded border border-blue-500/30 text-[10px] text-blue-200 uppercase tracking-widest">Semua</span>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-white drop-shadow-md animate-number-grow">{{ stats.totalTransactions }}</p>
                                <p class="text-blue-200/80 text-xs font-semibold uppercase tracking-widest mt-1">Total Transaksi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tabungan (WIDE BENTO - Span 1x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-1 lg:row-span-1 bg-emerald-950/60 backdrop-blur-xl border border-emerald-500/30 shadow-[0_8px_30px_rgba(16,185,129,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-emerald-400/50 flex flex-col justify-center animate-fade-in-up animation-delay-200">
                        <div class="absolute inset-0 bg-gradient-to-l from-emerald-600/10 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex flex-col items-start gap-4 h-full justify-center">
                            <div class="w-full flex justify-between items-center">
                                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-3 rounded-xl shadow-lg border border-emerald-400/40 text-white">
                                    <span class="text-xl">💰</span>
                                </div>
                                <span class="bg-emerald-500/20 px-2 py-1 rounded border border-emerald-500/30 text-[10px] text-emerald-200 uppercase tracking-widest">Saldo Aman</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-white drop-shadow-md animate-number-grow">{{ formatCurrency(stats.totalSavings || 0) }}</p>
                                <p class="text-emerald-200/80 text-xs font-semibold uppercase tracking-widest mt-1">Total Tabungan</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Recent Transactions -->
                <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl relative">
                    <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 to-transparent z-0"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                                <span class="text-indigo-400">📝</span>
                                Transaksi Terakhir
                            </h3>
                            <Link :href="route('teacher.transactions')" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition border border-slate-600">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div v-if="recentTransactions.length === 0" class="text-center py-8 text-slate-500">
                                Belum ada transaksi
                            </div>
                            <div v-for="tx in recentTransactions" :key="tx.id"
                                 class="flex items-center justify-between p-4 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition shadow-inner">
                                <div class="flex items-center gap-4">
                                    <div :class="[
                                        'w-10 h-10 rounded-xl flex items-center justify-center text-lg border shadow-sm',
                                        tx.type === 'topup' ? 'bg-emerald-900/30 border-emerald-500/30' : 
                                        tx.type === 'purchase' ? 'bg-rose-900/30 border-rose-500/30' : 
                                        'bg-blue-900/30 border-blue-500/30'
                                    ]">
                                        {{ tx.type === 'topup' ? '💳' : tx.type === 'purchase' ? '🛒' : '💵' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-slate-200 capitalize">
                                            {{ tx.type === 'topup' ? 'Top Up Saldo' : tx.type === 'purchase' ? 'Pembelian' : tx.type }}
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            {{ new Date(tx.created_at).toLocaleString('id-ID', {
                                                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                                            }) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold" :class="['topup', 'redeem', 'credit'].includes(tx.type) ? 'text-emerald-400' : 'text-rose-400'">
                                        {{ ['topup', 'redeem', 'credit'].includes(tx.type) ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
