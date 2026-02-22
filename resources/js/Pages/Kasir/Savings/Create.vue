<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';

const props = defineProps({ students: Array, teachers: Array });

const form = useForm({ saver_type: '', saver_id: '', type: 'deposit', amount: '', description: '', transaction_date: new Date().toISOString().split('T')[0] });

const selectedSaverType = ref('');
const saverList = computed(() => {
    if (selectedSaverType.value === 'student') return props.students;
    if (selectedSaverType.value === 'teacher') return props.teachers;
    return [];
});

const updateFormSaverType = () => {
    form.saver_type = selectedSaverType.value;
    form.saver_id = '';
};

const submit = () => form.post(route('kasir.savings.store'));
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head title="Transaksi Tabungan Baru" />
    <AuthenticatedLayout>
        <template #mobileTitle>Tabungan Baru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Transaksi Tabungan Baru</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-purple-600/80 via-indigo-600/80 to-blue-600/80 backdrop-blur-md rounded-xl shadow-xl p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <div>
                            <h3 class="text-2xl font-bold">Transaksi Tabungan</h3>
                            <p class="text-purple-100 text-sm">Setoran atau penarikan tabungan siswa/guru</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Tipe Penabung -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tipe Penabung</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="selectedSaverType = 'student'; updateFormSaverType()" :class="selectedSaverType === 'student' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border border-white/10'" class="px-6 py-4 rounded-lg font-semibold transition-all">Siswa</button>
                                <button type="button" @click="selectedSaverType = 'teacher'; updateFormSaverType()" :class="selectedSaverType === 'teacher' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border border-white/10'" class="px-6 py-4 rounded-lg font-semibold transition-all">Guru</button>
                            </div>
                            <InputError :message="form.errors.saver_type" class="mt-2" />
                        </div>

                        <!-- Pilih Penabung -->
                        <div v-if="selectedSaverType">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Pilih {{ selectedSaverType === 'student' ? 'Siswa' : 'Guru' }}</label>
                            <select v-model="form.saver_id" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-3" required>
                                <option value="">-- Pilih {{ selectedSaverType === 'student' ? 'Siswa' : 'Guru' }} --</option>
                                <option v-for="saver in saverList" :key="saver.id" :value="saver.id">
                                    {{ saver.user?.name || saver.name }} - {{ selectedSaverType === 'student' ? saver.nis : saver.nip }} (Saldo: {{ formatCurrency(saver.balance) }})
                                </option>
                            </select>
                            <InputError :message="form.errors.saver_id" class="mt-2" />
                        </div>

                        <!-- Jenis Transaksi -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Jenis Transaksi</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button type="button" @click="form.type = 'deposit'" :class="form.type === 'deposit' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border border-white/10'" class="px-6 py-4 rounded-lg font-semibold transition-all">Setoran</button>
                                <button type="button" @click="form.type = 'withdrawal'" :class="form.type === 'withdrawal' ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border border-white/10'" class="px-6 py-4 rounded-lg font-semibold transition-all">Penarikan</button>
                            </div>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <!-- Jumlah -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Jumlah (Rp)</label>
                            <input type="number" v-model="form.amount" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-3 text-lg" min="1000" step="1000" required placeholder="Contoh: 50000" />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Keterangan (Opsional)</label>
                            <textarea v-model="form.description" rows="3" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-2.5" placeholder="Keterangan tambahan..."></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- Tanggal Transaksi -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tanggal Transaksi</label>
                            <input type="date" v-model="form.transaction_date" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-2.5" required />
                            <InputError :message="form.errors.transaction_date" class="mt-2" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <span v-if="!form.processing">💾 Proses Transaksi</span>
                                <span v-else>Memproses...</span>
                            </button>
                            <Link :href="route('kasir.savings.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
