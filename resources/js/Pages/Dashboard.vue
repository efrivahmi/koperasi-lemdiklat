<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement
} from 'chart.js';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const page = usePage();

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    topProducts: Array,
    lowStockList: Array,
    salesChart: Array,
});

const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role || 'admin');
const userPermissions = computed(() => user.value?.permissions || {});

// Check if user has permission for a module
const hasPermission = (module) => {
    if (userRole.value === 'admin' || userRole.value === 'master') return true;
    return userPermissions.value[module] || false;
};

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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getTypeColor = (type) => {
    // topup, credit, redeem, voucher, return are credits (add balance) = green
    // purchase, debit are debits (reduce balance) = red
    return ['topup', 'credit', 'redeem', 'return'].includes(type)
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
};

// Chart.js Data - Sales Trend Bar Chart
const salesChartData = computed(() => ({
    labels: props.salesChart?.map(day => day.date.split(',')[0]) || [],
    datasets: [{
        label: 'Pendapatan',
        data: props.salesChart?.map(day => day.revenue) || [],
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderColor: 'rgb(99, 102, 241)',
        borderWidth: 1,
        borderRadius: 6,
    }]
}));

const salesChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context) => formatCurrency(context.parsed.y)
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => 'Rp ' + (value / 1000) + 'K'
            }
        }
    }
};

// Chart.js Data - Stock Status Pie Chart
const stockChartData = computed(() => ({
    labels: ['Stok Aman', 'Stok Menipis', 'Stok Habis'],
    datasets: [{
        data: [
            props.stats?.totalProducts - props.stats?.lowStockProducts - props.stats?.outOfStockProducts || 0,
            props.stats?.lowStockProducts || 0,
            props.stats?.outOfStockProducts || 0
        ],
        backgroundColor: [
            'rgba(34, 197, 94, 0.8)',   // green
            'rgba(251, 146, 60, 0.8)',  // orange
            'rgba(239, 68, 68, 0.8)'    // red
        ],
        borderColor: [
            'rgb(34, 197, 94)',
            'rgb(251, 146, 60)',
            'rgb(239, 68, 68)'
        ],
        borderWidth: 2
    }]
}));

const stockChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 15,
                font: {
                    size: 12
                }
            }
        }
    }
};

// Chart.js Data - Top Products Horizontal Bar
const topProductsChartData = computed(() => ({
    labels: props.topProducts?.map(p => p.name) || [],
    datasets: [{
        label: 'Total Penjualan',
        data: props.topProducts?.map(p => p.total_revenue) || [],
        backgroundColor: 'rgba(147, 51, 234, 0.8)',
        borderColor: 'rgb(147, 51, 234)',
        borderWidth: 1,
        borderRadius: 4,
    }]
}));

const topProductsChartOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context) => formatCurrency(context.parsed.x) + ' (' + props.topProducts[context.dataIndex].total_sold + ' terjual)'
            }
        }
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: {
                callback: (value) => 'Rp ' + (value / 1000) + 'K'
            }
        }
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :hideHeaderClock="true" :hideHeaderPos="true">
        <template #mobileTitle>Dashboard</template>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">
                    Dashboard Koperasi
                </h2>
            </div>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6 relative z-10">

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

                <!-- Today's Stats Row -->
                <!-- Stats Bento Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:auto-rows-[minmax(160px,auto)]">

                    <!-- Month Revenue (HERO BENTO - Span 2x2) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-8 text-white lg:col-span-2 lg:row-span-2 bg-emerald-950/40 backdrop-blur-xl shadow-[0_8px_30px_rgba(16,185,129,0.2)] border border-emerald-400/30 group transform transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_15px_40px_rgba(16,185,129,0.3)] flex flex-col justify-between animate-fade-in-up">
                        <!-- Abstract background shapes -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl group-hover:bg-emerald-400/30 transition-all duration-700 pointer-events-none"></div>
                        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-teal-500/20 rounded-full blur-3xl group-hover:bg-teal-500/30 transition-all duration-700 pointer-events-none"></div>
                        <svg class="absolute top-0 right-0 text-white/5 w-64 h-64 transform rotate-12 translate-x-12 -translate-y-12 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4l7.5 13h-15L12 6z"/></svg>

                        <div class="relative z-10 flex justify-between items-start">
                            <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 shadow-lg">
                                <svg class="w-8 h-8 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-black/20 backdrop-blur-md border border-white/10 text-emerald-100 text-xs font-semibold tracking-wider uppercase shadow-sm">
                                Pencatatan Bulan Ini
                            </span>
                        </div>

                        <div class="relative z-10 mt-8">
                            <p class="text-emerald-100/80 text-sm font-medium uppercase tracking-widest mb-2">Pendapatan Bulan Ini</p>
                            <h2 class="text-4xl sm:text-5xl lg:text-5xl font-extrabold text-white drop-shadow-xl animate-number-grow leading-tight">{{ formatCurrency(stats.monthRevenue) }}</h2>
                            
                            <!-- Abstract Chart Bars -->
                            <div class="mt-8 flex items-end h-12 gap-2 w-2/3 lg:w-1/2 opacity-80">
                                <div class="w-full bg-white/20 rounded-t-md h-[40%] group-hover:h-[50%] transition-all duration-500"></div>
                                <div class="w-full bg-white/20 rounded-t-md h-[60%] group-hover:h-[75%] transition-all duration-500 delay-75"></div>
                                <div class="w-full bg-white/20 rounded-t-md h-[50%] group-hover:h-[65%] transition-all duration-500 delay-100"></div>
                                <div class="w-full bg-emerald-300 rounded-t-md h-[80%] group-hover:h-[100%] transition-all duration-500 delay-150 shadow-[0_0_15px_rgba(110,231,183,0.8)]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Revenue (WIDE BENTO - Span 2x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-2 lg:row-span-1 bg-blue-950/40 backdrop-blur-xl border border-blue-500/30 shadow-[0_8px_30px_rgba(59,130,246,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-blue-400/50 flex flex-col justify-center animate-fade-in-up animation-delay-100">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex justify-between items-center gap-6">
                            <div class="min-w-0">
                                <p class="text-blue-200/80 text-xs font-semibold uppercase tracking-widest mb-1">Penjualan Hari Ini</p>
                                <p class="text-3xl font-bold text-white drop-shadow-md animate-number-grow">{{ formatCurrency(stats.todayRevenue) }}</p>
                            </div>
                            <div class="flex items-center gap-4 shrink-0">
                                <div class="text-right hidden sm:block">
                                    <p class="text-blue-100 font-medium text-lg">{{ stats.todayTransactions }}</p>
                                    <p class="text-blue-300/70 text-[10px] uppercase tracking-wider">Transaksi</p>
                                </div>
                                <div class="shrink-0 bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-2xl shadow-lg border border-blue-400/40 text-white animate-float">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Laba Bersih (SQUARE BENTO - Span 1x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 text-white lg:col-span-1 lg:row-span-1 bg-purple-950/40 backdrop-blur-xl border border-purple-500/30 shadow-[0_8px_30px_rgba(168,85,247,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-purple-400/50 flex flex-col justify-between animate-fade-in-up animation-delay-200">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div class="bg-purple-500/20 p-3 rounded-xl border border-purple-400/30 text-purple-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </div>
                            <span class="text-purple-300/60 text-[10px] uppercase tracking-wider">Bulan Ini</span>
                        </div>
                        <div class="relative z-10">
                            <p class="text-2xl font-bold text-white drop-shadow-md animate-number-grow">{{ formatCurrency(stats.netProfit) }}</p>
                            <p class="text-purple-200/80 text-xs font-medium uppercase tracking-wider mt-1">Laba Bersih</p>
                        </div>
                    </div>

                    <!-- Total Siswa (SQUARE BENTO - Span 1x1) -->
                    <div class="relative overflow-hidden sm:rounded-[2xl] p-6 pb-8 text-white lg:col-span-1 lg:row-span-1 bg-orange-950/40 backdrop-blur-xl border border-orange-500/30 shadow-[0_8px_30px_rgba(249,115,22,0.15)] group transform transition-all duration-300 hover:-translate-y-1 hover:border-orange-400/50 flex flex-col justify-between animate-fade-in-up animation-delay-300">
                        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-orange-500/20 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div class="bg-orange-500/20 p-3 rounded-xl border border-orange-400/30 text-orange-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>
                        <div class="relative z-10">
                            <p class="text-2xl font-bold text-white drop-shadow-md animate-number-grow">{{ stats.totalStudents }}</p>
                            <p class="text-orange-200/80 text-[10px] font-medium uppercase tracking-wider mt-1 mb-1">Total Siswa</p>
                            <p class="text-orange-300/60 text-[10px]">Saldo: {{ formatCurrency(stats.totalStudentBalance) }}</p>
                        </div>
                    </div>

                </div>

                <!-- Professional Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Sales Trend Chart (Bar) -->
                    <div class="lg:col-span-2 bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl relative animate-fade-in-up animation-delay-400">
                        <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent z-0"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-slate-700/50 rounded-lg border border-slate-600/50">📊</div>
                                    <span>Trend Penjualan 7 Hari Terakhir</span>
                                </h3>
                            </div>
                            <div class="h-80">
                                <Bar v-if="salesChart && salesChart.length > 0" :data="salesChartData" :options="{ ...salesChartOptions, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }, y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', callback: (value) => 'Rp ' + (value / 1000) + 'K' } } } }" />
                                <div v-else class="flex items-center justify-center h-full text-slate-500">
                                    Belum ada data penjualan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status Pie Chart -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl relative animate-fade-in-up animation-delay-500">
                        <div class="absolute inset-0 bg-gradient-to-b from-emerald-500/5 to-transparent z-0"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-slate-700/50 rounded-lg border border-slate-600/50">🎯</div>
                                    <span>Status Stok</span>
                                </h3>
                            </div>
                            <div class="h-80 flex items-center justify-center">
                                <Doughnut v-if="stats && stats.totalProducts > 0" :data="stockChartData" :options="{ ...stockChartOptions, plugins: { legend: { position: 'right', labels: { color: '#e2e8f0', padding: 20, font: { size: 12 } } } }, cutout: '70%', elements: { arc: { borderWidth: 0 } } }" />
                                <div v-else class="text-slate-500">
                                    Belum ada data produk
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert & Student Saldo -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Low Stock Alert -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl animate-fade-in-up animation-delay-600 relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-red-500/5 to-transparent z-0"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-red-900/30 rounded-lg border border-red-500/30 text-red-400">⚠️</div>
                                    <span>Stok Menipis (Perlu Restock)</span>
                                </h3>
                                <Link v-if="hasPermission('stock_ins.create')" :href="route('stock-ins.create')" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition border border-slate-600">
                                    + Tambah Stok
                                </Link>
                            </div>
                            <div class="space-y-3">
                                <div v-if="lowStockList.length === 0" class="text-center text-slate-400 py-8">
                                    ✅ Semua produk stoknya aman
                                </div>
                                <div v-for="product in lowStockList" :key="product.id" class="flex justify-between items-center p-4 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition shadow-inner">
                                    <div>
                                        <p class="font-medium text-slate-200">{{ product.name }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ product.category.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span :class="[
                                            'px-3 py-1.5 rounded-lg text-xs font-bold border',
                                            product.stock === 0 ? 'bg-red-900/40 text-red-400 border-red-500/30' :
                                            product.stock <= 5 ? 'bg-orange-900/40 text-orange-400 border-orange-500/30' :
                                            'bg-yellow-900/40 text-yellow-400 border-yellow-500/30'
                                        ]">
                                            {{ product.stock === 0 ? 'HABIS' : product.stock + ' unit' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-slate-900/60 backdrop-blur-md overflow-hidden sm:rounded-2xl p-6 border border-slate-700/50 shadow-xl animate-fade-in-up animation-delay-600 relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-purple-500/5 to-transparent z-0"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-semibold text-slate-100 flex items-center gap-3">
                                    <div class="p-2 bg-slate-700/50 rounded-lg border border-slate-600/50">📝</div>
                                    <span>Transaksi Terbaru</span>
                                </h3>
                                <Link v-if="hasPermission('transactions.view')" :href="route('transactions.index')" class="text-xs px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-md transition border border-slate-600">
                                    Lihat Semua →
                                </Link>
                            </div>
                            <div class="space-y-3">
                                <div v-if="recentTransactions.length === 0" class="text-center text-slate-400 py-8">
                                    Belum ada transaksi
                                </div>
                                <div v-for="transaction in recentTransactions" :key="transaction.id" class="flex justify-between items-center p-4 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:bg-slate-700/30 transition shadow-inner">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs border border-slate-600 text-slate-300">
                                                👤
                                            </div>
                                            <div>
                                                <p class="font-medium text-sm text-slate-200">{{ transaction.student.user.name }}</p>
                                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[150px]">{{ transaction.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4 flex flex-col items-end">
                                        <p class="font-bold text-sm" :class="['topup', 'credit', 'voucher', 'redeem', 'return'].includes(transaction.type) ? 'text-emerald-400' : 'text-rose-400'">
                                            {{ ['topup', 'credit', 'voucher', 'redeem', 'return'].includes(transaction.type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                        </p>
                                        <span v-if="transaction.transaction_method" :class="[
                                            'px-2 py-0.5 mt-1 rounded text-[10px] font-medium border border-transparent',
                                            transaction.transaction_method === 'manual' ? 'bg-blue-900/30 text-blue-300 border-blue-500/20' :
                                            transaction.transaction_method === 'rfid' ? 'bg-purple-900/30 text-purple-300 border-purple-500/20' :
                                            transaction.transaction_method === 'barcode' ? 'bg-orange-900/30 text-orange-300 border-orange-500/20' :
                                            transaction.transaction_method === 'voucher' ? 'bg-yellow-900/30 text-yellow-300 border-yellow-500/20' :
                                            'bg-slate-700 text-slate-300'
                                        ]">
                                            {{ transaction.transaction_method === 'manual' ? '✍️ Manual' :
                                               transaction.transaction_method === 'rfid' ? '📡 RFID' :
                                               transaction.transaction_method === 'barcode' ? '📊 Barcode' :
                                               transaction.transaction_method === 'voucher' ? '🎫 Voucher' :
                                               '⚙️ System'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links - Glossy SaaS Dock -->
                <div class="mt-8 pt-4 pb-4">
                    <p class="text-xs text-slate-400 font-semibold mb-4 ml-2 uppercase tracking-widest flex items-center gap-2">
                        <span>Quick Actions</span>
                    </p>
                    <div class="bg-gradient-to-r from-indigo-500/20 via-purple-600/20 to-pink-600/20 backdrop-blur-xl border border-white/10 rounded-3xl p-4 shadow-2xl">
                        <div class="flex flex-wrap md:flex-nowrap justify-around gap-4 md:gap-8">
                            <Link :href="route('products.index')" class="flex flex-col items-center gap-3 transition-transform duration-300 hover:scale-110 group w-32">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 shadow-[0_0_20px_rgba(59,130,246,0.5)] group-hover:shadow-[0_0_30px_rgba(59,130,246,0.8)] flex items-center justify-center text-white border border-white/20">
                                    <span class="text-3xl">🛍️</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-200 text-center drop-shadow-md">Data Produk</span>
                            </Link>
                            
                            <Link :href="route('transactions.topup.form')" class="flex flex-col items-center gap-3 transition-transform duration-300 hover:scale-110 group w-32">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-[0_0_20px_rgba(16,185,129,0.5)] group-hover:shadow-[0_0_30px_rgba(16,185,129,0.8)] flex items-center justify-center text-white border border-white/20">
                                    <span class="text-3xl">💰</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-200 text-center drop-shadow-md">Top-up Saldo</span>
                            </Link>

                            <Link :href="route('stock-ins.create')" class="flex flex-col items-center gap-3 transition-transform duration-300 hover:scale-110 group w-32">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 shadow-[0_0_20px_rgba(168,85,247,0.5)] group-hover:shadow-[0_0_30px_rgba(168,85,247,0.8)] flex items-center justify-center text-white border border-white/20">
                                    <span class="text-3xl">📦</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-200 text-center drop-shadow-md">Tambah Stok</span>
                            </Link>

                            <Link :href="route('reports.stock-adjustments')" class="flex flex-col items-center gap-3 transition-transform duration-300 hover:scale-110 group w-32">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-400 to-rose-600 shadow-[0_0_20px_rgba(244,63,94,0.5)] group-hover:shadow-[0_0_30px_rgba(244,63,94,0.8)] flex items-center justify-center text-white border border-white/20">
                                    <span class="text-3xl">📝</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-200 text-center drop-shadow-md leading-tight">Laporan<br>Penyesuaian Stok</span>
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

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes number-grow {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-number-grow {
    animation: number-grow 0.5s ease-out;
}

.animation-delay-100 {
    animation-delay: 0.1s;
}

.animation-delay-200 {
    animation-delay: 0.2s;
}

.animation-delay-300 {
    animation-delay: 0.3s;
}

.animation-delay-400 {
    animation-delay: 0.4s;
}

.animation-delay-500 {
    animation-delay: 0.5s;
}

.animation-delay-600 {
    animation-delay: 0.6s;
}
</style>
