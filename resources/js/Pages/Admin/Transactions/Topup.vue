<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const students = ref([]);
const selectedStudent = ref(null);
const amount = ref('');
const description = ref('');
const loading = ref(false);
const message = ref({ show: false, text: '', type: 'success' });

const searchStudent = async () => {
    if (!searchQuery.value) return;
    try {
        const response = await axios.get(route('transactions.search.student'), { params: { search: searchQuery.value } });
        if (response.data.success) students.value = response.data.students;
    } catch (error) { console.error('Search failed:', error); }
};

const selectStudent = (student) => { selectedStudent.value = student; students.value = []; searchQuery.value = ''; };

const processTopup = async () => {
    if (!selectedStudent.value || !amount.value) { showMessage('Pilih siswa dan masukkan nominal top-up', 'error'); return; }
    const topupAmount = parseInt(amount.value);
    if (topupAmount < 1000) { showMessage('Minimal top-up adalah Rp 1.000', 'error'); return; }
    if (topupAmount > 10000000) { showMessage('Maksimal top-up adalah Rp 10.000.000', 'error'); return; }
    loading.value = true;
    try {
        const response = await axios.post(route('transactions.topup'), { student_id: selectedStudent.value.id, amount: topupAmount, description: description.value });
        if (response.data.success) {
            showMessage(`Top-up berhasil! Saldo ${formatCurrency(response.data.balance_before)} → ${formatCurrency(response.data.balance_after)}`, 'success');
            selectedStudent.value = response.data.student; amount.value = ''; description.value = '';
        }
    } catch (error) { showMessage(error.response?.data?.message || 'Terjadi kesalahan', 'error'); }
    finally { loading.value = false; }
};

const showMessage = (text, type) => { message.value = { show: true, text, type }; setTimeout(() => { message.value.show = false; }, 5000); };
const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Top-up Saldo" />
    <AuthenticatedLayout>
        <template #mobileTitle>Top-up</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Top-up Saldo Siswa</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-2xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl p-4">
                    <Link :href="route('transactions.index')" class="inline-flex items-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg border border-white/10 transition-all text-sm">
                        ← Kembali
                    </Link>
                </div>

                <!-- Alert -->
                <div v-if="message.show" :class="['p-4 rounded-xl border', message.type === 'success' ? 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/10 text-rose-300 border-rose-500/30']">
                    {{ message.text }}
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6">
                    <div class="space-y-6">
                        <!-- Search Student -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Cari Siswa (NIS / Nama)</label>
                            <div class="relative">
                                <input v-model="searchQuery" @input="searchStudent" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5 placeholder-slate-400" placeholder="Ketik NIS atau nama siswa..." :disabled="loading" />
                                <div v-if="students.length > 0" class="absolute z-10 w-full mt-1 bg-slate-800 border border-white/10 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                    <div v-for="s in students" :key="s.id" @click="selectStudent(s)" class="p-3 hover:bg-white/5 cursor-pointer border-b border-white/5 last:border-b-0">
                                        <div class="font-semibold text-white">{{ s.user.name }}</div>
                                        <div class="text-sm text-slate-400">NIS: {{ s.nis }} | Kelas: {{ s.kelas }}</div>
                                        <div class="text-sm font-medium text-indigo-300">Saldo: {{ formatCurrency(s.balance) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Student -->
                        <div v-if="selectedStudent" class="p-4 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
                            <h4 class="font-semibold mb-3 text-indigo-300">Siswa Terpilih:</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span class="text-slate-400">Nama:</span><span class="font-semibold text-white">{{ selectedStudent.user.name }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">NIS:</span><span class="font-semibold text-white">{{ selectedStudent.nis }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Kelas:</span><span class="font-semibold text-white">{{ selectedStudent.kelas }}</span></div>
                                <div class="flex justify-between pt-2 border-t border-indigo-500/20"><span class="text-slate-400">Saldo:</span><span class="text-lg font-bold text-indigo-300">{{ formatCurrency(selectedStudent.balance) }}</span></div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nominal Top-up (Rp)</label>
                            <input v-model="amount" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5 text-lg" placeholder="Contoh: 50000" :disabled="loading" @input="(e) => amount = e.target.value.replace(/\D/g, '')" />
                            <p class="mt-1 text-sm text-slate-500">Minimal Rp 1.000 - Maksimal Rp 10.000.000</p>
                            <p v-if="amount && parseInt(amount) > 0" class="mt-1 text-sm font-semibold text-emerald-300">{{ formatCurrency(parseInt(amount)) }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Keterangan (Opsional)</label>
                            <input v-model="description" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5" placeholder="Contoh: Top-up untuk kantin" :disabled="loading" />
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="processTopup" :disabled="loading || !selectedStudent || !amount" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold shadow-lg shadow-indigo-500/20 disabled:opacity-50 w-full sm:w-auto">
                                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ loading ? 'Memproses...' : '💰 Proses Top-up' }}
                            </button>
                            <Link :href="route('transactions.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
                    <h4 class="font-semibold text-amber-300 mb-2">ℹ️ Informasi</h4>
                    <ul class="text-sm text-amber-200/80 space-y-1 list-disc list-inside">
                        <li>Pastikan data siswa sudah benar sebelum top-up</li>
                        <li>Top-up langsung menambah saldo siswa</li>
                        <li>Transaksi tercatat dalam riwayat</li>
                        <li>Minimal Rp 1.000, maksimal Rp 10.000.000</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
