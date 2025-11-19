<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    voucher: Object,
});

const form = useForm({
    nominal: props.voucher.nominal,
    expired_at: props.voucher.expired_at ? props.voucher.expired_at.split(' ')[0] : '',
    status: props.voucher.status,
});

const submit = () => {
    form.put(route('vouchers.update', props.voucher.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getStatusBadge = (status) => {
    const badges = {
        'available': 'bg-green-100 text-green-800',
        'used': 'bg-gray-100 text-gray-800',
        'expired': 'bg-red-100 text-red-800'
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'available': 'Tersedia',
        'used': 'Sudah Digunakan',
        'expired': 'Kadaluarsa'
    };
    return texts[status] || status;
};
</script>

<template>
    <Head title="Edit Voucher" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Voucher</h2>
                <Link :href="route('vouchers.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Voucher Info Card -->
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Kode Voucher</p>
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 font-mono">{{ voucher.code }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                    <span :class="getStatusBadge(voucher.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-1">
                                        {{ getStatusText(voucher.status) }}
                                    </span>
                                </div>
                                <div v-if="voucher.student">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Digunakan Oleh</p>
                                    <p class="font-semibold">{{ voucher.student.user.name }}</p>
                                    <p class="text-sm text-gray-500">NIS: {{ voucher.student.nis }}</p>
                                </div>
                                <div v-if="voucher.used_at">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Tanggal Penggunaan</p>
                                    <p class="font-semibold">{{ new Date(voucher.used_at).toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nominal -->
                                <div>
                                    <InputLabel for="nominal" value="Nominal Voucher" />
                                    <TextInput
                                        id="nominal"
                                        type="number"
                                        v-model="form.nominal"
                                        class="mt-1 block w-full"
                                        min="1000"
                                        step="1000"
                                        required
                                        :disabled="voucher.status === 'used'"
                                    />
                                    <InputError :message="form.errors.nominal" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Preview: {{ formatCurrency(form.nominal || 0) }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <InputLabel for="status" value="Status" />
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="available">Tersedia</option>
                                        <option value="used">Sudah Digunakan</option>
                                        <option value="expired">Kadaluarsa</option>
                                    </select>
                                    <InputError :message="form.errors.status" class="mt-2" />
                                    <p v-if="form.status === 'used'" class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">
                                        ⚠ Mengubah status ke 'Sudah Digunakan' tidak akan menambah saldo siswa.
                                    </p>
                                </div>

                                <!-- Expired At -->
                                <div class="md:col-span-2">
                                    <InputLabel for="expired_at" value="Tanggal Kadaluarsa" />
                                    <TextInput
                                        id="expired_at"
                                        type="date"
                                        v-model="form.expired_at"
                                        class="mt-1 block w-full"
                                        :disabled="voucher.status === 'used'"
                                    />
                                    <InputError :message="form.errors.expired_at" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Kosongkan jika voucher tidak memiliki tanggal kadaluarsa
                                    </p>
                                </div>
                            </div>

                            <!-- Warning for Used Vouchers -->
                            <div v-if="voucher.status === 'used'" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Perhatian: Voucher Sudah Digunakan
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                            <p>Voucher ini sudah digunakan. Perubahan nominal tidak akan mempengaruhi saldo siswa yang sudah menerima top-up.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Simpan Perubahan</span>
                                </PrimaryButton>
                                <Link
                                    :href="route('vouchers.index')"
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                >
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
