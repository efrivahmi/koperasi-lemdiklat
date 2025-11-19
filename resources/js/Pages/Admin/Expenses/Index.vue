<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
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
                <Link :href="route('expenses.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Tambah Pengeluaran</Link>
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
                <div v-else class="bg-white shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="expense in expenses.data" :key="expense.id">
                                <td class="px-6 py-4 text-sm">{{ formatDate(expense.expense_date) }}</td>
                                <td class="px-6 py-4 text-sm">{{ expense.description }}</td>
                                <td class="px-6 py-4 text-sm">{{ expense.category }}</td>
                                <td class="px-6 py-4 text-sm font-semibold">{{ formatCurrency(expense.amount) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <button @click="router.delete(route('expenses.destroy', expense.id))" class="text-red-600 hover:text-red-900">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
