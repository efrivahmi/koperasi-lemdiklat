<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({ description: '', category: '', amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '' });
</script>

<template>
    <Head title="Tambah Pengeluaran" />
    <AuthenticatedLayout>
        <template #mobileTitle>Pengeluaran</template>
        <template #header><h2 class="font-semibold text-xl text-white leading-tight">Tambah Pengeluaran</h2></template>
        <div class="min-h-screen">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-purple-600/80 via-indigo-600/80 to-blue-600/80 backdrop-blur-md rounded-xl shadow-xl p-6 text-white border border-white/10">
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
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10 p-6 sm:p-8">
                    <form @submit.prevent="form.post(route('expenses.store'))" class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-300 mb-1">Deskripsi</label>
                            <input id="description" v-model="form.description" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" required />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-300 mb-1">Kategori</label>
                            <input id="category" v-model="form.category" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" placeholder="Listrik, Air, Gaji, dll" required />
                            <InputError :message="form.errors.category" class="mt-2" />
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Jumlah (Rp)</label>
                            <input id="amount" v-model="form.amount" type="number" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" min="0" step="0.01" required />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>
                        <div>
                            <label for="expense_date" class="block text-sm font-medium text-slate-300 mb-1">Tanggal</label>
                            <input id="expense_date" v-model="form.expense_date" type="date" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" required />
                            <InputError :message="form.errors.expense_date" class="mt-2" />
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-slate-300 mb-1">Catatan</label>
                            <textarea id="notes" v-model="form.notes" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" rows="3"></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold text-sm transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <span v-if="!form.processing">💾 Simpan Pengeluaran</span>
                                <span v-else>Menyimpan...</span>
                            </button>
                            <Link :href="route('expenses.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition border border-white/10 w-full sm:w-auto">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
