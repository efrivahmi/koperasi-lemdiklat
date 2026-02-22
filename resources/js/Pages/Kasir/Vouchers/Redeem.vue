<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const students = ref([]);
const selectedStudent = ref(null);
const voucherCode = ref('');
const loading = ref(false);
const message = ref({ show: false, text: '', type: 'success' });
const barcodeBuffer = ref('');
const lastKeyTime = ref(0);
let searchTimeout = null;

const searchStudent = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) { students.value = []; return; }
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('kasir.vouchers.search.student'), { params: { search: searchQuery.value } });
            students.value = response.data.students || [];
        } catch (error) { students.value = []; }
    }, 300);
};

const selectStudent = (student) => { selectedStudent.value = student; students.value = []; searchQuery.value = ''; };

const redeemVoucher = async () => {
    if (!selectedStudent.value || !voucherCode.value) { showMessage('Pilih siswa dan masukkan kode voucher', 'error'); return; }
    loading.value = true;
    try {
        const response = await axios.post(route('kasir.vouchers.redeem'), { code: voucherCode.value, student_id: selectedStudent.value.id });
        if (response.data.success) {
            showMessage(`Voucher berhasil! Saldo ${formatCurrency(response.data.balance_before)} → ${formatCurrency(response.data.balance_after)}`, 'success');
            selectedStudent.value = response.data.student; voucherCode.value = '';
        }
    } catch (error) { showMessage(error.response?.data?.message || 'Terjadi kesalahan', 'error'); }
    finally { loading.value = false; }
};

const showMessage = (text, type) => { message.value = { show: true, text, type }; setTimeout(() => { message.value.show = false; }, 3000); };
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);

const handleBarcodeScanner = (event) => {
    const isInputField = document.activeElement?.tagName === 'INPUT';
    if (isInputField && event.timeStamp - lastKeyTime.value > 100) return;
    const currentTime = Date.now();
    barcodeBuffer.value = (currentTime - lastKeyTime.value < 50) ? barcodeBuffer.value + event.key : event.key;
    lastKeyTime.value = currentTime;
    if (event.key === 'Enter' && barcodeBuffer.value) {
        event.preventDefault();
        const code = barcodeBuffer.value.replace('Enter', '').trim();
        if (code?.startsWith('VCR-')) { voucherCode.value = code; if (selectedStudent.value) { showMessage('Voucher terdeteksi! Proses redeem...', 'success'); redeemVoucher(); } else { showMessage('Scan kartu siswa terlebih dahulu', 'error'); } }
        else if (code && /^[0-9]+$/.test(code)) { autoSelectStudentByNIS(code); }
        else if (code) { showMessage('Format kode tidak dikenali', 'error'); }
        barcodeBuffer.value = '';
    }
};

const autoSelectStudentByNIS = async (nis) => {
    try {
        showMessage('Mencari siswa NIS: ' + nis + '...', 'success');
        const response = await axios.get(route('kasir.vouchers.search.student'), { params: { search: nis } });
        const found = response.data.students || [];
        if (found.length > 0) {
            const student = found.find(s => s.nis === nis) || found[0];
            selectedStudent.value = student; searchQuery.value = ''; students.value = [];
            showMessage(`Siswa: ${student.user.name} (${student.nis})`, 'success');
            try { const ctx = new AudioContext(); const o = ctx.createOscillator(); const g = ctx.createGain(); o.connect(g); g.connect(ctx.destination); o.frequency.value = 800; g.gain.setValueAtTime(0.3, ctx.currentTime); g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1); o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.1); } catch (e) {}
        } else { showMessage('Siswa NIS ' + nis + ' tidak ditemukan', 'error'); }
    } catch (error) { showMessage('Gagal mencari siswa', 'error'); }
};

onMounted(() => window.addEventListener('keypress', handleBarcodeScanner));
onUnmounted(() => window.removeEventListener('keypress', handleBarcodeScanner));
</script>

<template>
    <Head title="Redeem Voucher" />
    <AuthenticatedLayout>
        <template #mobileTitle>Voucher</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Redeem Voucher</h2>
        </template>
        <div class="min-h-screen">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-emerald-600/80 via-teal-600/80 to-cyan-600/80 backdrop-blur-md rounded-xl shadow-xl p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">🎫</span>
                        <div>
                            <h3 class="text-2xl font-bold">Redeem Voucher</h3>
                            <p class="text-emerald-100 text-sm">Scan atau masukkan kode voucher untuk top-up saldo siswa</p>
                        </div>
                    </div>
                </div>

                <!-- Alert -->
                <div v-if="message.show" :class="['p-4 rounded-xl border', message.type === 'success' ? 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/10 text-rose-300 border-rose-500/30']">
                    {{ message.text }}
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl p-6 sm:p-8">
                    <div class="space-y-6">
                        <!-- Search Student -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Cari Siswa (NIS / Nama)</label>
                            <div class="relative">
                                <input v-model="searchQuery" @input="searchStudent" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5 placeholder-slate-400" placeholder="Ketik NIS atau nama..." />
                                <div v-if="students.length > 0" class="absolute z-10 w-full mt-1 bg-slate-800 border border-white/10 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                    <div v-for="s in students" :key="s.id" @click="selectStudent(s)" class="p-3 hover:bg-white/5 cursor-pointer border-b border-white/5 last:border-b-0">
                                        <div class="font-semibold text-white">{{ s.user.name }}</div>
                                        <div class="text-sm text-slate-400">{{ s.nis }} - {{ s.kelas }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Student -->
                        <div v-if="selectedStudent" class="p-4 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
                            <h4 class="font-semibold mb-2 text-indigo-300">Siswa Terpilih:</h4>
                            <div class="text-lg font-bold text-white">{{ selectedStudent.user.name }}</div>
                            <div class="text-sm text-slate-400">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</div>
                            <div class="text-lg font-bold text-indigo-300 mt-2">Saldo: {{ formatCurrency(selectedStudent.balance) }}</div>
                        </div>

                        <!-- Voucher Code -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Kode Voucher</label>
                            <input v-model="voucherCode" type="text" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm py-2.5 font-mono text-lg" placeholder="VCR-XXXXXXXX" />
                            <p class="mt-2 text-sm text-slate-500">💡 Scan barcode voucher untuk input otomatis</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="redeemVoucher" :disabled="loading || !selectedStudent || !voucherCode" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-lg font-bold shadow-lg shadow-emerald-500/20 disabled:opacity-50 w-full sm:w-auto">
                                {{ loading ? 'Memproses...' : '⚡ Redeem Voucher' }}
                            </button>
                            <Link :href="route('kasir.vouchers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold border border-white/10 w-full sm:w-auto">Batal</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
