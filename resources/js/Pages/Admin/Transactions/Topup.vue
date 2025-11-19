<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
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
    const response = await axios.get(route('transactions.search.student'), { params: { search: searchQuery.value } });
    students.value = response.data.students;
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    students.value = [];
    searchQuery.value = '';
};

const processTopup = async () => {
    if (!selectedStudent.value || !amount.value) {
        showMessage('Pilih siswa dan masukkan nominal', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(route('transactions.topup'), {
            student_id: selectedStudent.value.id,
            amount: amount.value,
            description: description.value,
        });

        if (response.data.success) {
            showMessage('Top-up berhasil!', 'success');
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
    setTimeout(() => { message.value.show = false; }, 3000);
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Top-up Saldo" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Top-up Saldo Siswa</h2></template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div v-if="message.show" :class="['mb-4 p-4 rounded', message.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                    {{ message.text }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="space-y-6">
                        <div>
                            <InputLabel value="Cari Siswa (NIS / Nama)" />
                            <div class="relative">
                                <TextInput v-model="searchQuery" @input="searchStudent" type="text" class="mt-1 block w-full" placeholder="Ketik NIS atau nama..." />
                                <div v-if="students.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded shadow-lg">
                                    <div v-for="student in students" :key="student.id" @click="selectStudent(student)" class="p-3 hover:bg-gray-100 cursor-pointer">
                                        <div class="font-semibold">{{ student.user.name }}</div>
                                        <div class="text-sm text-gray-600">{{ student.nis }} - {{ student.kelas }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedStudent" class="p-4 bg-blue-50 rounded">
                            <h4 class="font-semibold mb-2">Siswa:</h4>
                            <div class="text-lg font-bold">{{ selectedStudent.user.name }}</div>
                            <div class="text-sm">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</div>
                            <div class="text-lg font-bold text-blue-600 mt-2">Saldo: {{ formatCurrency(selectedStudent.balance) }}</div>
                        </div>

                        <div>
                            <InputLabel for="amount" value="Nominal Top-up (Rp)" />
                            <TextInput id="amount" v-model="amount" type="number" class="mt-1 block w-full" min="1000" step="1000" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Keterangan (Opsional)" />
                            <TextInput id="description" v-model="description" type="text" class="mt-1 block w-full" placeholder="Catatan..." />
                        </div>

                        <div class="flex gap-4">
                            <PrimaryButton @click="processTopup" :disabled="loading || !selectedStudent || !amount">
                                {{ loading ? 'Memproses...' : 'Top-up Sekarang' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
