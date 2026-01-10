<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    students: Array,
    teachers: Array,
});

const form = useForm({
    saver_type: '',
    saver_id: '',
    type: 'deposit',
    amount: '',
    description: '',
});

const selectedSaverType = ref('');
const saverList = computed(() => {
    if (selectedSaverType.value === 'student') return props.students;
    if (selectedSaverType.value === 'teacher') return props.teachers;
    return [];
});

const updateFormSaverType = () => {
    if (selectedSaverType.value === 'student') {
        form.saver_type = 'App\\Models\\Student';
    } else if (selectedSaverType.value === 'teacher') {
        form.saver_type = 'App\\Models\\Teacher';
    }
    form.saver_id = '';
};

const submit = () => {
    form.post(route('savings.store'));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Transaksi Tabungan Baru" />
    <AuthenticatedLayout>
        <template #mobileTitle>Tabungan Baru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Transaksi Tabungan Baru</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <div>
                            <h3 class="text-2xl font-bold">Transaksi Tabungan</h3>
                            <p class="text-purple-100 text-sm">Setoran atau penarikan tabungan siswa/guru</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Pilih Tipe Penabung -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe Penabung</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" @click="selectedSaverType = 'student'; updateFormSaverType()" :class="selectedSaverType === 'student' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-6 py-4 rounded-lg font-semibold transition">
                                        Siswa
                                    </button>
                                    <button type="button" @click="selectedSaverType = 'teacher'; updateFormSaverType()" :class="selectedSaverType === 'teacher' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-6 py-4 rounded-lg font-semibold transition">
                                        Guru
                                    </button>
                                </div>
                                <InputError :message="form.errors.saver_type" class="mt-2" />
                            </div>

                            <!-- Pilih Penabung -->
                            <div v-if="selectedSaverType">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih {{ selectedSaverType === 'student' ? 'Siswa' : 'Guru' }}</label>
                                <select v-model="form.saver_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-3" required>
                                    <option value="">-- Pilih {{ selectedSaverType === 'student' ? 'Siswa' : 'Guru' }} --</option>
                                    <option v-for="saver in saverList" :key="saver.id" :value="saver.id">
                                        {{ saver.user?.name || saver.name }} - {{ selectedSaverType === 'student' ? saver.nis : saver.nip }} (Saldo: {{ formatCurrency(saver.balance) }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.saver_id" class="mt-2" />
                            </div>

                            <!-- Jenis Transaksi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Transaksi</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" @click="form.type = 'deposit'" :class="form.type === 'deposit' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-6 py-4 rounded-lg font-semibold transition">
                                        Setoran
                                    </button>
                                    <button type="button" @click="form.type = 'withdrawal'" :class="form.type === 'withdrawal' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-6 py-4 rounded-lg font-semibold transition">
                                        Penarikan
                                    </button>
                                </div>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>

                            <!-- Jumlah -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jumlah (Rp)</label>
                                <input type="number" v-model="form.amount" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-3 text-lg" min="1000" step="1000" required placeholder="Contoh: 50000" />
                                <InputError :message="form.errors.amount" class="mt-2" />
                            </div>

                            <!-- Keterangan -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keterangan (Opsional)</label>
                                <textarea v-model="form.description" rows="3" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg py-2" placeholder="Keterangan tambahan..."></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-bold shadow-lg disabled:opacity-50">
                                    <span v-if="!form.processing">Proses Transaksi</span>
                                    <span v-else>Memproses...</span>
                                </button>
                                <Link :href="route('savings.index')" class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold shadow-sm">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
