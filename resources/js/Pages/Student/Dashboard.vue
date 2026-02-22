<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Live clock and greeting logic
const currentTime = ref(new Date());
let clockInterval = null;

const greeting = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const liveTime = computed(() => {
    return currentTime.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
});

const liveDate = computed(() => {
    return currentTime.value.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

onMounted(() => {
    clockInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});

const props = defineProps({
    student: Object,
    stats: Object,
    recentTransactions: Array,
    transactionChart: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getTransactionIcon = (type) => {
    return type === 'topup' ? '💳' : type === 'purchase' ? '🛒' : '💵';
};

const getTransactionColor = (type) => {
    return type === 'topup'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : type === 'purchase'
        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};
</script>

<template>
    <Head title="Portal Siswa" />
    <AuthenticatedLayout :hideHeaderClock="true" :hideHeaderPos="true">
        <template #mobileTitle>Portal Siswa</template>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">Portal Siswa</h2>
                    <p class="text-sm text-slate-300 mt-1">Selamat datang, {{ student.user.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">

                <!-- Greeting Banner -->
                <div class="relative overflow-hidden rounded-2xl bg-indigo-950/40 backdrop-blur-xl p-6 sm:p-8 shadow-2xl shadow-indigo-500/20 border border-white/10 mb-6 group transition-all duration-500 hover:shadow-indigo-500/30">
                    <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-lg">
                                {{ greeting }}, {{ user?.name?.split(' ')[0] }} 👋
                            </h1>
                            <p class="text-purple-100/80 text-sm mt-1">{{ liveDate }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-white/15 backdrop-blur-md rounded-xl px-5 py-3 border border-white/20 shadow-lg">
                                <p class="text-3xl sm:text-4xl font-bold text-white tabular-nums tracking-wider drop-shadow-lg">{{ liveTime }}</p>
                                <p class="text-[10px] text-purple-200 uppercase tracking-widest text-center mt-0.5">Waktu Indonesia Barat</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Student Info Card -->
                <div class="relative overflow-hidden sm:rounded-2xl p-8 text-white transform transition-all duration-300 hover:scale-[1.01] bg-purple-950/40 backdrop-blur-xl border border-purple-500/40 shadow-[0_0_15px_rgba(168,85,247,0.15)]">
                    <div class="absolute inset-0 bg-purple-500/10 z-0 mix-blend-overlay"></div>
                    <div class="flex flex-col md:flex-row md:items-center justify-between relative z-10 gap-6">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20 shadow-inner">
                                <span class="text-3xl">👨‍🎓</span>
                            </div>
                            <div>
                                <p class="text-purple-200/80 text-xs font-medium uppercase tracking-wider">NISN</p>
                                <p class="text-2xl font-bold tracking-wide">{{ student.nis }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-md bg-white/10 text-xs font-semibold border border-white/10">Kelas {{ student.class }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left md:text-right bg-black/20 p-5 rounded-2xl border border-white/10 shadow-inner">
                            <p class="text-purple-200/80 text-xs font-medium uppercase tracking-wider">Saldo Tersedia</p>
                            <p class="text-4xl font-bold mt-1 text-emerald-400 drop-shadow-md">{{ formatCurrency(stats.balance) }}</p>
                            <Link :href="route('student.transactions')" class="mt-4 inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white rounded-xl font-semibold transition-all shadow-lg shadow-purple-500/30 text-sm">
                                <span>Riwayat Transaksi</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Bento Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:auto-rows-[160px]">
                    <!-- Total Pengeluaran (HERO BENTO - Span 2x2) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-8 text-white lg:col-span-2 lg:row-span-2 bg-rose-950/40 backdrop-blur-xl shadow-[0_8px_30px_rgba(244,63,94,0.2)] border border-rose-400/30 group transform transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_15px_40px_rgba(244,63,94,0.3)] flex flex-col justify-between animate-fade-in-up">
                        <!-- Abstract background shapes -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-rose-400/20 rounded-full blur-3xl group-hover:bg-rose-400/30 transition-all duration-700 pointer-events-none"></div>
                        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl group-hover:bg-pink-500/30 transition-all duration-700 pointer-events-none"></div>
                        <svg class="absolute top-0 right-0 text-white/5 w-64 h-64 transform rotate-12 translate-x-12 -translate-y-12 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4l7.5 13h-15L12 6z"/></svg>

                        <div class="relative z-10 flex justify-between items-start">
                            <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 shadow-lg">
                                <span class="text-3xl">💸</span>
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-black/20 backdrop-blur-md border border-white/10 text-rose-100 text-xs font-semibold tracking-wider uppercase shadow-sm flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Keseluruhan
                            </span>
                        </div>

                        <div class="relative z-10 mt-8">
                            <p class="text-rose-100/80 text-sm font-medium uppercase tracking-widest mb-2">Total Pengeluaran</p>
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
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-1 lg:row-span-1 bg-blue-950/40 backdrop-blur-xl border border-blue-500/30 shadow-[0_8px_30px_rgba(59,130,246,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-blue-400/50 flex flex-col justify-center animate-fade-in-up animation-delay-100">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex flex-col items-start gap-4 h-full justify-center">
                            <div class="w-full flex justify-between items-center">
                                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl shadow-lg border border-blue-400/40 text-white">
                                    <span class="text-xl">📊</span>
                                </div>
                                <span class="bg-blue-500/20 px-2 py-1 rounded border border-blue-500/30 text-[10px] text-blue-200 uppercase tracking-widest">Semua Waktu</span>
                            </div>
                            <div>
                                <p class="text-3xl font-bold text-white drop-shadow-md animate-number-grow">{{ stats.totalTransactions }}</p>
                                <p class="text-blue-200/80 text-xs font-semibold uppercase tracking-widest mt-1">Total Transaksi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pengeluaran Bulan Ini (WIDE BENTO - Span 1x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-1 lg:row-span-1 bg-orange-950/40 backdrop-blur-xl border border-orange-500/30 shadow-[0_8px_30px_rgba(249,115,22,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-orange-400/50 flex flex-col justify-center animate-fade-in-up animation-delay-200">
                        <div class="absolute inset-0 bg-gradient-to-l from-orange-600/10 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex flex-col items-start gap-4 h-full justify-center">
                            <div class="w-full flex justify-between items-center">
                                <div class="bg-gradient-to-br from-orange-500 to-amber-600 p-3 rounded-xl shadow-lg border border-orange-400/40 text-white">
                                    <span class="text-xl">📅</span>
                                </div>
                                <span class="bg-orange-500/20 px-2 py-1 rounded border border-orange-500/30 text-[10px] text-orange-200 uppercase tracking-widest">30 Hari</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-white drop-shadow-md animate-number-grow">{{ formatCurrency(stats.monthlySpending) }}</p>
                                <p class="text-orange-200/80 text-xs font-semibold uppercase tracking-widest mt-1">Pengeluaran Bulan Ini</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Spending Chart -->
                <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl relative">
                    <div class="absolute inset-0 bg-gradient-to-b from-indigo-500/5 to-transparent z-0"></div>
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold mb-6 text-slate-100 flex items-center gap-2">
                            <span class="text-indigo-400">📈</span>
                            Pengeluaran 7 Hari Terakhir
                        </h3>
                        <div class="space-y-5">
                            <div v-for="day in transactionChart" :key="day.date" class="flex items-center gap-4 group">
                                <div class="w-28 text-xs font-semibold text-slate-400 group-hover:text-slate-300 transition-colors">{{ day.date }}</div>
                                <div class="flex-1 bg-slate-900/50 rounded-full h-6 relative border border-slate-700/50 overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-600 to-purple-500 h-full rounded-full flex items-center justify-end pr-3 text-white text-[10px] font-bold transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(99,102,241,0.5)]"
                                         :style="{ width: (day.spent / Math.max(...transactionChart.map(d => d.spent)) * 100) + '%' }">
                                        <span v-if="day.spent > 0">{{ formatCurrency(day.spent) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Transactions -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-emerald-500/5 to-transparent z-0"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                                    <span class="text-emerald-400">📝</span>
                                    Transaksi Terakhir
                                </h3>
                                <Link :href="route('student.transactions')" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition border border-slate-600">
                                    Lihat Semua
                                </Link>
                            </div>
                            <div class="space-y-3">
                                <div v-if="recentTransactions.length === 0" class="text-center py-8 text-slate-500">
                                    Belum ada transaksi
                                </div>
                                <div v-for="transaction in recentTransactions" :key="transaction.id"
                                     class="flex items-center justify-between p-4 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition shadow-inner">
                                    <div class="flex items-center gap-4">
                                        <div :class="[
                                            'w-10 h-10 rounded-xl flex items-center justify-center text-lg border shadow-sm',
                                            transaction.type === 'topup' ? 'bg-emerald-900/30 border-emerald-500/30' : 
                                            transaction.type === 'purchase' ? 'bg-rose-900/30 border-rose-500/30' : 
                                            'bg-blue-900/30 border-blue-500/30'
                                        ]">
                                            {{ getTransactionIcon(transaction.type) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm text-slate-200">
                                                {{ transaction.type === 'topup' ? 'Top Up Saldo' : transaction.type === 'purchase' ? 'Pembelian' : 'Redeem Voucher' }}
                                            </p>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                {{ new Date(transaction.created_at).toLocaleString('id-ID', {
                                                    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                                                }) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold" :class="['topup', 'redeem', 'credit'].includes(transaction.type) ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ ['topup', 'redeem', 'credit'].includes(transaction.type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                        </p>
                                        <p class="text-[10px] text-slate-500 mt-0.5 font-medium">Saldo: {{ formatCurrency(transaction.ending_balance) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 gap-6">
                        <Link :href="route('student.purchases')" class="group relative overflow-hidden bg-gradient-to-br from-blue-700 to-cyan-600 text-white p-8 rounded-2xl shadow-[0_0_20px_rgba(14,165,233,0.3)] hover:-translate-y-1 transition-all duration-300 border border-cyan-400/30">
                            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="flex items-center justify-between relative z-10">
                                <div>
                                    <h3 class="text-xl font-bold mb-2 tracking-wide">Riwayat Pembelian</h3>
                                    <p class="text-sm text-blue-100/80">Lihat detail barang yang Anda beli</p>
                                </div>
                                <div class="w-14 h-14 bg-black/20 rounded-2xl flex items-center justify-center border border-white/10 group-hover:bg-black/30 transition-colors shadow-inner">
                                    <span class="text-2xl group-hover:scale-110 transition-transform">🛒</span>
                                </div>
                            </div>
                        </Link>

                        <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-8 rounded-2xl shadow-[0_0_20px_rgba(16,185,129,0.3)] border border-emerald-400/30 cursor-default">
                            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="flex items-center justify-between relative z-10">
                                <div>
                                    <h3 class="text-xl font-bold mb-2 tracking-wide">Butuh Top Up?</h3>
                                    <p class="text-sm text-emerald-100/80">Temui petugas koperasi untuk isi saldo</p>
                                </div>
                                <div class="w-14 h-14 bg-black/20 rounded-2xl flex items-center justify-center border border-white/10 shadow-inner">
                                    <span class="text-2xl group-hover:scale-110 transition-transform animate-pulse">💳</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
