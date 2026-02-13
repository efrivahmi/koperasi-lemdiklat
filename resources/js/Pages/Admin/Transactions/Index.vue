<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    transactions: Object,
    filters: Object,
    stats: Object,
});

const searchForm = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    transaction_method: props.filters?.transaction_method || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const search = () => {
    router.get(route('transactions.index'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchForm.value = {
        search: '',
        type: '',
        transaction_method: '',
        date_from: '',
        date_to: '',
    };
    search();
};

const getMethodBadge = (method) => {
    const badges = {
        'manual': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'rfid': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'barcode': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        'voucher': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'system': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    };
    return badges[method] || 'bg-gray-100 text-gray-800';
};

const getMethodIcon = (method) => {
    const icons = {
        'manual': '✍️',
        'rfid': '📡',
        'barcode': '📊',
        'voucher': '🎫',
        'system': '⚙️',
    };
    return icons[method] || '📝';
};

const getMethodText = (method) => {
    const texts = {
        'manual': 'Manual',
        'rfid': 'RFID Scanner',
        'barcode': 'Barcode Scanner',
        'voucher': 'Voucher',
        'system': 'System/Auto',
    };
    return texts[method] || method;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getTypeIcon = (type) => {
    const icons = {
        'topup': '↑',
        'redeem': '↑',
        'return': '↑',
        'purchase': '↓',
        'debit': '↓',
    };
    return icons[type] || '•';
};

const getTypeColor = (type) => {
    // Credit types (masuk): topup, redeem, return
    const creditTypes = ['topup', 'redeem', 'return'];
    return creditTypes.includes(type)
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
};

const getTypeBadge = (type) => {
    const creditTypes = ['topup', 'redeem', 'return'];
    return creditTypes.includes(type)
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};

const getTypeText = (type) => {
    const texts = {
        'topup': 'Top-up',
        'purchase': 'Pembelian',
        'redeem': 'Voucher',
        'return': 'Return/Void',
        'debit': 'Debit Manual',
    };
    return texts[type] || type;
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

// Calculate summary statistics
const summary = computed(() => {
    const creditTypes = ['topup', 'redeem', 'return'];
    const totalCredit = props.transactions.data.reduce((sum, t) =>
        creditTypes.includes(t.type) ? sum + parseFloat(t.amount) : sum, 0
    );
    const totalDebit = props.transactions.data.reduce((sum, t) =>
        !creditTypes.includes(t.type) ? sum + parseFloat(t.amount) : sum, 0
    );
    return {
        totalCredit,
        totalDebit,
        netFlow: totalCredit - totalDebit
    };
});
</script>

<template>
    <Head title="Transaksi" />

    <AuthenticatedLayout>
        <template #mobileTitle>Transaksi</template>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Riwayat Transaksi Siswa
                </h2>
                <Link
                    v-if="can('pos.access')"
                    :href="route('transactions.topup.form')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Top-up Saldo
                </Link>
            </div>
        </template>


        <div class="py-6 sm:py-12 bg-gray-100 dark:bg-slate-900 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Total Transaksi -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-slate-700 p-4 transition-colors">
                        <div class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Total Transaksi</div>
                        <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ transactions.total }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Hari ini: {{ stats.total_manual + stats.total_rfid + stats.total_barcode + stats.total_voucher + stats.total_system }}
                        </div>
                    </div>

                    <!-- Omset Tunai (Manual) -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-slate-700 p-4 transition-colors">
                        <div class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Omset Tunai (Manual)</div>
                        <div class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats.total_amount_manual) }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ stats.total_manual }} Transaksi
                        </div>
                    </div>

                    <!-- Omset Non-Tunai -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-slate-700 p-4 transition-colors">
                        <div class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Omset Non-Tunai</div>
                        <div class="mt-1 text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(stats.total_amount_automated) }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ stats.total_rfid + stats.total_barcode + stats.total_voucher }} Transaksi
                        </div>
                    </div>

                    <!-- Voucher Terpakai -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-slate-700 p-4 transition-colors">
                        <div class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Voucher Terpakai</div>
                        <div class="mt-1 text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ stats.total_voucher }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Transaksi via Voucher
                        </div>
                    </div>
                </div>

                <!-- Toolbar Section -->
                <div class="mb-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-sm p-4 transition-colors">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchForm.search"
                                    @input="search"
                                    type="text"
                                    placeholder="Cari siswa/transaksi..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors"
                                />
                            </div>

                            <!-- Filter Type -->
                            <select 
                                v-model="searchForm.type" 
                                @change="search"
                                class="block w-full py-2.5 pl-3 pr-10 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="purchase">Pembelian</option>
                                <option value="topup">Top Up</option>
                                <option value="redeem">Redeem Voucher</option>
                                <option value="return">Retur</option>
                            </select>

                            <!-- Filter Method -->
                            <select 
                                v-model="searchForm.transaction_method" 
                                @change="search"
                                class="block w-full py-2.5 pl-3 pr-10 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors"
                            >
                                <option value="">Semua Metode</option>
                                <option value="manual">Manual</option>
                                <option value="rfid">RFID Card</option>
                                <option value="barcode">Barcode</option>
                                <option value="voucher">Voucher Code</option>
                            </select>

                            <!-- Reset Button -->
                            <button 
                                @click="resetFilters"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium rounded-lg transition-colors"
                            >
                                Reset Filter
                            </button>
                        </div>

                        <!-- Action Button -->
                        <div class="flex-shrink-0">
                            <Link v-if="can('transactions.topup')" :href="route('transactions.topup.form')" class="w-full lg:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span class="hidden sm:inline">Top Up Saldo</span>
                                <span class="sm:hidden">Top Up</span>
                            </Link>
                        </div>
                    </div>
                            </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="transactions.data.length === 0"
                    icon="receipt"
                    title="Belum Ada Transaksi"
                    description="Belum ada riwayat transaksi yang tercatat sistem."
                />

                <!-- Table with Data -->
                <div v-else class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
                    <div class="p-6 text-slate-900 dark:text-white">
                            <form @submit.prevent="search">
                                <label for="transaction-date-from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Dari Tanggal
                                </label>
                                <input
                                    id="transaction-date-from"
                                    name="date_from"
                                    type="date"
                                    v-model="searchForm.date_from"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    aria-label="Filter dari tanggal"
                                />
                            </div>

                            <!-- Date To -->
                            <div>
                                <label for="transaction-date-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Sampai Tanggal
                                </label>
                                <input
                                    id="transaction-date-to"
                                    name="date_to"
                                    type="date"
                                    v-model="searchForm.date_to"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    aria-label="Filter sampai tanggal"
                                />
                            </div>

                            <!-- Buttons -->
                            <div class="md:col-span-4 flex gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    🔍 Cari
                                </button>
                                <button
                                    type="button"
                                    @click="resetFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    ↺ Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Siswa
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tipe
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Metode
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Referensi
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Deskripsi
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nominal
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Saldo Akhir
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider">
                                            Dibuat
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider">
                                            Diubah
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <!-- Date -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div>
                                                {{ new Date(transaction.created_at).toLocaleDateString('id-ID', {
                                                    day: '2-digit',
                                                    month: 'short',
                                                    year: 'numeric'
                                                }) }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ new Date(transaction.created_at).toLocaleTimeString('id-ID', {
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                }) }}
                                            </div>
                                        </td>

                                        <!-- Student -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ transaction.student.user.name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        NIS: {{ transaction.student.nis }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Type -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getTypeBadge(transaction.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span class="mr-1" :class="getTypeColor(transaction.type)">
                                                    {{ getTypeIcon(transaction.type) }}
                                                </span>
                                                {{ getTypeText(transaction.type) }}
                                            </span>
                                        </td>

                                        <!-- Transaction Method -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getMethodBadge(transaction.transaction_method)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span class="mr-1">
                                                    {{ getMethodIcon(transaction.transaction_method) }}
                                                </span>
                                                {{ getMethodText(transaction.transaction_method) }}
                                            </span>
                                        </td>

                                        <!-- Reference -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-1">
                                                <span>{{ getReferenceIcon(transaction.reference_type) }}</span>
                                                <span>{{ getReferenceText(transaction.reference_type) }}</span>
                                            </div>
                                        </td>

                                        <!-- Description -->
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="max-w-xs truncate" :title="transaction.description">
                                                {{ transaction.description }}
                                            </div>
                                        </td>

                                        <!-- Amount -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-semibold" :class="getTypeColor(transaction.type)">
                                                {{ ['topup', 'redeem', 'return'].includes(transaction.type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                            </div>
                                        </td>

                                        <!-- Ending Balance -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ formatCurrency(transaction.ending_balance) }}
                                            </div>
                                        </td>

                                        <!-- Audit Info: Created -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="transaction.creator" :timestamp="transaction.created_at" label="Dibuat" />
                                        </td>

                                        <!-- Audit Info: Updated -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="transaction.updater" :timestamp="transaction.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <tr v-if="transactions.data.length === 0 && (searchForm.search || searchForm.type || searchForm.transaction_method || searchForm.date_from || searchForm.date_to)">
                                        <td colspan="10" class="px-6 py-12 text-center">
                                            <div class="text-gray-400 dark:text-gray-500">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Tidak ada hasil pencarian</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ditemukan transaksi dengan filter yang dipilih.</p>
                                                <button
                                                    @click="resetFilters"
                                                    class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors"
                                                >
                                                    ↺ Reset Filter
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
