<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    student: Object,
    transactions: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getTypeIcon = (type) => {
    return type === 'credit' ? '↑' : '↓';
};

const getTypeColor = (type) => {
    return type === 'credit'
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
};

const getTypeBadge = (type) => {
    return type === 'credit'
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};

const getTypeText = (type) => {
    return type === 'credit' ? 'Masuk' : 'Keluar';
};

const getReferenceIcon = (type) => {
    const icons = {
        'sale': '🛒',
        'topup': '💰',
        'voucher': '🎫',
    };
    return icons[type] || '📝';
};

const getReferenceText = (type) => {
    const texts = {
        'sale': 'Pembelian',
        'topup': 'Top-up Manual',
        'voucher': 'Voucher',
    };
    return texts[type] || type;
};

// Calculate summary
const summary = computed(() => {
    const totalCredit = props.transactions.data.reduce((sum, t) =>
        t.type === 'credit' ? sum + parseFloat(t.amount) : sum, 0
    );
    const totalDebit = props.transactions.data.reduce((sum, t) =>
        t.type === 'debit' ? sum + parseFloat(t.amount) : sum, 0
    );
    return {
        totalCredit,
        totalDebit,
        totalTransactions: props.transactions.total
    };
});
</script>

<template>
    <Head :title="`Riwayat Transaksi - ${student.user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Riwayat Transaksi Siswa
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ student.user.name }} (NIS: {{ student.nis }})
                    </p>
                </div>
                <Link
                    :href="route('transactions.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    &larr; Kembali ke Semua Transaksi
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Student Info Card -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-lg sm:rounded-lg p-6 text-white">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">Nama Lengkap</p>
                            <p class="text-xl font-bold">{{ student.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">NISN</p>
                            <p class="text-xl font-bold">{{ student.nis }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">Kelas</p>
                            <p class="text-xl font-bold">{{ student.class }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-100 text-sm mb-1">Saldo Saat Ini</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(student.balance) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Pemasukan</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(summary.totalCredit) }}
                                </p>
                            </div>
                            <div class="text-4xl">💰</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Pengeluaran</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ formatCurrency(summary.totalDebit) }}
                                </p>
                            </div>
                            <div class="text-4xl">🛒</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Transaksi</p>
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ summary.totalTransactions }}
                                </p>
                            </div>
                            <div class="text-4xl">📊</div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Timeline -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="font-semibold text-lg mb-4">Timeline Transaksi</h3>

                        <div v-if="transactions.data.length === 0" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Belum ada transaksi</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500">Siswa ini belum melakukan transaksi apapun</p>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="transaction in transactions.data"
                                :key="transaction.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <!-- Type Badge -->
                                            <span :class="getTypeBadge(transaction.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span class="mr-1" :class="getTypeColor(transaction.type)">
                                                    {{ getTypeIcon(transaction.type) }}
                                                </span>
                                                {{ getTypeText(transaction.type) }}
                                            </span>

                                            <!-- Reference -->
                                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ getReferenceIcon(transaction.reference_type) }}
                                                {{ getReferenceText(transaction.reference_type) }}
                                            </span>

                                            <!-- Date -->
                                            <span class="text-xs text-gray-500 dark:text-gray-500">
                                                {{ new Date(transaction.created_at).toLocaleDateString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    year: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                }) }}
                                            </span>
                                        </div>

                                        <!-- Description -->
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                            {{ transaction.description }}
                                        </p>

                                        <!-- Balance Info -->
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>
                                                Saldo Akhir: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(transaction.ending_balance) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Amount -->
                                    <div class="text-right ml-4">
                                        <div class="text-lg font-bold" :class="getTypeColor(transaction.type)">
                                            {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="transactions.data.length > 0" class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }} transaksi
                            </div>
                            <div class="flex gap-2">
                                <template v-for="link in transactions.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                                        ]"
                                        v-html="link.label"
                                        :preserve-scroll="true"
                                    />
                                    <span
                                        v-else
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            'bg-white dark:bg-gray-700 text-gray-400 dark:text-gray-500 opacity-50 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
