<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
    purchases: Object,
    filters: Object,
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    payment_method: props.filters?.payment_method || '',
});

const search = () => {
    router.get(route('student.purchases'), searchForm.value, { preserveState: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getPaymentBadge = (method) => {
    return method === 'cash'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};
</script>

<template>
    <Head title="Riwayat Pembelian" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Riwayat Pembelian</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ student.user.name }} ({{ student.nis }})</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Saldo Saat Ini</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ formatCurrency(student.balance) }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Filter Pembelian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Metode Pembayaran</label>
                            <select v-model="searchForm.payment_method" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Semua Metode</option>
                                <option value="cash">Tunai</option>
                                <option value="balance">Saldo</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button @click="search" class="flex-1 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold">
                                Tampilkan
                            </button>
                            <button @click="() => { searchForm = { date_from: '', date_to: '', payment_method: '' }; search(); }"
                                class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Purchases List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Total Pembelian:</strong> {{ purchases.total }} •
                            Menampilkan {{ purchases.from }}-{{ purchases.to }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Tanggal & Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Barang</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Total QTY</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Metode</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="purchases.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada pembelian ditemukan
                                    </td>
                                </tr>
                                <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div class="font-semibold">{{ new Date(purchase.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(purchase.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="max-w-md">
                                            <span v-for="(item, idx) in purchase.sale_items" :key="item.id">
                                                {{ item.product.name }} ({{ item.quantity }}x){{ idx < purchase.sale_items.length - 1 ? ', ' : '' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 font-bold">
                                            {{ purchase.sale_items.reduce((sum, item) => sum + item.quantity, 0) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPaymentBadge(purchase.payment_method)" class="px-3 py-1 rounded-full text-xs font-semibold">
                                            {{ purchase.payment_method === 'cash' ? '💵 Tunai' : '💳 Saldo' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(purchase.total) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 flex justify-between items-center border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan {{ purchases.from }} - {{ purchases.to }} dari {{ purchases.total }} pembelian
                        </div>
                        <div class="flex gap-2">
                            <Link v-for="link in purchases.links" :key="link.label" :href="link.url"
                                :class="['px-3 py-2 text-sm rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                                v-html="link.label"
                                :preserve-scroll="true" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
