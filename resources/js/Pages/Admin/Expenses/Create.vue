<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({ description: '', category: '', amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '' });
</script>

<template>
    <Head title="Tambah Pengeluaran" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengeluaran</h2></template>
        <div class="py-6 sm:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
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
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                            >
                                <span v-if="!form.processing">Simpan</span>
                                <span v-else>Menyimpan...</span>
                            </button>
                            <Link :href="route('expenses.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
