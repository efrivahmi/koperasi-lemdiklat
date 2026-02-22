<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();
const page = usePage();

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    topProducts: Array,
    lowStockList: Array,
    salesChart: Array,
});

const user = computed(() => page.props.auth?.user);

// Live clock
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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const lowStockExpanded = ref(false);
</script>

<template>
    <Head title="Dashboard Kasir" />

    <AuthenticatedLayout>
        <template #mobileTitle>Dashboard</template>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">
                Dashboard Kasir
            </h2>
        </template>

        <div class="py-6 sm:py-8 min-h-screen relative">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">

                <!-- Greeting Banner -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 sm:p-8 shadow-2xl shadow-purple-500/30 border border-white/10">
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

                <!-- Today's Sales Hero -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Today Revenue -->
                    <div class="relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-emerald-500 via-teal-600 to-green-800 shadow-xl shadow-emerald-500/20 border border-emerald-400/30 group hover:-translate-y-0.5 transition-all duration-300">
                        <div class="absolute -top-16 -right-16 w-40 h-40 bg-emerald-300/20 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-white/15 backdrop-blur-md p-3 rounded-xl border border-white/20">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-emerald-100/80 text-xs font-semibold uppercase tracking-widest">Penjualan Hari Ini</p>
                                </div>
                            </div>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-white drop-shadow-xl leading-tight">{{ formatCurrency(stats?.todayRevenue) }}</h2>
                            <div class="flex items-center gap-4 mt-3">
                                <span class="text-emerald-100 text-sm font-medium">{{ stats?.todayTransactions || 0 }} Transaksi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Month Revenue (compact) -->
                    <div class="relative overflow-hidden rounded-2xl p-6 bg-blue-950/60 backdrop-blur-xl border border-blue-500/30 shadow-xl shadow-blue-500/10 group hover:-translate-y-0.5 transition-all duration-300">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/15 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-blue-500/20 p-3 rounded-xl border border-blue-400/30">
                                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-blue-200/80 text-xs font-semibold uppercase tracking-widest">Bulan Ini</p>
                                </div>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">{{ formatCurrency(stats?.monthRevenue) }}</h2>
                            <p class="text-blue-300/70 text-xs mt-2">Pendapatan bulan berjalan</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div>
                    <p class="text-xs text-slate-400 font-semibold mb-3 uppercase tracking-widest flex items-center gap-2">
                        <span>⚡</span>
                        <span>Quick Actions</span>
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        <!-- POS -->
                        <Link v-if="can('pos.access')" :href="route('kasir.pos.index')"
                            class="group relative overflow-hidden rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-pink-500/20 to-rose-600/20 backdrop-blur-xl border border-pink-500/30 hover:border-pink-400/60 shadow-lg hover:shadow-pink-500/20 transition-all duration-300 hover:-translate-y-1 text-center">
                            <div class="relative z-10">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/40 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300 border border-white/20">
                                    <span class="text-3xl sm:text-4xl">🛒</span>
                                </div>
                                <span class="text-sm font-bold text-white drop-shadow-md">POS / Kasir</span>
                            </div>
                        </Link>

                        <!-- Top-up -->
                        <Link v-if="can('transactions.topup')" :href="route('transactions.topup.form')"
                            class="group relative overflow-hidden rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-emerald-500/20 to-green-600/20 backdrop-blur-xl border border-emerald-500/30 hover:border-emerald-400/60 shadow-lg hover:shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-1 text-center">
                            <div class="relative z-10">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-500/40 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300 border border-white/20">
                                    <span class="text-3xl sm:text-4xl">💰</span>
                                </div>
                                <span class="text-sm font-bold text-white drop-shadow-md">Top-up Saldo</span>
                            </div>
                        </Link>

                        <!-- Tambah Stok -->
                        <Link v-if="can('stock_ins.create')" :href="route('stock-ins.create')"
                            class="group relative overflow-hidden rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-purple-500/20 to-violet-600/20 backdrop-blur-xl border border-purple-500/30 hover:border-purple-400/60 shadow-lg hover:shadow-purple-500/20 transition-all duration-300 hover:-translate-y-1 text-center">
                            <div class="relative z-10">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 shadow-lg shadow-purple-500/40 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300 border border-white/20">
                                    <span class="text-3xl sm:text-4xl">📦</span>
                                </div>
                                <span class="text-sm font-bold text-white drop-shadow-md">Tambah Stok</span>
                            </div>
                        </Link>

                        <!-- Penyesuaian Stok -->
                        <Link v-if="can('reports.stock_adjustments')" :href="route('kasir.reports.stock-adjustments')"
                            class="group relative overflow-hidden rounded-2xl p-5 sm:p-6 bg-gradient-to-br from-orange-500/20 to-amber-600/20 backdrop-blur-xl border border-orange-500/30 hover:border-orange-400/60 shadow-lg hover:shadow-orange-500/20 transition-all duration-300 hover:-translate-y-1 text-center">
                            <div class="relative z-10">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 shadow-lg shadow-orange-500/40 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300 border border-white/20">
                                    <span class="text-3xl sm:text-4xl">📑</span>
                                </div>
                                <span class="text-sm font-bold text-white drop-shadow-md leading-tight">Penyesuaian Stok</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Bottom Row: Transactions + Low Stock -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Recent Transactions -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden rounded-2xl border border-slate-700/50 shadow-xl relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-purple-500/5 to-transparent z-0"></div>
                        <div class="relative z-10 p-5 sm:p-6">
                            <div class="flex justify-between items-center mb-5">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-purple-500/20 rounded-lg border border-purple-400/30">📝</div>
                                    <span>Transaksi Terbaru</span>
                                </h3>
                                <Link v-if="can('transactions.history')" :href="route('transactions.index')" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition border border-slate-600 font-medium">
                                    Lihat Semua →
                                </Link>
                            </div>

                            <div class="space-y-2.5">
                                <div v-if="!recentTransactions || recentTransactions.length === 0" class="text-center text-slate-400 py-8">
                                    Belum ada transaksi hari ini
                                </div>
                                <div v-for="tx in (recentTransactions || []).slice(0, 5)" :key="tx.id"
                                    class="flex items-center justify-between p-3.5 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-md">
                                            {{ tx.student?.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-200 truncate">{{ tx.student?.user?.name || 'Tunai' }}</p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">{{ tx.description?.substring(0, 35) || tx.type }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-3">
                                        <p class="text-sm font-bold" :class="['topup', 'credit', 'voucher', 'redeem', 'return'].includes(tx.type) ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ ['topup', 'credit', 'voucher', 'redeem', 'return'].includes(tx.type) ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                        </p>
                                        <span v-if="tx.transaction_method" :class="[
                                            'px-1.5 py-0.5 rounded text-[9px] font-medium border',
                                            tx.transaction_method === 'rfid' ? 'bg-purple-900/30 text-purple-300 border-purple-500/20' :
                                            tx.transaction_method === 'barcode' ? 'bg-orange-900/30 text-orange-300 border-orange-500/20' :
                                            tx.transaction_method === 'voucher' ? 'bg-yellow-900/30 text-yellow-300 border-yellow-500/20' :
                                            'bg-blue-900/30 text-blue-300 border-blue-500/20'
                                        ]">
                                            {{ tx.transaction_method === 'rfid' ? '📡 RFID' :
                                               tx.transaction_method === 'barcode' ? '📊 Barcode' :
                                               tx.transaction_method === 'voucher' ? '🎫 Voucher' :
                                               '✍️ Manual'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden rounded-2xl border border-slate-700/50 shadow-xl relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-red-500/5 to-transparent z-0"></div>
                        <div class="relative z-10 p-5 sm:p-6">
                            <div class="flex justify-between items-center mb-5">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-red-900/30 rounded-lg border border-red-500/30 text-red-400">⚠️</div>
                                    <span>Stok Menipis</span>
                                    <span v-if="lowStockList && lowStockList.length > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-500/20 text-red-400 border border-red-500/30">{{ lowStockList.length }}</span>
                                </h3>
                                <button v-if="lowStockList && lowStockList.length > 3" @click="lowStockExpanded = !lowStockExpanded" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition border border-slate-600 font-medium">
                                    {{ lowStockExpanded ? 'Tampilkan Sedikit' : 'Lihat Semua' }}
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <div v-if="!lowStockList || lowStockList.length === 0" class="text-center text-slate-400 py-8">
                                    ✅ Semua produk stoknya aman
                                </div>
                                <div v-for="product in (lowStockExpanded ? lowStockList : (lowStockList || []).slice(0, 4))" :key="product.id"
                                    class="flex justify-between items-center p-3.5 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition">
                                    <div class="min-w-0">
                                        <p class="font-medium text-sm text-slate-200 truncate">{{ product.name }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">{{ product.category?.name }}</p>
                                    </div>
                                    <span :class="[
                                        'px-2.5 py-1 rounded-lg text-xs font-bold border flex-shrink-0',
                                        product.stock === 0 ? 'bg-red-900/40 text-red-400 border-red-500/30' :
                                        product.stock <= 5 ? 'bg-orange-900/40 text-orange-400 border-orange-500/30' :
                                        'bg-yellow-900/40 text-yellow-400 border-yellow-500/30'
                                    ]">
                                        {{ product.stock === 0 ? 'HABIS' : product.stock + ' unit' }}
                                    </span>
                                </div>
                            </div>

                            <Link v-if="can('stock_ins.create')" :href="route('stock-ins.create')" class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-600/50 rounded-xl text-slate-300 hover:text-white text-sm font-medium transition-colors">
                                <span>📦</span>
                                <span>Tambah Stok Baru</span>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}
</style>
