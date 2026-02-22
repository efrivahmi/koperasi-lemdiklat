<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
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

// Balance color (Galaxy-compatible)
const getBalanceColor = (balance) => {
    if (balance > 50000) return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300';
    if (balance >= 10000) return 'bg-amber-500/10 border-amber-500/30 text-amber-300';
    return 'bg-rose-500/10 border-rose-500/30 text-rose-300';
};

const getBalanceIcon = (balance) => {
    if (balance > 50000) return 'text-emerald-400';
    if (balance >= 10000) return 'text-amber-400';
    return 'text-rose-400';
};
</script>

<template>
    <Head :title="`Detail Siswa - ${student.user?.name}`" />

    <AuthenticatedLayout>
        <template #mobileTitle>Siswa</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Detail Siswa</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons (moved from header for mobile) -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <div class="flex flex-wrap gap-2">
                        <button @click="window.print()" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold rounded-lg shadow-lg shadow-purple-500/20 transition-all text-sm">
                            🖨️ <span class="ml-1">Cetak Kartu</span>
                        </button>
                        <Link :href="route('transactions.topup.form')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold rounded-lg shadow-lg shadow-emerald-500/20 transition-all text-sm">
                            💰 <span class="ml-1">Top-up</span>
                        </Link>
                        <Link :href="route('students.edit', student.id)" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                            ✏️ <span class="ml-1">Edit</span>
                        </Link>
                        <Link :href="route('students.index')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                            ← <span class="ml-1">Kembali</span>
                        </Link>
                    </div>
                </div>

                <!-- Profile Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Photo Section -->
                            <div class="md:col-span-1">
                                <div class="aspect-square bg-slate-900/50 rounded-xl overflow-hidden ring-4 ring-indigo-500/20">
                                    <img v-if="student.foto"
                                        :src="`/storage/${student.foto}`"
                                        :alt="student.user?.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-32 h-32 text-slate-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Details -->
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-white mb-1">{{ student.user?.name }}</h3>
                                    <p class="text-slate-400">{{ student.user?.email }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">NISN</label>
                                        <p class="text-lg font-semibold text-white font-mono mt-1">{{ student.nis }}</p>
                                    </div>
                                    <div v-if="student.nisn" class="bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">NISN Nasional</label>
                                        <p class="text-lg font-semibold text-white font-mono mt-1">{{ student.nisn }}</p>
                                    </div>
                                    <div class="bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">Kelas</label>
                                        <p class="text-lg text-white mt-1">{{ student.kelas }}</p>
                                    </div>
                                    <div class="bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">Jurusan</label>
                                        <p class="text-lg text-white mt-1">{{ student.jurusan }}</p>
                                    </div>
                                    <div class="md:col-span-2 bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">Alamat</label>
                                        <p class="text-white mt-1">{{ student.alamat || '-' }}</p>
                                    </div>
                                    <div class="bg-slate-900/40 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-indigo-300 uppercase tracking-wider">Status RFID</label>
                                        <div class="mt-2">
                                            <span v-if="student.rfid_uid" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif
                                            </span>
                                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-600/50 text-slate-300 border border-slate-500/30">
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
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div :class="['p-8 border-l-4', getBalanceColor(student.balance)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-400 mb-2">Saldo Saat Ini</p>
                                <p class="text-4xl font-bold text-white">{{ formatCurrency(student.balance) }}</p>
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
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-indigo-500/10">
                            <p class="text-sm text-indigo-300 mb-1">Total Transaksi</p>
                            <p class="text-3xl font-bold text-white">{{ totalTransactions }}</p>
                            <p class="text-xs text-indigo-400 mt-1">transaksi</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-rose-500/10">
                            <p class="text-sm text-rose-300 mb-1">Total Pengeluaran</p>
                            <p class="text-2xl font-bold text-white">{{ formatCurrency(totalSpent) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-purple-500/10">
                            <p class="text-sm text-purple-300 mb-1">Rata-rata per Transaksi</p>
                            <p class="text-2xl font-bold text-white">{{ formatCurrency(averageTransaction) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="border-b border-white/10">
                        <nav class="-mb-px flex">
                            <button
                                @click="activeTab = 'transactions'"
                                :class="[
                                    'py-4 px-6 border-b-2 font-medium text-sm transition-all',
                                    activeTab === 'transactions'
                                        ? 'border-indigo-400 text-indigo-300'
                                        : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600'
                                ]"
                            >
                                Riwayat Transaksi
                            </button>
                            <button
                                @click="activeTab = 'purchases'"
                                :class="[
                                    'py-4 px-6 border-b-2 font-medium text-sm transition-all',
                                    activeTab === 'purchases'
                                        ? 'border-indigo-400 text-indigo-300'
                                        : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600'
                                ]"
                            >
                                Riwayat Pembelian
                            </button>
                        </nav>
                    </div>

                    <!-- Transaction History Tab -->
                    <div v-if="activeTab === 'transactions'" class="p-6">
                        <div v-if="student.transactions && student.transactions.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo Sebelum</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo Sesudah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Keterangan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="transaction in student.transactions" :key="transaction.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">
                                            {{ formatDate(transaction.created_at) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full border"
                                                :class="{
                                                    'bg-emerald-500/20 text-emerald-300 border-emerald-500/30': transaction.type === 'topup',
                                                    'bg-indigo-500/20 text-indigo-300 border-indigo-500/30': transaction.type === 'purchase',
                                                    'bg-rose-500/20 text-rose-300 border-rose-500/30': transaction.type === 'refund'
                                                }"
                                            >
                                                {{ transaction.type === 'topup' ? 'Top-up' : transaction.type === 'purchase' ? 'Pembelian' : 'Pembatalan' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span
                                                class="text-lg font-bold"
                                                :class="{
                                                    'text-emerald-300': transaction.type === 'topup',
                                                    'text-rose-300': transaction.type === 'purchase',
                                                    'text-indigo-300': transaction.type === 'refund'
                                                }"
                                            >
                                                {{ transaction.type === 'topup' || transaction.type === 'refund' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">
                                            {{ formatCurrency(transaction.balance_before) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-white">
                                            {{ formatCurrency(transaction.balance_after) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-slate-400">
                                            {{ transaction.description || '-' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="transaction.creator" :timestamp="transaction.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="transaction.updater" :timestamp="transaction.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-200">Belum ada transaksi</h3>
                            <p class="mt-1 text-sm text-slate-400">Riwayat transaksi siswa akan muncul di sini.</p>
                        </div>
                    </div>

                    <!-- Purchase History Tab -->
                    <div v-if="activeTab === 'purchases'" class="p-6">
                        <div v-if="student.sales && student.sales.length > 0" class="space-y-4">
                            <div v-for="sale in student.sales" :key="sale.id" class="bg-slate-900/40 border border-white/5 rounded-xl p-4 hover:bg-slate-900/60 transition-colors">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-sm text-slate-400">{{ formatDate(sale.created_at) }}</p>
                                        <p class="font-mono text-sm text-slate-500">ID: #{{ sale.id }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-slate-400">Total</p>
                                        <p class="text-xl font-bold text-emerald-300">{{ formatCurrency(sale.total) }}</p>
                                    </div>
                                </div>
                                <div v-if="sale.sale_items" class="space-y-2">
                                    <div v-for="item in sale.sale_items" :key="item.id" class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-200">{{ item.product?.name }}</span>
                                            <span class="text-slate-500">x{{ item.quantity }}</span>
                                        </div>
                                        <span class="font-semibold text-white">{{ formatCurrency(item.quantity * item.price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-200">Belum ada pembelian</h3>
                            <p class="mt-1 text-sm text-slate-400">Riwayat pembelian siswa akan muncul di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
