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
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Portal Guru</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Selamat datang, {{ teacher.user.name }}</p>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-8 rounded-lg shadow-lg">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm opacity-90">NIP</p>
                            <p class="text-2xl font-bold">{{ teacher.nip }}</p>
                            <p class="mt-4 text-sm opacity-90">Jabatan</p>
                            <p class="text-lg font-semibold">{{ teacher.jabatan || '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm opacity-90">Saldo Anda</p>
                            <p class="text-4xl font-bold">{{ formatCurrency(stats.balance) }}</p>
                            <Link :href="route('teacher.transactions')" class="mt-4 inline-block px-6 py-2 bg-white text-purple-600 rounded-md font-semibold hover:bg-gray-100">Riwayat</Link>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-sm text-gray-600 dark:text-gray-400">Total Transaksi</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ stats.totalTransactions }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-sm text-gray-600 dark:text-gray-400">Total Belanja</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ formatCurrency(stats.totalSpent) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <h3 class="text-sm text-gray-600 dark:text-gray-400">Total Tabungan</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ formatCurrency(stats.totalSavings || 0) }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Transaksi Terakhir</h3>
                    <div class="space-y-3">
                        <div v-for="tx in recentTransactions" :key="tx.id" class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ tx.type }}</p>
                                <p class="text-sm text-gray-500">{{ new Date(tx.created_at).toLocaleDateString('id-ID') }}</p>
                            </div>
                            <p class="font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(tx.amount) }}</p>
                        </div>
                        <div v-if="recentTransactions.length === 0" class="text-center text-gray-400 py-8">Belum ada transaksi</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
