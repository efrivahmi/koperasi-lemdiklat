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
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Biaya Operasional</h2>
                <Link :href="route('expenses.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition">Tambah Pengeluaran</Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
