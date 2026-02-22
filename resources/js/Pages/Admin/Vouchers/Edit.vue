<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ voucher: Object });

const form = useForm({
    nominal: props.voucher.nominal,
    expired_at: props.voucher.expired_at ? props.voucher.expired_at.split(' ')[0] : '',
    status: props.voucher.status,
});

const submit = () => form.put(route('vouchers.update', props.voucher.id));
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const getStatusBadge = (s) => ({ 'available': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30', 'used': 'bg-slate-500/20 text-slate-300 border-slate-500/30', 'expired': 'bg-rose-500/20 text-rose-300 border-rose-500/30' }[s] || 'bg-slate-500/20 text-slate-300 border-slate-500/30');
const getStatusText = (s) => ({ 'available': 'Tersedia', 'used': 'Sudah Digunakan', 'expired': 'Kadaluarsa' }[s] || s);
</script>

<template>
    <Head title="Edit Voucher" />
    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Voucher</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <Link :href="route('vouchers.index')" class="inline-flex items-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">← Kembali</Link>
                </div>

                <!-- Voucher Info Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Kode Voucher</label>
                            <p class="text-xl font-bold text-indigo-300 font-mono mt-1">{{ voucher.code }}</p>
                        </div>
                        <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Status</label>
                            <div class="mt-1"><span :class="getStatusBadge(voucher.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border">{{ getStatusText(voucher.status) }}</span></div>
                        </div>
                        <div v-if="voucher.student" class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Digunakan Oleh</label>
                            <p class="font-semibold text-white mt-1">{{ voucher.student.user.name }}</p>
                            <p class="text-sm text-slate-400">NIS: {{ voucher.student.nis }}</p>
                        </div>
                        <div v-if="voucher.used_at" class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Tanggal Penggunaan</label>
                            <p class="font-semibold text-white mt-1">{{ new Date(voucher.used_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nominal" class="block text-sm font-medium text-slate-300 mb-1">Nominal Voucher</label>
                                <input id="nominal" type="number" v-model="form.nominal" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" min="1000" step="1000" required :disabled="voucher.status === 'used'" />
                                <InputError :message="form.errors.nominal" class="mt-2" />
                                <p class="mt-1 text-sm text-indigo-300">Preview: {{ formatCurrency(form.nominal || 0) }}</p>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                                <select id="status" v-model="form.status" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" required>
                                    <option value="available">Tersedia</option>
                                    <option value="used">Sudah Digunakan</option>
                                    <option value="expired">Kadaluarsa</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                                <p v-if="form.status === 'used'" class="mt-1 text-sm text-amber-300">Mengubah ke 'Sudah Digunakan' tidak menambah saldo.</p>
                            </div>
                            <div class="md:col-span-2">
                                <label for="expired_at" class="block text-sm font-medium text-slate-300 mb-1">Tanggal Kadaluarsa</label>
                                <input id="expired_at" type="date" v-model="form.expired_at" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" :disabled="voucher.status === 'used'" />
                                <InputError :message="form.errors.expired_at" class="mt-2" />
                                <p class="mt-1 text-sm text-slate-500">Kosongkan jika tidak ada kadaluarsa</p>
                            </div>
                        </div>

                        <!-- Warning -->
                        <div v-if="voucher.status === 'used'" class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                            <p class="text-sm text-amber-300">⚠️ Voucher sudah digunakan. Perubahan nominal tidak mempengaruhi saldo siswa.</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold text-sm transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>💾 Simpan Perubahan</span>
                            </button>
                            <Link :href="route('vouchers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
