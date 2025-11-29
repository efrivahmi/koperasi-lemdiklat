<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const searchQuery = ref('');
const students = ref([]);
const selectedStudent = ref(null);
const voucherCode = ref('');
const loading = ref(false);
const message = ref({ show: false, text: '', type: 'success' });
const voucherCodeInput = ref(null);

// Barcode scanner support
const barcodeBuffer = ref('');
const lastKeyTime = ref(0);

const searchStudent = async () => {
    if (!searchQuery.value) return;

    try {
        const response = await axios.get(route('vouchers.search.student'), {
            params: { search: searchQuery.value }
        });
        students.value = response.data.students || [];
    } catch (error) {
        console.error('Search student failed:', error);
        students.value = [];
    }
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

// Handle barcode scanner input
const handleBarcodeScanner = (event) => {
    const currentTime = Date.now();

    // If time between keystrokes is less than 50ms, it's likely from a scanner
    if (currentTime - lastKeyTime.value < 50) {
        barcodeBuffer.value += event.key;
    } else {
        barcodeBuffer.value = event.key;
    }

    lastKeyTime.value = currentTime;

    // If Enter is pressed, process the barcode
    if (event.key === 'Enter' && barcodeBuffer.value) {
        event.preventDefault();
        const scannedCode = barcodeBuffer.value.replace('Enter', '').trim();

        if (scannedCode && scannedCode.startsWith('VCR-')) {
            voucherCode.value = scannedCode;

            // Auto redeem if student is selected
            if (selectedStudent.value) {
                redeemVoucher();
            } else {
                showMessage('Pilih siswa terlebih dahulu', 'error');
                // Focus on student search
                document.querySelector('input[placeholder*="siswa"]')?.focus();
            }
        }

        barcodeBuffer.value = '';
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Redeem Voucher</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div v-if="message.show" :class="['mb-4 p-4 rounded', message.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                    {{ message.text }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-6">
                            <div>
                                <InputLabel value="Cari Siswa (NIS / Nama)" />
                                <div class="relative">
                                    <TextInput
                                        v-model="searchQuery"
                                        @input="searchStudent"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ketik NIS atau nama siswa..."
                                    />
                                    <div v-if="students.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded shadow-lg">
                                        <div
                                            v-for="student in students"
                                            :key="student.id"
                                            @click="selectStudent(student)"
                                            class="p-3 hover:bg-gray-100 cursor-pointer"
                                        >
                                            <div class="font-semibold">{{ student.user.name }}</div>
                                            <div class="text-sm text-gray-600">{{ student.nis }} - {{ student.kelas }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedStudent" class="p-4 bg-blue-50 rounded">
                                <h4 class="font-semibold mb-2">Siswa Terpilih:</h4>
                                <div class="text-lg font-bold">{{ selectedStudent.user.name }}</div>
                                <div class="text-sm">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</div>
                                <div class="text-lg font-bold text-blue-600 mt-2">Saldo: {{ formatCurrency(selectedStudent.balance) }}</div>
                            </div>

                            <div>
                                <InputLabel for="voucher_code" value="Kode Voucher" />
                                <TextInput
                                    id="voucher_code"
                                    ref="voucherCodeInput"
                                    v-model="voucherCode"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="VCR-XXXXXXXX"
                                />
                                <p class="mt-2 text-sm text-gray-600">
                                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <strong>Tip:</strong> Scan barcode voucher menggunakan barcode scanner untuk input otomatis
                                </p>
                            </div>

                            <div class="flex gap-4">
                                <PrimaryButton @click="redeemVoucher" :disabled="loading || !selectedStudent || !voucherCode">
                                    {{ loading ? 'Memproses...' : 'Redeem Voucher' }}
                                </PrimaryButton>
                                <Link :href="route('vouchers.index')" class="text-gray-600 hover:text-gray-900 flex items-center">Batal</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
