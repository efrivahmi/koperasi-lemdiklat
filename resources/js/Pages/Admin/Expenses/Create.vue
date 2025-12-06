<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({ description: '', category: '', amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '' });
</script>

<template>
    <Head title="Tambah Pengeluaran" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Biaya Operasional</h2></template>
        <div class="py-6 sm:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Tambah Pengeluaran Baru</h3>
                            <p class="text-purple-100 text-sm">Catat biaya operasional koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30 p-6 sm:p-8">
                    <form @submit.prevent="form.post(route('expenses.store'))" class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                            <input
                                id="description"
                                v-model="form.description"
                                type="text"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                required
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                            <input
                                id="category"
                                v-model="form.category"
                                type="text"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                placeholder="Listrik, Air, Gaji, dll"
                                required
                            />
                            <InputError :message="form.errors.category" class="mt-2" />
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah (Rp)</label>
                            <input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                min="0"
                                step="0.01"
                                required
                            />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>
                        <div>
                            <label for="expense_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                            <input
                                id="expense_date"
                                v-model="form.expense_date"
                                type="date"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                required
                            />
                            <InputError :message="form.errors.expense_date" class="mt-2" />
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:from-purple-800 active:to-indigo-800 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                            >
                                <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span v-if="!form.processing">Simpan Pengeluaran</span>
                                <span v-else>Menyimpan...</span>
                            </button>
                            <Link :href="route('expenses.index')" class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
