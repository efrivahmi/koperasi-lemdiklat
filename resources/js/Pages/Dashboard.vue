<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard Koperasi
                </h2>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ new Date().toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) }}
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Today's Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Today's Revenue -->
                    <div class="bg-gradient-to-br from-blue-500 via-blue-600 to-cyan-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/50 animate-fade-in-up">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-blue-100 text-sm font-medium">Penjualan Hari Ini</p>
                                <p class="text-3xl font-bold mt-2 animate-number-grow">{{ formatCurrency(stats.todayRevenue) }}</p>
                                <p class="text-blue-100 text-xs mt-2">{{ stats.todayTransactions }} transaksi</p>
                            </div>
                            <div class="text-5xl opacity-20 animate-float">💰</div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mb-10"></div>
                    </div>

                    <!-- Month Revenue -->
                    <div class="bg-gradient-to-br from-green-500 via-green-600 to-emerald-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/50 animate-fade-in-up animation-delay-100">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-green-100 text-sm font-medium">Pendapatan Bulan Ini</p>
                                <p class="text-3xl font-bold mt-2 animate-number-grow">{{ formatCurrency(stats.monthRevenue) }}</p>
                                <p class="text-green-100 text-xs mt-2">Sejak awal bulan</p>
                            </div>
                            <div class="text-5xl opacity-20 animate-float animation-delay-200">📈</div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mb-10"></div>
                    </div>

                    <!-- Net Profit -->
                    <div class="bg-gradient-to-br from-purple-500 via-purple-600 to-pink-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/50 animate-fade-in-up animation-delay-200">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-purple-100 text-sm font-medium">Laba Bersih</p>
                                <p class="text-3xl font-bold mt-2 animate-number-grow">{{ formatCurrency(stats.netProfit) }}</p>
                                <p class="text-purple-100 text-xs mt-2">Setelah biaya operasional</p>
                            </div>
                            <div class="text-5xl opacity-20 animate-float animation-delay-400">💎</div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mb-10"></div>
                    </div>

                    <!-- Total Students -->
                    <div class="bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-orange-500/50 animate-fade-in-up animation-delay-300">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-orange-100 text-sm font-medium">Total Siswa</p>
                                <p class="text-3xl font-bold mt-2 animate-number-grow">{{ stats.totalStudents }}</p>
                                <p class="text-orange-100 text-xs mt-2">{{ stats.activeStudents }} dengan saldo aktif</p>
                            </div>
                            <div class="text-5xl opacity-20 animate-float animation-delay-600">👨‍🎓</div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mb-10"></div>
                    </div>
                </div>

                <!-- Secondary Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Product Stats -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/20 animate-fade-in-up animation-delay-400">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span class="text-2xl animate-float">📦</span>
                            <span>Inventaris Produk</span>
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Total Produk</span>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ stats.totalProducts }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Stok Menipis</span>
                                <span class="font-bold text-yellow-600">{{ stats.lowStockProducts }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Stok Habis</span>
                                <span class="font-bold text-red-600">{{ stats.outOfStockProducts }}</span>
                            </div>
                        </div>
                        <Link v-if="hasPermission('module_products')" :href="route('products.index')" class="mt-4 block text-center text-sm text-indigo-600 hover:text-indigo-800">
                            Lihat Semua Produk →
                        </Link>
                    </div>

                    <!-- Student Balance Stats -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/20 animate-fade-in-up animation-delay-500">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span class="text-2xl animate-float animation-delay-200">💳</span>
                            <span>Saldo Siswa</span>
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400 text-sm">Total Saldo Seluruh Siswa</span>
                                <p class="font-bold text-2xl text-green-600 dark:text-green-400 mt-1">
                                    {{ formatCurrency(stats.totalStudentBalance) }}
                                </p>
                            </div>
                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 dark:text-gray-400">Siswa Aktif</span>
                                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ stats.activeStudents }}/{{ stats.totalStudents }}</span>
                                </div>
                            </div>
                        </div>
                        <Link v-if="hasPermission('module_transactions')" :href="route('transactions.topup.form')" class="mt-4 block text-center text-sm text-indigo-600 hover:text-indigo-800">
                            Top-up Saldo →
                        </Link>
                    </div>

                    <!-- Voucher Stats -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/20 animate-fade-in-up animation-delay-600">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span class="text-2xl animate-float animation-delay-400">🎫</span>
                            <span>Voucher</span>
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Tersedia</span>
                                <span class="font-bold text-green-600">{{ stats.availableVouchers }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Sudah Digunakan</span>
                                <span class="font-bold text-gray-600">{{ stats.usedVouchers }}</span>
                            </div>
                        </div>
                        <div v-if="hasPermission('module_vouchers')" class="mt-4 space-y-2">
                            <Link :href="route('vouchers.create')" class="block text-center text-sm text-indigo-600 hover:text-indigo-800">
                                Generate Voucher →
                            </Link>
                            <Link :href="route('vouchers.redeem.form')" class="block text-center text-sm text-green-600 hover:text-green-800">
                                Redeem Voucher →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Professional Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Sales Trend Chart (Bar) -->
                    <div class="lg:col-span-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/20">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <span class="text-2xl animate-float">📊</span>
                                <span>Trend Penjualan 7 Hari Terakhir</span>
                            </h3>
                        </div>
                        <div class="h-80">
                            <Bar v-if="salesChart && salesChart.length > 0" :data="salesChartData" :options="salesChartOptions" />
                            <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                                Belum ada data penjualan
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status Pie Chart -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/20">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <span class="text-2xl animate-float animation-delay-200">🎯</span>
                                <span>Status Stok</span>
                            </h3>
                        </div>
                        <div class="h-80 flex items-center justify-center">
                            <Doughnut v-if="stats && stats.totalProducts > 0" :data="stockChartData" :options="stockChartOptions" />
                            <div v-else class="text-gray-500 dark:text-gray-400">
                                Belum ada data produk
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products Chart -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-purple-200/50 dark:border-purple-700/50 transform transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/20">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span class="text-2xl animate-float animation-delay-300">🏆</span>
                            <span>Top 5 Produk Terlaris Bulan Ini</span>
                        </h3>
                        <Link v-if="hasPermission('module_reports_sales')" :href="route('reports.sales')" class="text-sm text-indigo-600 hover:text-indigo-800">
                            Lihat Laporan Lengkap →
                        </Link>
                    </div>
                    <div class="h-80">
                        <Bar v-if="topProducts && topProducts.length > 0" :data="topProductsChartData" :options="topProductsChartOptions" />
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            Belum ada penjualan bulan ini
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert & Recent Transactions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Low Stock Alert -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-red-200/50 dark:border-red-700/50 transform transition-all duration-300 hover:shadow-2xl hover:shadow-red-500/20">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <span class="text-red-500 animate-pulse">⚠️</span>
                                <span>Stok Menipis (Perlu Restock)</span>
                            </h3>
                            <Link v-if="hasPermission('module_stock_ins')" :href="route('stock-ins.create')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                + Tambah Stok
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <div v-if="lowStockList.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                ✅ Semua produk stoknya aman
                            </div>
                            <div v-for="product in lowStockList" :key="product.id" class="flex justify-between items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ product.name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ product.category.name }}</p>
                                </div>
                                <div class="text-right">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-sm font-bold',
                                        product.stock === 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                        product.stock <= 5 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                    ]">
                                        {{ product.stock === 0 ? 'HABIS' : product.stock + ' unit' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm overflow-hidden shadow-lg sm:rounded-lg p-6 border border-green-200/50 dark:border-green-700/50 transform transition-all duration-300 hover:shadow-2xl hover:shadow-green-500/20">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <span class="text-2xl animate-float animation-delay-100">📝</span>
                                <span>Transaksi Terbaru</span>
                            </h3>
                            <Link v-if="hasPermission('module_transactions')" :href="route('transactions.index')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                Lihat Semua →
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <div v-if="recentTransactions.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                Belum ada transaksi
                            </div>
                            <div v-for="transaction in recentTransactions" :key="transaction.id" class="flex justify-between items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ transaction.student.user.name }}</p>
                                        <span v-if="transaction.transaction_method" :class="[
                                            'px-2 py-0.5 rounded text-xs font-medium',
                                            transaction.transaction_method === 'manual' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                            transaction.transaction_method === 'rfid' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' :
                                            transaction.transaction_method === 'barcode' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' :
                                            transaction.transaction_method === 'voucher' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                            'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400'
                                        ]">
                                            {{ transaction.transaction_method === 'manual' ? '✍️ Manual' :
                                               transaction.transaction_method === 'rfid' ? '📡 RFID' :
                                               transaction.transaction_method === 'barcode' ? '📊 Barcode' :
                                               transaction.transaction_method === 'voucher' ? '🎫 Voucher' :
                                               '⚙️ System'
                                            }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate mt-1">{{ transaction.description }}</p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-bold text-sm" :class="getTypeColor(transaction.type)">
                                        {{ ['topup', 'credit', 'voucher', 'redeem', 'return'].includes(transaction.type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(transaction.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white transform transition-all duration-300 hover:shadow-2xl hover:shadow-purple-500/50 border border-purple-400/30">
                    <h3 class="font-semibold text-xl mb-4 flex items-center gap-2">
                        <span class="animate-pulse">⚡</span>
                        <span>Quick Actions</span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link v-if="hasPermission('module_pos')" :href="route('pos.index')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg group">
                            <div class="text-3xl mb-2 group-hover:scale-125 transition-transform duration-300">🛒</div>
                            <div class="font-semibold">POS / Kasir</div>
                        </Link>
                        <Link v-if="hasPermission('module_transactions')" :href="route('transactions.topup.form')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg group">
                            <div class="text-3xl mb-2 group-hover:scale-125 transition-transform duration-300">💰</div>
                            <div class="font-semibold">Top-up Saldo</div>
                        </Link>
                        <Link v-if="hasPermission('module_stock_ins')" :href="route('stock-ins.create')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg group">
                            <div class="text-3xl mb-2 group-hover:scale-125 transition-transform duration-300">📦</div>
                            <div class="font-semibold">Tambah Stok</div>
                        </Link>
                        <Link v-if="hasPermission('module_expenses')" :href="route('expenses.create')" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg p-4 text-center transition-all duration-300 transform hover:scale-110 hover:shadow-lg group">
                            <div class="text-3xl mb-2 group-hover:scale-125 transition-transform duration-300">💸</div>
                            <div class="font-semibold">Catat Pengeluaran</div>
                        </Link>
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
