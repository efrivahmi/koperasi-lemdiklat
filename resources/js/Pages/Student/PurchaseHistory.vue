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
        <template #mobileTitle>Riwayat Pembelian</template>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white drop-shadow-md">Riwayat Pembelian</h2>
        </template>

        <div class="py-8 min-h-screen relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 relative z-10">
                <!-- Filters -->
                <div class="bg-slate-800/50 backdrop-blur-md p-6 rounded-2xl border border-white/10 shadow-xl shadow-blue-500/5">
                    <h3 class="font-semibold mb-4 text-white flex items-center gap-2">
                        <span class="text-blue-400">🔍</span>
                        Filter Pembelian
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-blue-200/70">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-blue-500 focus:border-blue-500 transition-all" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-blue-200/70">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-blue-500 focus:border-blue-500 transition-all" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider mb-2 text-blue-200/70">Metode Pembayaran</label>
                            <select v-model="searchForm.payment_method" 
                                class="w-full rounded-xl bg-slate-900/50 border-white/10 text-white focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="" class="bg-slate-900">Semua Metode</option>
                                <option value="cash" class="bg-slate-900">Tunai</option>
                                <option value="balance" class="bg-slate-900">Saldo</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button @click="search" 
                                class="flex-1 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                                Tampilkan
                            </button>
                            <button @click="() => { searchForm = { date_from: '', date_to: '', payment_method: '' }; search(); }"
                                class="px-4 py-2.5 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl font-bold border border-white/10 transition-all active:scale-95">
                                ↺
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Purchases List -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-2xl border border-white/10">
                    <div class="p-4 bg-blue-900/20 border-b border-white/10">
                        <p class="text-sm text-blue-200">
                            <strong>Total Pembelian:</strong> {{ purchases.total }} •
                            Menampilkan {{ purchases.from }}-{{ purchases.to }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-200 uppercase tracking-widest">Tanggal & Waktu</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-200 uppercase tracking-widest">Barang</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-blue-200 uppercase tracking-widest">QTY</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-blue-200 uppercase tracking-widest">Metode</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-blue-200 uppercase tracking-widest">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-if="purchases.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                        Tidak ada pembelian ditemukan
                                    </td>
                                </tr>
                                <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-white">{{ new Date(purchase.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</div>
                                        <div class="text-[10px] text-slate-400 group-hover:text-blue-300 transition-colors uppercase tracking-wider">{{ new Date(purchase.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB</div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        <div class="max-w-md line-clamp-2">
                                            <span v-for="(item, idx) in purchase.sale_items" :key="item.id">
                                                {{ item.product.name }} <span class="text-blue-400 font-bold">({{ item.quantity }}x)</span>{{ idx < purchase.sale_items.length - 1 ? ', ' : '' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg bg-blue-500/20 border border-blue-500/20 text-blue-300 text-xs font-bold">
                                            {{ purchase.sale_items.reduce((sum, item) => sum + item.quantity, 0) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="[
                                            'px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm border',
                                            purchase.payment_method === 'cash' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/20' : 'bg-blue-500/20 text-blue-300 border-blue-500/20'
                                        ]">
                                            {{ purchase.payment_method === 'cash' ? '💵 Tunai' : '💳 Saldo' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="text-base font-bold text-emerald-400 tabular-nums">
                                            {{ formatCurrency(purchase.total) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-white/10 bg-slate-900/30">
                        <div class="text-xs font-medium text-slate-400 uppercase tracking-widest">
                            Menampilkan {{ purchases.from }} - {{ purchases.to }} dari {{ purchases.total }}
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="link in purchases.links" :key="link.label">
                                <Link v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-xs font-bold rounded-lg transition-all border', 
                                        link.active 
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 border-blue-500 text-white shadow-lg shadow-blue-500/20' 
                                            : 'bg-slate-800/50 border-white/10 text-slate-400 hover:text-white hover:bg-slate-700/50'
                                    ]"
                                    v-html="link.label"
                                    :preserve-scroll="true" />
                                <span v-else
                                    class="px-3 py-2 text-xs font-bold rounded-lg bg-slate-900/50 border border-white/5 text-slate-600 cursor-not-allowed"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
