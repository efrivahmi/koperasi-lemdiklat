<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    expense: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getCategoryBadgeClass = (category) => {
    const classes = {
        'operasional': 'bg-blue-100 text-blue-800',
        'pembelian': 'bg-green-100 text-green-800',
        'gaji': 'bg-purple-100 text-purple-800',
        'listrik': 'bg-yellow-100 text-yellow-800',
        'air': 'bg-cyan-100 text-cyan-800',
        'internet': 'bg-indigo-100 text-indigo-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'lainnya': 'bg-gray-100 text-gray-800',
    };
    return classes[category] || 'bg-gray-100 text-gray-800';
};

const getCategoryLabel = (category) => {
    const labels = {
        'operasional': 'Operasional',
        'pembelian': 'Pembelian',
        'gaji': 'Gaji',
        'listrik': 'Listrik',
        'air': 'Air',
        'internet': 'Internet',
        'maintenance': 'Maintenance',
        'lainnya': 'Lainnya',
    };
    return labels[category] || category;
};
</script>

<template>
    <Head title="Detail Pengeluaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Pengeluaran
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('expenses.edit', expense.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('expenses.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Expense Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <!-- Category Badge -->
                        <div class="flex justify-center mb-6">
                            <span
                                class="px-6 py-2 text-lg font-bold rounded-full"
                                :class="getCategoryBadgeClass(expense.category)"
                            >
                                {{ getCategoryLabel(expense.category) }}
                            </span>
                        </div>

                        <!-- Amount Display -->
                        <div class="text-center mb-8 py-6 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg border-2 border-red-200">
                            <p class="text-sm text-red-600 mb-2 font-medium">Total Pengeluaran</p>
                            <p class="text-5xl font-bold text-red-600">{{ formatCurrency(expense.amount) }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="text-sm text-gray-500 block mb-2">Deskripsi</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-gray-900 text-lg leading-relaxed">
                                    {{ expense.description || 'Tidak ada deskripsi' }}
                                </p>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <label class="text-sm text-blue-600 block mb-2">Tanggal Pengeluaran</label>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-lg font-semibold text-blue-900">
                                        {{ new Date(expense.expense_date).toLocaleDateString('id-ID', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        }) }}
                                    </p>
                                </div>
                            </div>

                            <div class="bg-purple-50 rounded-lg p-4">
                                <label class="text-sm text-purple-600 block mb-2">ID Pengeluaran</label>
                                <p class="text-lg font-mono font-semibold text-purple-900">#{{ expense.id }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pencatatan</h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                        <span class="text-xl font-bold text-white">
                                            {{ expense.user?.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500 mb-1">Dicatat oleh</p>
                                    <p class="font-semibold text-gray-900 text-lg">{{ expense.user?.name || 'N/A' }}</p>
                                    <p class="text-sm text-gray-600">{{ expense.user?.email }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500 block mb-1">Dibuat Pada</label>
                                    <p class="text-gray-900">{{ formatDate(expense.created_at) }}</p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500 block mb-1">Terakhir Update</label>
                                    <p class="text-gray-900">{{ formatDate(expense.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tentang Kategori</h3>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <span v-if="expense.category === 'operasional'">Pengeluaran untuk kebutuhan operasional harian koperasi.</span>
                                        <span v-else-if="expense.category === 'pembelian'">Pengeluaran untuk pembelian barang atau jasa.</span>
                                        <span v-else-if="expense.category === 'gaji'">Pengeluaran untuk pembayaran gaji karyawan.</span>
                                        <span v-else-if="expense.category === 'listrik'">Pengeluaran untuk pembayaran tagihan listrik.</span>
                                        <span v-else-if="expense.category === 'air'">Pengeluaran untuk pembayaran tagihan air.</span>
                                        <span v-else-if="expense.category === 'internet'">Pengeluaran untuk pembayaran tagihan internet.</span>
                                        <span v-else-if="expense.category === 'maintenance'">Pengeluaran untuk perawatan dan perbaikan.</span>
                                        <span v-else>Pengeluaran kategori lainnya.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
