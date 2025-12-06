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
const voucherCodeInput = ref(null);
const studentSearchInput = ref(null);

// Barcode/RFID scanner support
const barcodeBuffer = ref('');
const lastKeyTime = ref(0);
let searchTimeout = null;

const searchStudent = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
        students.value = [];
        return;
    }

    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Debounce search
    searchTimeout = setTimeout(async () => {
        try {
            console.log('Searching student:', searchQuery.value);
            const response = await axios.get(route('vouchers.search.student'), {
                params: { search: searchQuery.value }
            });
            students.value = response.data.students || [];
            console.log('Found students:', students.value.length);
        } catch (error) {
            console.error('Search student failed:', error);
            students.value = [];
        }
    }, 300);
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    students.value = [];
    searchQuery.value = '';
};

const redeemVoucher = async () => {
    if (!selectedStudent.value || !voucherCode.value) {
        showMessage('Pilih siswa dan masukkan kode voucher', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(route('vouchers.redeem'), {
            code: voucherCode.value,
            student_id: selectedStudent.value.id,
        });

        if (response.data.success) {
            const balanceBefore = formatCurrency(response.data.balance_before);
            const balanceAfter = formatCurrency(response.data.balance_after);

            showMessage(
                `Voucher berhasil di-redeem! Saldo ${balanceBefore} → ${balanceAfter}`,
                'success'
            );

            selectedStudent.value = response.data.student;
            voucherCode.value = '';
        }
    } catch (error) {
        console.error('Redeem error:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Terjadi kesalahan saat redeem voucher';
        showMessage(errorMessage, 'error');
    } finally {
        loading.value = false;
    }
};

const showMessage = (text, type) => {
    message.value = { show: true, text, type };
    setTimeout(() => { message.value.show = false; }, 3000);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// Handle barcode/RFID scanner input
const handleBarcodeScanner = (event) => {
    // Ignore if user is manually typing in input fields
    const activeElement = document.activeElement;
    const isInputField = activeElement && activeElement.tagName === 'INPUT';

    // Allow manual typing in input fields
    if (isInputField && event.timeStamp - lastKeyTime.value > 100) {
        return;
    }

    const currentTime = Date.now();

    // If time between keystrokes is less than 50ms, it's likely from a scanner
    if (currentTime - lastKeyTime.value < 50) {
        barcodeBuffer.value += event.key;
    } else {
        barcodeBuffer.value = event.key;
    }

    lastKeyTime.value = currentTime;

    // If Enter is pressed, process the scanned code
    if (event.key === 'Enter' && barcodeBuffer.value) {
        event.preventDefault();
        const scannedCode = barcodeBuffer.value.replace('Enter', '').trim();

        console.log('Scanner detected code:', scannedCode);

        // Detect voucher code (starts with VCR-)
        if (scannedCode && scannedCode.startsWith('VCR-')) {
            voucherCode.value = scannedCode;

            // Auto redeem if student is selected
            if (selectedStudent.value) {
                console.log('Auto-redeeming voucher for student:', selectedStudent.value.user.name);
                showMessage('Voucher terdeteksi! Proses redeem...', 'success');
                redeemVoucher();
            } else {
                showMessage('Scan kartu siswa (RFID/NIS) terlebih dahulu', 'error');
            }
        }
        // Detect RFID/NIS (numeric code for student)
        else if (scannedCode && /^[0-9]+$/.test(scannedCode)) {
            console.log('RFID/NIS detected:', scannedCode);
            // Auto search and select student
            autoSelectStudentByNIS(scannedCode);
        }
        else if (scannedCode) {
            showMessage('Format kode tidak dikenali. Scan voucher (VCR-) atau kartu siswa (NIS)', 'error');
        }

        barcodeBuffer.value = '';
    }
};

// Auto select student by NIS from RFID scanner
const autoSelectStudentByNIS = async (nis) => {
    try {
        showMessage('Mencari siswa dengan NIS: ' + nis + '...', 'success');

        const response = await axios.get(route('vouchers.search.student'), {
            params: { search: nis }
        });

        const foundStudents = response.data.students || [];

        if (foundStudents.length > 0) {
            // Find exact match by NIS
            const student = foundStudents.find(s => s.nis === nis) || foundStudents[0];

            selectedStudent.value = student;
            searchQuery.value = '';
            students.value = [];

            showMessage(`Siswa terdeteksi: ${student.user.name} (${student.nis})`, 'success');
            console.log('Student auto-selected:', student);

            // Play success beep
            playBeep();
        } else {
            showMessage('Siswa dengan NIS ' + nis + ' tidak ditemukan', 'error');
            console.error('Student not found with NIS:', nis);
        }
    } catch (error) {
        console.error('Auto select student failed:', error);
        showMessage('Gagal mencari siswa: ' + error.message, 'error');
    }
};

// Play beep sound for feedback
const playBeep = () => {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (e) {
        // Silently fail if audio not supported
        console.log('Audio beep not supported');
    }
};

// Setup and cleanup event listeners
onMounted(() => {
    window.addEventListener('keypress', handleBarcodeScanner);
});

onUnmounted(() => {
    window.removeEventListener('keypress', handleBarcodeScanner);
});
</script>

<template>
    <Head title="Redeem Voucher" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Redeem Voucher</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Message Alert -->
                <div v-if="message.show" :class="[
                    'mb-4 p-4 rounded-lg border',
                    message.type === 'success'
                        ? 'bg-green-100 dark:bg-green-900/30 border-green-400 dark:border-green-600 text-green-700 dark:text-green-300'
                        : 'bg-red-100 dark:bg-red-900/30 border-red-400 dark:border-red-600 text-red-700 dark:text-red-300'
                ]">
                    {{ message.text }}
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <div class="space-y-6">
                            <!-- Search Student -->
                            <div>
                                <label for="search-student" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Cari Siswa (NIS / Nama)
                                </label>
                                <div class="relative">
                                    <input
                                        id="search-student"
                                        v-model="searchQuery"
                                        @input="searchStudent"
                                        type="text"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2"
                                        placeholder="Ketik NIS atau nama siswa..."
                                    />
                                    <!-- Dropdown Results -->
                                    <div v-if="students.length > 0" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                        <div
                                            v-for="student in students"
                                            :key="student.id"
                                            @click="selectStudent(student)"
                                            class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                                        >
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ student.user.name }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ student.nis }} - {{ student.kelas }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Student Card -->
                            <div v-if="selectedStudent" class="p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                                <h4 class="font-semibold mb-2 text-indigo-900 dark:text-indigo-300">Siswa Terpilih:</h4>
                                <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ selectedStudent.user.name }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</div>
                                <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                                    Saldo: {{ formatCurrency(selectedStudent.balance) }}
                                </div>
                            </div>

                            <!-- Voucher Code Input -->
                            <div>
                                <label for="voucher_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kode Voucher
                                </label>
                                <input
                                    id="voucher_code"
                                    ref="voucherCodeInput"
                                    v-model="voucherCode"
                                    type="text"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2"
                                    placeholder="VCR-XXXXXXXX"
                                />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <strong>Tip:</strong> Scan barcode voucher menggunakan barcode scanner untuk input otomatis
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button
                                    @click="redeemVoucher"
                                    :disabled="loading || !selectedStudent || !voucherCode"
                                    class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                                >
                                    {{ loading ? 'Memproses...' : 'Redeem Voucher' }}
                                </button>
                                <Link
                                    :href="route('vouchers.index')"
                                    class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto"
                                >
                                    Batal
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
