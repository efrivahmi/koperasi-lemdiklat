<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({ voucher: Object });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const isExpired = computed(() => new Date(props.voucher.expired_date) < new Date());
const isExpiringSoon = computed(() => { const d = Math.ceil((new Date(props.voucher.expired_date) - new Date()) / 86400000); return d <= 7 && d > 0; });
const canEdit = computed(() => !props.voucher.is_used && can('vouchers.edit'));
</script>

<template>
    <Head :title="`Detail Voucher - ${voucher.code}`" />
    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Detail Voucher</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <div class="flex flex-wrap gap-2">
                        <Link v-if="canEdit" :href="route('vouchers.edit', voucher.id)" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all text-sm">
                            ✏️ <span class="ml-1">Edit</span>
                        </Link>
                        <Link :href="route('vouchers.index')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                            ← <span class="ml-1">Kembali</span>
                        </Link>
                    </div>
                </div>

                <!-- Status + Code Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-8">
                    <!-- Status Badge -->
                    <div class="flex justify-center mb-6">
                        <span class="px-6 py-2 text-lg font-bold rounded-full border" :class="[voucher.is_used ? 'bg-slate-500/20 text-slate-300 border-slate-500/30' : isExpired ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30']">
                            {{ voucher.is_used ? '✅ Sudah Digunakan' : isExpired ? '❌ Expired' : '✨ Tersedia' }}
                        </span>
                    </div>

                    <!-- Voucher Code -->
                    <div class="bg-slate-900/40 rounded-xl p-8 mb-6 border-4 border-dashed" :class="[voucher.is_used ? 'border-slate-600' : isExpired ? 'border-rose-500/30' : 'border-indigo-500/30']">
                        <p class="text-center text-sm text-slate-400 mb-3 font-medium uppercase tracking-wider">Kode Voucher</p>
                        <p class="text-center text-5xl font-bold font-mono tracking-wider" :class="[voucher.is_used ? 'text-slate-400' : isExpired ? 'text-rose-300' : 'text-indigo-300']">{{ voucher.code }}</p>
                    </div>

                    <!-- Nominal -->
                    <div class="text-center mb-6 py-6 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                        <p class="text-sm text-emerald-300 mb-2 font-medium">Nominal Voucher</p>
                        <p class="text-5xl font-bold text-emerald-300">{{ formatCurrency(voucher.nominal) }}</p>
                    </div>

                    <!-- Expired Info -->
                    <div class="bg-slate-900/40 rounded-xl p-4 border border-white/5">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl" v-if="isExpired">❌</span>
                            <span class="text-2xl" v-else-if="isExpiringSoon">⚠️</span>
                            <span class="text-2xl" v-else>📅</span>
                            <div>
                                <p class="font-semibold" :class="[isExpired ? 'text-rose-300' : isExpiringSoon ? 'text-amber-300' : 'text-indigo-300']">
                                    {{ isExpired ? 'Voucher Sudah Expired!' : isExpiringSoon ? 'Voucher Segera Expired!' : 'Tanggal Expired' }}
                                </p>
                                <p class="text-lg font-semibold text-white mt-1">{{ new Date(voucher.expired_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                                <p v-if="isExpiringSoon && !isExpired" class="text-sm text-amber-300 mt-1">Expired dalam {{ Math.ceil((new Date(voucher.expired_date) - new Date()) / 86400000) }} hari.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Info -->
                <div v-if="voucher.is_used && voucher.student" class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Informasi Penggunaan</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold text-white">{{ voucher.student.user?.name?.charAt(0) }}</span>
                            </div>
                            <div>
                                <p class="text-sm text-emerald-300 mb-1">Digunakan oleh</p>
                                <Link :href="route('students.show', voucher.student.id)" class="font-semibold text-white text-lg hover:text-indigo-300 transition-colors">{{ voucher.student.user?.name }}</Link>
                                <p class="text-sm text-slate-400 mt-1">NIS: {{ voucher.student.nis }} | {{ voucher.student.kelas }} - {{ voucher.student.jurusan }}</p>
                            </div>
                        </div>
                        <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Tanggal Penggunaan</label>
                            <p class="text-lg font-semibold text-white mt-1">{{ formatDate(voucher.used_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Voucher Info -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Informasi Voucher</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">ID Voucher</label>
                            <p class="text-lg font-mono font-semibold text-white mt-1">#{{ voucher.id }}</p>
                        </div>
                        <div class="bg-slate-900/40 p-4 rounded-lg border border-white/5">
                            <label class="text-xs text-indigo-300 uppercase tracking-wider">Dibuat Pada</label>
                            <p class="text-white mt-1">{{ formatDate(voucher.created_at) }}</p>
                        </div>
                        <div v-if="!voucher.is_used" class="md:col-span-2 p-4 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
                            <p class="text-sm text-indigo-300">ℹ️ Voucher ini belum digunakan dan masih bisa digunakan untuk top-up saldo siswa.</p>
                        </div>
                        <div v-if="!canEdit" class="md:col-span-2 p-4 bg-slate-600/20 rounded-xl border border-slate-500/20">
                            <p class="text-sm text-slate-400">🔒 Voucher yang sudah digunakan tidak dapat diubah.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
