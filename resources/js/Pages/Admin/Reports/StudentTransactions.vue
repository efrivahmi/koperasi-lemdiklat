<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuditInfo from '@/Components/AuditInfo.vue';

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
    router.get(route('reports.student-transactions'), searchForm.value, { preserveState: true });
};

const exportToExcel = () => {
    window.location.href = route('reports.student-transactions.export', searchForm.value);
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
</script>

<template>
    <Head title="Laporan Transaksi Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">Laporan Transaksi Siswa</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>Error:</strong> {{ error }}
                </div>

                <!-- Filter Section -->
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-base sm:text-lg mb-4 text-gray-800 dark:text-gray-200">Filter Laporan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <input
                                type="date"
                                v-model="searchForm.date_from"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base py-2"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <input
                                type="date"
                                v-model="searchForm.date_to"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base py-2"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Kelas</label>
                            <select
                                v-model="searchForm.class"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base py-2"
                            >
                                <option value="">Semua Kelas</option>
                                <option v-for="kelas in classes" :key="kelas" :value="kelas">{{ kelas }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Siswa</label>
                            <select
                                v-model="searchForm.student_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base py-2"
                            >
                                <option value="">Semua Siswa</option>
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.name }} ({{ student.nis }})
                                </option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Cari Siswa</label>
                            <input
                                type="text"
                                v-model="searchForm.search"
                                placeholder="Cari berdasarkan nama atau NIS siswa..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base py-2"
                                @keyup.enter="search"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <button
                            @click="search"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Tampilkan
                        </button>
                        <button
                            @click="searchForm = { date_from: '', date_to: '', class: '', student_id: '', search: '' }; search();"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>
                        <button
                            @click="exportToExcel"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Riwayat Transaksi Pembelian Siswa</h3>
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Siswa</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">NISN</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kelas</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Item</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Diubah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">#{{ transaction.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ formatDate(transaction.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ transaction.student?.user?.name || 'Tunai' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ transaction.student?.nis || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ transaction.student?.kelas || '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div v-if="transaction.sale_items && transaction.sale_items.length > 0">
                                                {{ transaction.sale_items.length }} item
                                            </div>
                                            <div v-else>-</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(transaction.total) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <AuditInfo :user="transaction.creator" :timestamp="transaction.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <AuditInfo :user="transaction.updater" :timestamp="transaction.updated_at" label="Diubah" />
                                        </td>
                                    </tr>
                                    <tr v-if="transactions.data.length === 0">
                                        <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
                    <div v-if="transactions.data.length > 0" class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 border-t dark:border-gray-700">
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
                            Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }}
                        </div>
                        <div class="flex gap-1 sm:gap-2 justify-center sm:justify-end overflow-x-auto pb-2 sm:pb-0">
                            <template v-for="link in transactions.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm rounded transition whitespace-nowrap',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    :class="'px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm rounded bg-gray-200 dark:bg-gray-800 text-gray-400 cursor-not-allowed whitespace-nowrap'"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
