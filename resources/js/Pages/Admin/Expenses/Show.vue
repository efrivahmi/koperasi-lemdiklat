<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({ expense: Object });

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Detail Pengeluaran" />
    <AuthenticatedLayout>
        <template #mobileTitle>Pengeluaran</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Detail Pengeluaran</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <div class="flex flex-wrap gap-2">
                        <Link v-if="can('expenses.edit')" :href="route('expenses.edit', expense.id)" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                            ✏️ <span class="ml-1">Edit</span>
                        </Link>
                        <Link :href="route('expenses.index')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                            ← <span class="ml-1">Kembali</span>
                        </Link>
                    </div>
                </div>

                <!-- Amount Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-8 border-l-4 bg-rose-500/10 border-rose-500/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-400 mb-1">Total Pengeluaran</p>
                                <p class="text-4xl font-bold text-rose-300">{{ formatCurrency(expense.amount) }}</p>
                            </div>
                            <div>
                                <span class="px-4 py-2 text-sm font-bold rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">{{ expense.category }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6 space-y-6">
                    <div>
                        <label class="text-xs text-indigo-300 uppercase tracking-wider">Deskripsi</label>
                        <div class="mt-2 bg-slate-900/40 rounded-lg p-4 border border-white/5">
                            <p class="text-white text-lg leading-relaxed">{{ expense.description || 'Tidak ada deskripsi' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-indigo-500/10 rounded-xl p-4 border border-indigo-500/20">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Tanggal Pengeluaran</label>
                            <p class="text-lg font-semibold text-white mt-1">{{ new Date(expense.expense_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                        </div>
                        <div class="bg-purple-500/10 rounded-xl p-4 border border-purple-500/20">
                            <label class="text-xs text-purple-300 uppercase tracking-wider">ID Pengeluaran</label>
                            <p class="text-lg font-mono font-semibold text-white mt-1">#{{ expense.id }}</p>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Informasi Pencatatan</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-4 bg-slate-900/40 rounded-lg border border-white/5">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-xl font-bold text-white">{{ expense.user?.name?.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-400 mb-1">Dicatat oleh</p>
                                <p class="font-semibold text-white text-lg">{{ expense.user?.name || 'N/A' }}</p>
                                <p class="text-sm text-slate-400">{{ expense.user?.email }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                                <label class="text-xs text-indigo-300 uppercase tracking-wider">Dibuat Pada</label>
                                <p class="text-white mt-1">{{ formatDate(expense.created_at) }}</p>
                            </div>
                            <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                                <label class="text-xs text-indigo-300 uppercase tracking-wider">Terakhir Update</label>
                                <p class="text-white mt-1">{{ formatDate(expense.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
