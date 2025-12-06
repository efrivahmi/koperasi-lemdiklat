<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    expense: Object,
});

const form = useForm({
    description: props.expense.description,
    category: props.expense.category,
    amount: props.expense.amount,
    expense_date: props.expense.expense_date,
    notes: props.expense.notes || '',
});

const submit = () => {
    form.put(route('expenses.update', props.expense.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// Common expense categories
const categories = [
    'Operasional',
    'Pembelian Stok',
    'Gaji',
    'Utilitas',
    'Transportasi',
    'Perawatan',
    'Marketing',
    'Administrasi',
    'Lain-lain'
];
</script>

<template>
    <Head title="Edit Pengeluaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Pengeluaran</h2>
                <Link :href="route('expenses.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Kembali
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <!-- Expense Info Card -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Dicatat Oleh</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ expense.user.name }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Pencatatan</p>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ new Date(expense.created_at).toLocaleDateString('id-ID', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Pengeluaran</label>
                                <input
                                    id="description"
                                    type="text"
                                    v-model="form.description"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    placeholder="Contoh: Pembelian alat tulis, Bayar listrik bulan Januari"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        required
                                    >
                                        <option value="">Pilih Kategori</option>
                                        <option v-for="cat in categories" :key="cat" :value="cat">
                                            {{ cat }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category" class="mt-2" />
                                </div>

                                <!-- Amount -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah / Nominal</label>
                                    <input
                                        id="amount"
                                        type="number"
                                        v-model="form.amount"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        required
                                    />
                                    <InputError :message="form.errors.amount" class="mt-2" />
                                    <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Preview: {{ formatCurrency(form.amount || 0) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Expense Date -->
                            <div>
                                <label for="expense_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Pengeluaran</label>
                                <input
                                    id="expense_date"
                                    type="date"
                                    v-model="form.expense_date"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                />
                                <InputError :message="form.errors.expense_date" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Tanggal ketika pengeluaran terjadi (bisa berbeda dengan tanggal pencatatan)
                                </p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    rows="4"
                                    placeholder="Tambahkan catatan detail, nomor invoice, atau informasi penting lainnya..."
                                ></textarea>
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>

                            <!-- Summary Card -->
                            <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 mb-2">Ringkasan Pengeluaran</h3>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Kategori:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ form.category || '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Nominal:</span>
                                        <span class="font-bold text-lg text-red-600 dark:text-red-400">
                                            {{ formatCurrency(form.amount || 0) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Tanggal:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ form.expense_date ? new Date(form.expense_date).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric'
                                            }) : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                        <span v-if="form.processing">Menyimpan...</span>
                                        <span v-else>Simpan Perubahan</span>
                                    </button>
                                    <Link
                                        :href="route('expenses.index')"
                                        class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                                    >
                                        Batal
                                    </Link>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Terakhir diupdate: {{ new Date(expense.updated_at).toLocaleDateString('id-ID') }}
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
