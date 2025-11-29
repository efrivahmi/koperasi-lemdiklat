<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Portal Siswa</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Selamat datang, {{ student.user.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Student Info Card -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-8 rounded-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">NISN</p>
                            <p class="text-2xl font-bold">{{ student.nis }}</p>
                            <p class="mt-4 text-sm opacity-90">Kelas</p>
                            <p class="text-lg font-semibold">{{ student.class }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm opacity-90">Saldo Anda</p>
                            <p class="text-4xl font-bold">{{ formatCurrency(stats.balance) }}</p>
                            <Link :href="route('student.transactions')" class="mt-4 inline-block px-6 py-2 bg-white text-purple-600 rounded-md font-semibold hover:bg-gray-100 transition">
                                Riwayat Transaksi
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Transaksi</h3>
                            <span class="text-2xl">📊</span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.totalTransactions }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Semua waktu</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Pengeluaran</h3>
                            <span class="text-2xl">💸</span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats.totalSpent) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Semua waktu</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Pengeluaran Bulan Ini</h3>
                            <span class="text-2xl">📅</span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats.monthlySpending) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">30 hari terakhir</p>
                    </div>
                </div>

                <!-- Spending Chart -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-bold mb-6 text-gray-900 dark:text-gray-100">Pengeluaran 7 Hari Terakhir</h3>
                    <div class="space-y-4">
                        <div v-for="day in transactionChart" :key="day.date" class="flex items-center gap-4">
                            <div class="w-32 text-sm font-medium text-gray-600 dark:text-gray-400">{{ day.date }}</div>
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-8 relative">
                                <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-8 rounded-full flex items-center justify-end pr-4 text-white text-xs font-bold"
                                     :style="{ width: (day.spent / Math.max(...transactionChart.map(d => d.spent)) * 100) + '%' }">
                                    <span v-if="day.spent > 0">{{ formatCurrency(day.spent) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Transaksi Terakhir</h3>
                        <Link :href="route('student.transactions')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div v-if="recentTransactions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            Belum ada transaksi
                        </div>
                        <div v-for="transaction in recentTransactions" :key="transaction.id"
                             class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <div class="flex items-center gap-4">
                                <span class="text-3xl">{{ getTransactionIcon(transaction.type) }}</span>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ transaction.type === 'topup' ? 'Top Up Saldo' : transaction.type === 'purchase' ? 'Pembelian' : 'Redeem Voucher' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(transaction.created_at).toLocaleString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold" :class="transaction.type === 'topup' || transaction.type === 'redeem' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ transaction.type === 'topup' || transaction.type === 'redeem' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Saldo: {{ formatCurrency(transaction.ending_balance) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Link :href="route('student.purchases')" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-8 rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold mb-2">Riwayat Pembelian</h3>
                                <p class="text-sm opacity-90">Lihat detail pembelian Anda</p>
                            </div>
                            <span class="text-5xl">🛒</span>
                        </div>
                    </Link>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-8 rounded-lg shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold mb-2">Butuh Top Up?</h3>
                                <p class="text-sm opacity-90">Hubungi petugas koperasi</p>
                            </div>
                            <span class="text-5xl">💳</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
