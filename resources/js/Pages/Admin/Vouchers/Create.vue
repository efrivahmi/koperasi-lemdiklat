<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({ nominal: '', quantity: 1, expired_at: '' });
const submit = () => form.post(route('vouchers.store'));
</script>

<template>
    <Head title="Generate Voucher" />
    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Generate Voucher</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-purple-600/80 via-indigo-600/80 to-blue-600/80 backdrop-blur-md rounded-xl shadow-xl p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        <div>
                            <h3 class="text-2xl font-bold">Generate Voucher Baru</h3>
                            <p class="text-purple-100 text-sm">Buat voucher topup untuk siswa koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="nominal" class="block text-sm font-medium text-slate-300 mb-1">Nominal Voucher (Rp)</label>
                            <input id="nominal" type="number" v-model="form.nominal" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" min="1000" step="1000" required />
                            <InputError :message="form.errors.nominal" class="mt-2" />
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-slate-300 mb-1">Jumlah Voucher</label>
                            <input id="quantity" type="number" v-model="form.quantity" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" min="1" max="100" required />
                            <p class="text-sm text-slate-500 mt-1">Maks. 100 voucher per generate</p>
                            <InputError :message="form.errors.quantity" class="mt-2" />
                        </div>
                        <div>
                            <label for="expired_at" class="block text-sm font-medium text-slate-300 mb-1">Tanggal Expired (Opsional)</label>
                            <input id="expired_at" type="date" v-model="form.expired_at" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" />
                            <InputError :message="form.errors.expired_at" class="mt-2" />
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold text-sm transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <span v-if="!form.processing">🎫 Generate Voucher</span>
                                <span v-else>Generating...</span>
                            </button>
                            <Link :href="route('vouchers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
