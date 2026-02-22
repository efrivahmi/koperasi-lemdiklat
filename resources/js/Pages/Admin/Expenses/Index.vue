<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

defineProps({ expenses: Object, filters: Object });

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID');
</script>

<template>
    <Head title="Biaya Operasional" />
    <AuthenticatedLayout>
        <template #mobileTitle>Pengeluaran</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Biaya Operasional</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Toolbar -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-xl p-4">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-300">Catat dan kelola pengeluaran operasional</span>
                        </div>
                        <Link v-if="can('expenses.create')" :href="route('expenses.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
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

                <!-- Table -->
                <div v-else class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-4 sm:p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Kategori</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jumlah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ formatDate(expense.expense_date) }}</td>
                                        <td class="px-4 py-4 text-sm text-white">{{ expense.description }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30">{{ expense.category }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-rose-300">{{ formatCurrency(expense.amount) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="expense.creator" :timestamp="expense.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="expense.updater" :timestamp="expense.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <Link v-if="can('expenses.show')" :href="route('expenses.show', expense.id)" class="text-sky-400 hover:text-sky-300 transition-colors">Detail</Link>
                                                <Link v-if="can('expenses.edit')" :href="route('expenses.edit', expense.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</Link>
                                                <button v-if="can('expenses.delete')" @click="router.delete(route('expenses.destroy', expense.id))" class="text-rose-400 hover:text-rose-300 transition-colors">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                            <p class="text-sm text-slate-400">
                                Menampilkan <span class="font-semibold text-slate-200">{{ expenses.from || 0 }}</span> - <span class="font-semibold text-slate-200">{{ expenses.to || 0 }}</span> dari <span class="font-semibold text-slate-200">{{ expenses.total }}</span> pengeluaran
                            </p>
                            <div v-if="expenses.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                <template v-for="link in expenses.links" :key="link.label">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" :class="['px-3 py-2 rounded-lg text-sm transition-all', link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border border-white/10 hover:bg-slate-700 text-slate-300']" />
                                    <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm bg-slate-800/50 text-slate-500 opacity-50 cursor-not-allowed" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
