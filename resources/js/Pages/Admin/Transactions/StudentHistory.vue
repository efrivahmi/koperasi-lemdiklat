<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ student: Object, transactions: Object });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const getTypeColor = (t) => t === 'credit' ? 'text-emerald-300' : 'text-rose-300';
const getTypeBadge = (t) => t === 'credit' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30';
const getTypeText = (t) => t === 'credit' ? 'Masuk' : 'Keluar';
const getTypeIcon = (t) => t === 'credit' ? '↑' : '↓';
const getReferenceIcon = (t) => ({ 'sale': '🛒', 'topup': '💰', 'voucher': '🎫' }[t] || '📝');
const getReferenceText = (t) => ({ 'sale': 'Pembelian', 'topup': 'Top-up Manual', 'voucher': 'Voucher' }[t] || t);

const summary = computed(() => {
    const totalCredit = props.transactions.data.reduce((s, t) => t.type === 'credit' ? s + parseFloat(t.amount) : s, 0);
    const totalDebit = props.transactions.data.reduce((s, t) => t.type === 'debit' ? s + parseFloat(t.amount) : s, 0);
    return { totalCredit, totalDebit, totalTransactions: props.transactions.total };
});
</script>

<template>
    <Head :title="`Riwayat Transaksi - ${student.user.name}`" />
    <AuthenticatedLayout>
        <template #mobileTitle>Transaksi</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Riwayat Transaksi Siswa</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">{{ student.user.name }} (NIS: {{ student.nis }})</p>
                        </div>
                        <Link :href="route('transactions.index')" class="inline-flex items-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                            ← Kembali
                        </Link>
                    </div>
                </div>

                <!-- Student Info Card -->
                <div class="bg-gradient-to-r from-indigo-600/80 to-purple-600/80 backdrop-blur-md overflow-hidden rounded-xl shadow-xl p-6 text-white border border-white/10">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">Nama Lengkap</p>
                            <p class="text-xl font-bold">{{ student.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">NISN</p>
                            <p class="text-xl font-bold">{{ student.nis }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">Kelas</p>
                            <p class="text-xl font-bold">{{ student.class }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">Saldo Saat Ini</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(student.balance) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-emerald-500/10">
                            <p class="text-sm text-emerald-300 mb-1">Total Pemasukan</p>
                            <p class="text-2xl font-bold text-white">{{ formatCurrency(summary.totalCredit) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-rose-500/10">
                            <p class="text-sm text-rose-300 mb-1">Total Pengeluaran</p>
                            <p class="text-2xl font-bold text-white">{{ formatCurrency(summary.totalDebit) }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                        <div class="p-6 bg-indigo-500/10">
                            <p class="text-sm text-indigo-300 mb-1">Total Transaksi</p>
                            <p class="text-2xl font-bold text-white">{{ summary.totalTransactions }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <h3 class="font-semibold text-lg text-white mb-4">Timeline Transaksi</h3>

                    <div v-if="transactions.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-200">Belum ada transaksi</h3>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="t in transactions.data" :key="t.id" class="border border-white/10 rounded-xl p-4 hover:bg-white/5 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-3 mb-2">
                                        <span :class="getTypeBadge(t.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border">
                                            <span class="mr-1" :class="getTypeColor(t.type)">{{ getTypeIcon(t.type) }}</span>
                                            {{ getTypeText(t.type) }}
                                        </span>
                                        <span class="text-sm text-slate-400">{{ getReferenceIcon(t.reference_type) }} {{ getReferenceText(t.reference_type) }}</span>
                                        <span class="text-xs text-slate-500">{{ new Date(t.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                                    </div>
                                    <p class="text-sm text-slate-300 mb-2">{{ t.description }}</p>
                                    <div class="text-xs text-slate-500">Saldo Akhir: <span class="font-semibold text-white">{{ formatCurrency(t.ending_balance) }}</span></div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-lg font-bold" :class="getTypeColor(t.type)">{{ t.type === 'credit' ? '+' : '-' }}{{ formatCurrency(t.amount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.data.length > 0" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-sm text-slate-400">Menampilkan <span class="font-semibold text-slate-200">{{ transactions.from }}</span> - <span class="font-semibold text-slate-200">{{ transactions.to }}</span> dari <span class="font-semibold text-slate-200">{{ transactions.total }}</span></p>
                        <div v-if="transactions.links.length > 3" class="flex flex-wrap gap-1">
                            <template v-for="link in transactions.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" :preserve-scroll="true" :class="['px-3 py-2 rounded-lg text-sm transition-all', link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border border-white/10 hover:bg-slate-700 text-slate-300']" />
                                <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm bg-slate-800/50 text-slate-500 opacity-50 cursor-not-allowed" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
