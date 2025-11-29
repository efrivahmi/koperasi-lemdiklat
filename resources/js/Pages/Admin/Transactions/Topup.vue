<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
        const response = await axios.get(route('transactions.search.student'), {
            params: { search: searchQuery.value }
        });

        if (response.data.success) {
            students.value = response.data.students;
        }
    } catch (error) {
        console.error('Search failed:', error);
    }
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    students.value = [];
    searchQuery.value = '';
};

const processTopup = async () => {
    if (!selectedStudent.value || !amount.value) {
        showMessage('Pilih siswa dan masukkan nominal top-up', 'error');
        return;
    }

    const topupAmount = parseInt(amount.value);
    if (topupAmount < 1000) {
        showMessage('Minimal top-up adalah Rp 1.000', 'error');
        return;
    }

    if (topupAmount > 10000000) {
        showMessage('Maksimal top-up adalah Rp 10.000.000', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(route('transactions.topup'), {
            student_id: selectedStudent.value.id,
            amount: topupAmount,
            description: description.value,
        });

        if (response.data.success) {
            const balanceBefore = formatCurrency(response.data.balance_before);
            const balanceAfter = formatCurrency(response.data.balance_after);

            showMessage(
                `Top-up berhasil! Saldo ${balanceBefore} → ${balanceAfter}`,
                'success'
            );

            selectedStudent.value = response.data.student;
            amount.value = '';
            description.value = '';
        }
    } catch (error) {
        showMessage(error.response?.data?.message || 'Terjadi kesalahan', 'error');
    } finally {
        loading.value = false;
    }
};

const showMessage = (text, type) => {
    message.value = { show: true, text, type };
    setTimeout(() => { message.value.show = false; }, 5000);
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Top-up Saldo" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Top-up Saldo Siswa</h2>
                <Link
                    :href="route('transactions.index')"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Alert Message -->
                <div
                    v-if="message.show"
                    :class="[
                        'mb-4 p-4 rounded border',
                        message.type === 'success'
                            ? 'bg-green-100 text-green-700 border-green-400'
                            : 'bg-red-100 text-red-700 border-red-400'
                    ]"
                >
                    {{ message.text }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="space-y-6">
                        <!-- Search Student -->
                        <div>
                            <InputLabel value="Cari Siswa (NIS / Nama)" />
                            <div class="relative">
                                <TextInput
                                    v-model="searchQuery"
                                    @input="searchStudent"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Ketik NIS atau nama siswa..."
                                    :disabled="loading"
                                />

                                <!-- Search Results -->
                                <div
                                    v-if="students.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-for="student in students"
                                        :key="student.id"
                                        @click="selectStudent(student)"
                                        class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0"
                                    >
                                        <div class="font-semibold text-gray-900">{{ student.user.name }}</div>
                                        <div class="text-sm text-gray-600">
                                            NIS: {{ student.nis }} | Kelas: {{ student.kelas }}
                                        </div>
                                        <div class="text-sm font-medium text-blue-600">
                                            Saldo: {{ formatCurrency(student.balance) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Student -->
                        <div v-if="selectedStudent" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="font-semibold mb-3 text-blue-900">Siswa Terpilih:</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-semibold">{{ selectedStudent.user.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">NIS:</span>
                                    <span class="font-semibold">{{ selectedStudent.nis }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kelas:</span>
                                    <span class="font-semibold">{{ selectedStudent.kelas }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-blue-200">
                                    <span class="text-gray-600">Saldo Saat Ini:</span>
                                    <span class="text-lg font-bold text-blue-600">
                                        {{ formatCurrency(selectedStudent.balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div>
                            <InputLabel for="amount" value="Nominal Top-up (Rp)" />
                            <TextInput
                                id="amount"
                                v-model="amount"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Contoh: 50000"
                                :disabled="loading"
                                @input="(e) => amount = e.target.value.replace(/\D/g, '')"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Minimal Rp 1.000 - Maksimal Rp 10.000.000
                            </p>
                            <p v-if="amount && parseInt(amount) > 0" class="mt-1 text-sm font-semibold text-green-600">
                                {{ formatCurrency(parseInt(amount)) }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Keterangan (Opsional)" />
                            <TextInput
                                id="description"
                                v-model="description"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Contoh: Top-up untuk kantin"
                                :disabled="loading"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-4 pt-4">
                            <PrimaryButton
                                @click="processTopup"
                                :disabled="loading || !selectedStudent || !amount"
                                class="flex items-center"
                            >
                                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ loading ? 'Memproses...' : 'Proses Top-up' }}
                            </PrimaryButton>

                            <Link
                                :href="route('transactions.index')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 flex items-center transition"
                            >
                                Batal
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-900 mb-2">ℹ️ Informasi</h4>
                    <ul class="text-sm text-yellow-800 space-y-1 list-disc list-inside">
                        <li>Pastikan data siswa sudah benar sebelum melakukan top-up</li>
                        <li>Top-up akan langsung menambah saldo siswa</li>
                        <li>Transaksi top-up akan tercatat dalam riwayat transaksi</li>
                        <li>Minimal top-up Rp 1.000, maksimal Rp 10.000.000</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
