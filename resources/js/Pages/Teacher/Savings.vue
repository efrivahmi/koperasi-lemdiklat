<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ teacher: Object, savings: Object, stats: Object, filters: Object });
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Tabungan Saya" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold">Tabungan Saya</h2></template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-green-100 dark:bg-green-900/30 p-6 rounded-lg">
                        <h3 class="text-sm text-green-700 dark:text-green-400">Total Setoran</h3>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-300 mt-2">{{ formatCurrency(stats.totalDeposits) }}</p>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900/30 p-6 rounded-lg">
                        <h3 class="text-sm text-red-700 dark:text-red-400">Total Penarikan</h3>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-300 mt-2">{{ formatCurrency(stats.totalWithdrawals) }}</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-6 rounded-lg">
                        <h3 class="text-sm text-blue-700 dark:text-blue-400">Total Tabungan</h3>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-300 mt-2">{{ formatCurrency(stats.netSavings) }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Riwayat Tabungan</h3>
                    <div v-for="saving in savings.data" :key="saving.id" class="flex justify-between py-3 border-b">
                        <div>
                            <p :class="saving.type === 'deposit' ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">{{ saving.type === 'deposit' ? 'Setoran' : 'Penarikan' }}</p>
                            <p class="text-sm text-gray-500">{{ formatDate(saving.transaction_date) }}</p>
                        </div>
                        <p class="font-bold" :class="saving.type === 'deposit' ? 'text-green-600' : 'text-red-600'">{{ saving.type === 'deposit' ? '+' : '-' }} {{ formatCurrency(saving.amount) }}</p>
                    </div>
                    <p v-if="savings.data.length === 0" class="text-center text-gray-400 py-8">Belum ada transaksi tabungan</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
