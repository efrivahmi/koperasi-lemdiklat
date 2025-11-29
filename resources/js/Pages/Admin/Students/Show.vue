<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
});

const activeTab = ref('transactions');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Statistik
const totalTransactions = props.student.transactions?.length || 0;
const totalSpent = props.student.sales?.reduce((sum, sale) => sum + sale.total, 0) || 0;
const averageTransaction = totalTransactions > 0 ? totalSpent / totalTransactions : 0;

// Balance color
const getBalanceColor = (balance) => {
    if (balance > 50000) return 'bg-green-50 border-green-200 text-green-900';
    if (balance >= 10000) return 'bg-yellow-50 border-yellow-200 text-yellow-900';
    return 'bg-red-50 border-red-200 text-red-900';
};

const getBalanceIcon = (balance) => {
    if (balance > 50000) return 'text-green-600';
    if (balance >= 10000) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <Head :title="`Detail Siswa - ${student.user?.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Siswa
                </h2>
                <div class="flex gap-2">
                    <button @click="window.print()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Cetak Kartu
                    </button>
                    <Link :href="route('students.topup', student.id)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Top-up
                    </Link>
                    <Link :href="route('students.edit', student.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('students.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Profile Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Photo Section -->
                            <div class="md:col-span-1">
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                    <img v-if="student.photo_path"
                                        :src="`/storage/${student.photo_path}`"
                                        :alt="student.user?.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-32 h-32 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Details -->
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ student.user?.name }}</h3>
                                    <p class="text-gray-500">{{ student.user?.email }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500">NISN</label>
                                        <p class="text-lg font-semibold text-gray-900 font-mono">{{ student.nis }}</p>
                                    </div>
                                    <div v-if="student.nisn">
                                        <label class="text-sm text-gray-500">NISN Nasional</label>
                                        <p class="text-lg font-semibold text-gray-900 font-mono">{{ student.nisn }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Kelas</label>
                                        <p class="text-lg text-gray-900">{{ student.kelas }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Jurusan</label>
                                        <p class="text-lg text-gray-900">{{ student.jurusan }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-sm text-gray-500">Alamat</label>
                                        <p class="text-gray-900">{{ student.alamat || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Status RFID</label>
                                        <div class="mt-1">
                                            <span v-if="student.rfid_uid" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif
                                            </span>
                                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Belum Terdaftar
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div :class="['p-8 border-l-4', getBalanceColor(student.balance)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium mb-2">Saldo Saat Ini</p>
                                <p class="text-4xl font-bold">{{ formatCurrency(student.balance) }}</p>
                            </div>
                            <div :class="getBalanceIcon(student.balance)">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-blue-50">
                            <p class="text-sm text-blue-600 mb-1">Total Transaksi</p>
                            <p class="text-3xl font-bold text-blue-900">{{ totalTransactions }}</p>
                            <p class="text-xs text-blue-600 mt-1">transaksi</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-red-50">
                            <p class="text-sm text-red-600 mb-1">Total Pengeluaran</p>
                            <p class="text-2xl font-bold text-red-900">{{ formatCurrency(totalSpent) }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-purple-50">
                            <p class="text-sm text-purple-600 mb-1">Rata-rata per Transaksi</p>
                            <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(averageTransaction) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button
                                @click="activeTab = 'transactions'"
                                :class="[
                                    'py-4 px-6 border-b-2 font-medium text-sm',
                                    activeTab === 'transactions'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Riwayat Transaksi
                            </button>
                            <button
                                @click="activeTab = 'purchases'"
                                :class="[
                                    'py-4 px-6 border-b-2 font-medium text-sm',
                                    activeTab === 'purchases'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Riwayat Pembelian
                            </button>
                        </nav>
                    </div>

                    <!-- Transaction History Tab -->
                    <div v-if="activeTab === 'transactions'" class="p-6">
                        <div v-if="student.transactions && student.transactions.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Sebelum</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Sesudah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="transaction in student.transactions" :key="transaction.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(transaction.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': transaction.type === 'topup',
                                                    'bg-blue-100 text-blue-800': transaction.type === 'purchase',
                                                    'bg-red-100 text-red-800': transaction.type === 'refund'
                                                }"
                                            >
                                                {{ transaction.type === 'topup' ? 'Top-up' : transaction.type === 'purchase' ? 'Pembelian' : 'Pembatalan' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="text-lg font-bold"
                                                :class="{
                                                    'text-green-600': transaction.type === 'topup',
                                                    'text-red-600': transaction.type === 'purchase',
                                                    'text-blue-600': transaction.type === 'refund'
                                                }"
                                            >
                                                {{ transaction.type === 'topup' || transaction.type === 'refund' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(transaction.balance_before) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(transaction.balance_after) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            {{ transaction.description || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada transaksi</h3>
                            <p class="mt-1 text-sm text-gray-500">Riwayat transaksi siswa akan muncul di sini.</p>
                        </div>
                    </div>

                    <!-- Purchase History Tab -->
                    <div v-if="activeTab === 'purchases'" class="p-6">
                        <div v-if="student.sales && student.sales.length > 0" class="space-y-4">
                            <div v-for="sale in student.sales" :key="sale.id" class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-sm text-gray-500">{{ formatDate(sale.created_at) }}</p>
                                        <p class="font-mono text-sm text-gray-600">ID: #{{ sale.id }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Total</p>
                                        <p class="text-xl font-bold text-green-600">{{ formatCurrency(sale.total) }}</p>
                                    </div>
                                </div>
                                <div v-if="sale.sale_items" class="space-y-2">
                                    <div v-for="item in sale.sale_items" :key="item.id" class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700">{{ item.product?.name }}</span>
                                            <span class="text-gray-500">x{{ item.quantity }}</span>
                                        </div>
                                        <span class="font-semibold text-gray-900">{{ formatCurrency(item.quantity * item.price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pembelian</h3>
                            <p class="mt-1 text-sm text-gray-500">Riwayat pembelian siswa akan muncul di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
