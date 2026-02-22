<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ expense: Object });

const form = useForm({
    description: props.expense.description,
    category: props.expense.category,
    amount: props.expense.amount,
    expense_date: props.expense.expense_date,
    notes: props.expense.notes || '',
});

const submit = () => form.put(route('expenses.update', props.expense.id));

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);

const categories = ['Operasional', 'Pembelian Stok', 'Gaji', 'Utilitas', 'Transportasi', 'Perawatan', 'Marketing', 'Administrasi', 'Lain-lain'];
</script>

<template>
    <Head title="Edit Pengeluaran" />
    <AuthenticatedLayout>
        <template #mobileTitle>Pengeluaran</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Pengeluaran</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('expenses.index')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                            ← <span class="ml-1">Kembali</span>
                        </Link>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-300 mb-1">Deskripsi Pengeluaran</label>
                            <input id="description" type="text" v-model="form.description" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" placeholder="Contoh: Pembelian alat tulis" required autofocus />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-slate-300 mb-1">Kategori</label>
                                <select id="category" v-model="form.category" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" required>
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                                <InputError :message="form.errors.category" class="mt-2" />
                            </div>
                            <div>
                                <label for="amount" class="block text-sm font-medium text-slate-300 mb-1">Jumlah / Nominal</label>
                                <input id="amount" type="number" v-model="form.amount" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" min="0" step="0.01" required />
                                <InputError :message="form.errors.amount" class="mt-2" />
                                <p class="mt-1 text-sm font-medium text-indigo-300">Preview: {{ formatCurrency(form.amount || 0) }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="expense_date" class="block text-sm font-medium text-slate-300 mb-1">Tanggal Pengeluaran</label>
                            <input id="expense_date" type="date" v-model="form.expense_date" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" required />
                            <InputError :message="form.errors.expense_date" class="mt-2" />
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-slate-300 mb-1">Catatan Tambahan</label>
                            <textarea id="notes" v-model="form.notes" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" rows="4" placeholder="Tambahkan catatan detail..."></textarea>
                            <InputError :message="form.errors.notes" class="mt-2" />
                        </div>

                        <!-- Summary -->
                        <div class="p-4 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
                            <h3 class="font-semibold text-indigo-300 mb-2">Ringkasan</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between"><span class="text-slate-400">Kategori:</span><span class="font-medium text-white">{{ form.category || '-' }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Nominal:</span><span class="font-bold text-lg text-rose-300">{{ formatCurrency(form.amount || 0) }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Tanggal:</span><span class="font-medium text-white">{{ form.expense_date ? new Date(form.expense_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}</span></div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold text-sm transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>💾 Simpan Perubahan</span>
                            </button>
                            <Link :href="route('expenses.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
