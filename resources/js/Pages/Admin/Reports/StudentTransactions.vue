<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object,
    classes: Array,
    students: Array,
    filters: Object,
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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getPaymentBadge = (method) => {
    return method === 'cash'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};
</script>

<template>
    <Head title="Laporan Transaksi Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">👨‍🎓 Laporan Rekapitulasi Transaksi Siswa</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Detail lengkap transaksi dengan saldo akhir</p>
                </div>
                <button @click="exportToExcel" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-xs uppercase">
                    📥 Export Excel
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Advanced Filters -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>🔍</span> Filter Laporan Detail
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <input type="date" v-model="searchForm.date_from" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <input type="date" v-model="searchForm.date_to" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Kelas</label>
                            <select v-model="searchForm.class" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Semua Kelas</option>
                                <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Siswa</label>
                            <select v-model="searchForm.student_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Semua Siswa</option>
                                <option v-for="student in students" :key="student.id" :value="student.id">
                                    {{ student.name }} ({{ student.nis }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Cari</label>
                            <input type="text" v-model="searchForm.search" placeholder="Nama/NIS..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" />
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button @click="search" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold">
                            🔍 Tampilkan
                        </button>
                        <button @click="() => { searchForm = { date_from: '', date_to: '', class: '', student_id: '', search: '' }; search(); }"
                            class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold">
                            ↺ Reset
                        </button>
                    </div>
                </div>

                <!-- Table with All Required Columns -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-200 dark:border-indigo-800">
                        <p class="text-sm text-indigo-800 dark:text-indigo-200">
                            <strong>Total Transaksi:</strong> {{ transactions.total }} •
                            Menampilkan {{ transactions.from }}-{{ transactions.to }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase">Tanggal & Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase">Nama Siswa</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase">Kelas</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase">Nama Barang</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase">Total Barang (QTY)</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase">Metode</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase">Total Transaksi</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold uppercase">Saldo Akhir Siswa</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-lg font-medium">Tidak ada data transaksi</p>
                                            <p class="text-sm">Coba ubah filter pencarian Anda</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <!-- Tanggal & Waktu -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div class="font-semibold">{{ new Date(transaction.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(transaction.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}</div>
                                    </td>

                                    <!-- Nama Siswa -->
                                    <td class="px-4 py-4 text-sm">
                                        <div v-if="transaction.student" class="font-semibold text-gray-900 dark:text-gray-100">{{ transaction.student.user.name }}</div>
                                        <div v-if="transaction.student" class="text-xs text-gray-500 dark:text-gray-400">NIS: {{ transaction.student.nis }}</div>
                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                    </td>

                                    <!-- Kelas -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <span v-if="transaction.student" class="px-2 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded text-xs font-semibold">
                                            {{ transaction.student.kelas }}
                                        </span>
                                        <span v-else class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    </td>

                                    <!-- Nama Barang -->
                                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="max-w-xs">
                                            <span v-for="(item, idx) in transaction.sale_items" :key="item.id">
                                                {{ item.product.name }} ({{ item.quantity }}x){{ idx < transaction.sale_items.length - 1 ? ', ' : '' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Total Barang (QTY) -->
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 font-bold">
                                            {{ transaction.sale_items.reduce((sum, item) => sum + item.quantity, 0) }}
                                        </span>
                                    </td>

                                    <!-- Metode Pembayaran -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <span :class="getPaymentBadge(transaction.payment_method)" class="px-3 py-1 rounded-full text-xs font-semibold">
                                            {{ transaction.payment_method === 'cash' ? '💵 Tunai' : '💳 Saldo' }}
                                        </span>
                                    </td>

                                    <!-- Total Transaksi -->
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(transaction.total) }}
                                        </span>
                                    </td>

                                    <!-- Saldo Akhir Siswa (from ending_balance) -->
                                    <td class="px-4 py-4 text-right">
                                        <span v-if="transaction.student" class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                            {{ formatCurrency(transaction.student.balance) }}
                                        </span>
                                        <span v-else class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 flex justify-between items-center border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }} transaksi
                        </div>
                        <div class="flex gap-2">
                            <Link v-for="link in transactions.links" :key="link.label" :href="link.url"
                                :class="['px-3 py-2 text-sm rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                                v-html="link.label"
                                :preserve-scroll="true" />
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-l-4 border-purple-500 p-6 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-bold text-purple-900 dark:text-purple-100">📊 Laporan Lengkap Siap Export!</h4>
                            <p class="mt-2 text-sm text-purple-800 dark:text-purple-200">
                                Laporan ini menampilkan <strong>semua kolom yang diminta</strong>: Tanggal & Waktu, Nama Siswa, Kelas, Nama Barang, Total QTY, Metode Pembayaran, Total Transaksi, dan <strong>Saldo Akhir Siswa</strong>.
                            </p>
                            <p class="mt-1 text-sm text-purple-800 dark:text-purple-200">
                                Klik <strong>"Export Excel"</strong> untuk mendapatkan file lengkap yang dapat dibuka di Microsoft Excel atau Google Sheets.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
