<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ expenses: Object, filters: Object });

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID');
</script>

<template>
    <Head title="Pengeluaran" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Biaya Operasional</h2>
        </template>
        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <div class="mb-6 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-800 dark:to-gray-800 border border-purple-200 dark:border-purple-500/30 rounded-lg shadow-sm p-4">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Catat dan kelola pengeluaran operasional</span>
                        </div>
                        <Link :href="route('expenses.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Pengeluaran
                        </Link>
                    </div>
                </div>
                <!-- Empty State -->
                <EmptyState
                    v-if="expenses.data.length === 0"
                    icon="cash"
                    title="Belum Ada Pengeluaran"
                    description="Catat pengeluaran operasional koperasi Anda."
                    :action-url="route('expenses.create')"
                    action-text="Tambah Pengeluaran Pertama"
                />

                <!-- Table with Data -->
                <div v-else class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase hidden xl:table-cell">Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase hidden xl:table-cell">Diubah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(expense.expense_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ expense.description }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ expense.category }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(expense.amount) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                    <AuditInfo :user="expense.creator" :timestamp="expense.created_at" label="Dibuat" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                    <AuditInfo :user="expense.updater" :timestamp="expense.updated_at" label="Diubah" />
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button @click="router.delete(route('expenses.destroy', expense.id))" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
