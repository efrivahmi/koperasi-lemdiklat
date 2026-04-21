<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    transactions: Object,
    classes: Array,
    students: Array,
    filters: Object,
    error: String,
});

const searchForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    class: props.filters?.class || '',
    student_id: props.filters?.student_id || '',
    search: props.filters?.search || '',
});

const search = () => {
    router.get(route(route().current()), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route(route().current() + '.export', searchForm.value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

import ThermalPrintLayout from '@/Components/ThermalPrintLayout.vue';

const printThermal = () => {
    window.print();
};
</script>

<template>
    <Head title="Laporan Transaksi Siswa" />

    <AuthenticatedLayout>
        <template #mobileTitle>Laporan</template>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-white leading-tight">Laporan Transaksi Siswa</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <div v-if="error" class="bg-rose-500/20 border border-rose-500/50 text-rose-400 px-4 py-3 rounded-lg shadow-sm">
                    <strong>Error:</strong> {{ error }}
                </div>

                <!-- Filter Section -->
                <div class="bg-slate-800/50 backdrop-blur-md p-4 sm:p-6 rounded-xl border border-white/10 shadow-xl">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-white">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Dari Tanggal</label>
                            <input
                                type="date"
                                v-model="searchForm.date_from"
                                class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Sampai Tanggal</label>
                            <input
                                type="date"
                                v-model="searchForm.date_to"
                                class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Kelas</label>
                            <select
                                v-model="searchForm.class"
                                class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors"
                            >
                                <option value="">Semua Kelas</option>
                                <option v-for="kelas in classes" :key="kelas" :value="kelas">{{ kelas }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-300">Siswa</label>
                            <select
                                v-model="searchForm.student_id"
                                class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors"
                            >
                                <option value="">Semua Siswa</option>
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.name }} ({{ student.nis }})
                                </option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="block text-sm font-medium mb-1 text-slate-300">Cari Siswa</label>
                            <input
                                type="text"
                                v-model="searchForm.search"
                                placeholder="Cari berdasarkan nama atau NIS siswa..."
                                class="w-full rounded-lg bg-slate-900/70 border-slate-600 text-white text-sm sm:text-base py-2 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors"
                                @keyup.enter="search"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <button
                            @click="search"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Tampilkan
                        </button>
                        <button v-if="can('reports.print')"
                            @click="printThermal"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-700 hover:bg-slate-600 border border-white/10 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Thermal
                        </button>
                        <button
                            @click="searchForm = { date_from: '', date_to: '', class: '', student_id: '', search: '' }; search();"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-600 hover:bg-slate-500 active:bg-slate-700 border border-white/10 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>
                        <button v-if="can('reports.export')"
                            @click="exportToExcel"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold mb-4 text-white">Riwayat Transaksi Pembelian Siswa</h3>
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-lg border border-white/10">
                                    <table class="min-w-full divide-y divide-white/10">
                                        <thead class="bg-slate-900/80">
                                            <tr>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">ID</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Siswa</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">NISN</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Kelas</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Item</th>
                                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Total</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Dibuat</th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Diubah</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-transparent divide-y divide-white/5">
                                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-slate-700/30 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">#{{ transaction.id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">{{ formatDate(transaction.created_at) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                                                    {{ transaction.student?.user?.name || 'Tunai' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                                    {{ transaction.student?.nis || '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                                    {{ transaction.student?.kelas || '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-300">
                                                    <div v-if="transaction.sale_items && transaction.sale_items.length > 0">
                                                        {{ transaction.sale_items.length }} item
                                                    </div>
                                                    <div v-else>-</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-emerald-400">
                                                    {{ formatCurrency(transaction.total) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                                    <AuditInfo :user="transaction.creator" :timestamp="transaction.created_at" label="Dibuat" />
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                                    <AuditInfo :user="transaction.updater" :timestamp="transaction.updated_at" label="Diubah" />
                                                </td>
                                            </tr>
                                            <tr v-if="transactions.data.length === 0">
                                                <td colspan="9" class="px-6 py-12 text-center text-slate-500">
                                                    Tidak ada data transaksi siswa
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links && transactions.links.length > 3" class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-t border-white/10 bg-slate-900/30">
                        <div class="text-sm text-slate-400 text-center sm:text-left">
                            Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }}
                        </div>
                        <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-end">
                            <template v-for="link in transactions.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 text-sm rounded-lg transition-colors border',
                                        link.active
                                            ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20'
                                            : 'bg-slate-700/50 border-white/5 text-slate-300 hover:bg-slate-600/50'
                                    ]"
                                    v-html="link.label"
                                    preserve-scroll
                                />
                                <span
                                    v-else
                                    :class="'px-4 py-2 text-sm rounded-lg border border-white/5 bg-slate-800/30 text-slate-500 cursor-not-allowed opacity-50'"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thermal Print Layout -->
        <ThermalPrintLayout
            title="LAPORAN TRANSAKSI SISWA"
            :periode="`${searchForm.date_from || 'Awal'} s/d ${searchForm.date_to || 'Hari Ini'}`"
            :user="$page.props.auth.user"
        >
            <div style="margin-bottom: 10px; font-size: 10px; font-weight: bold; border-bottom: 1px dashed black; padding-bottom: 5px;">
                Total Transaksi: {{ transactions.total }} Data
            </div>

            <div v-for="transaction in transactions.data" :key="transaction.id" style="margin-bottom: 8px; border-bottom: 1px dashed #ccc; padding-bottom: 4px;">
                <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px;">
                    #{{ transaction.id }} - {{ formatCurrency(transaction.total) }}
                </div>
                <div style="font-size: 9px; margin-bottom: 2px;">
                    {{ formatDate(transaction.created_at) }}
                </div>
                <div style="font-size: 9px;">
                    Siswa: {{ transaction.student?.user?.name || '-' }}
                </div>
                <div style="font-size: 9px;">
                    Kelas: {{ transaction.student?.kelas || '-' }} ({{ transaction.student?.nis || '-' }})
                </div>
            </div>
        </ThermalPrintLayout>
    </AuthenticatedLayout>
</template>

