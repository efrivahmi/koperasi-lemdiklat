<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    voucher: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isExpired = computed(() => {
    return new Date(props.voucher.expired_date) < new Date();
});

const isExpiringSoon = computed(() => {
    const expiredDate = new Date(props.voucher.expired_date);
    const today = new Date();
    const diffDays = Math.ceil((expiredDate - today) / (1000 * 60 * 60 * 24));
    return diffDays <= 7 && diffDays > 0;
});

const canEdit = computed(() => {
    return !props.voucher.is_used;
});
</script>

<template>
    <Head :title="`Detail Voucher - ${voucher.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Voucher
                </h2>
                <div class="flex gap-2">
                    <Link v-if="canEdit" :href="route('vouchers.edit', voucher.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('vouchers.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Voucher Code Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <!-- Status Badge -->
                        <div class="flex justify-center mb-6">
                            <span
                                class="px-6 py-2 text-lg font-bold rounded-full"
                                :class="[
                                    voucher.is_used ? 'bg-gray-100 text-gray-800' :
                                    isExpired ? 'bg-red-100 text-red-800' :
                                    'bg-green-100 text-green-800'
                                ]"
                            >
                                <svg v-if="voucher.is_used" class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else-if="isExpired" class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ voucher.is_used ? 'Sudah Digunakan' : isExpired ? 'Expired' : 'Tersedia' }}
                            </span>
                        </div>

                        <!-- Voucher Code Display -->
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-8 mb-6 border-4 border-dashed"
                            :class="[
                                voucher.is_used ? 'border-gray-300' :
                                isExpired ? 'border-red-300' :
                                'border-blue-300'
                            ]"
                        >
                            <p class="text-center text-sm text-gray-600 mb-3 font-medium">KODE VOUCHER</p>
                            <p class="text-center text-5xl font-bold font-mono tracking-wider"
                                :class="[
                                    voucher.is_used ? 'text-gray-600' :
                                    isExpired ? 'text-red-600' :
                                    'text-blue-900'
                                ]"
                            >
                                {{ voucher.code }}
                            </p>
                        </div>

                        <!-- Nominal Display -->
                        <div class="text-center mb-6 py-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-2 border-green-200">
                            <p class="text-sm text-green-600 mb-2 font-medium">Nominal Voucher</p>
                            <p class="text-5xl font-bold text-green-600">{{ formatCurrency(voucher.nominal) }}</p>
                        </div>

                        <!-- Expired Date with Warning -->
                        <div class="bg-yellow-50 border-l-4 rounded-lg p-4 mb-6"
                            :class="[
                                isExpired ? 'border-red-400' :
                                isExpiringSoon ? 'border-yellow-400' :
                                'border-blue-400'
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <svg v-if="isExpired" class="w-6 h-6 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else-if="isExpiringSoon" class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-semibold mb-1"
                                        :class="[
                                            isExpired ? 'text-red-700' :
                                            isExpiringSoon ? 'text-yellow-700' :
                                            'text-blue-700'
                                        ]"
                                    >
                                        <span v-if="isExpired">Voucher Sudah Expired!</span>
                                        <span v-else-if="isExpiringSoon">Voucher Segera Expired!</span>
                                        <span v-else>Tanggal Expired</span>
                                    </p>
                                    <p class="text-lg font-semibold"
                                        :class="[
                                            isExpired ? 'text-red-900' :
                                            isExpiringSoon ? 'text-yellow-900' :
                                            'text-blue-900'
                                        ]"
                                    >
                                        {{ new Date(voucher.expired_date).toLocaleDateString('id-ID', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        }) }}
                                    </p>
                                    <p v-if="isExpiringSoon && !isExpired" class="text-sm text-yellow-700 mt-1">
                                        Voucher ini akan expired dalam {{ Math.ceil((new Date(voucher.expired_date) - new Date()) / (1000 * 60 * 60 * 24)) }} hari.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Information (if used) -->
                <div v-if="voucher.is_used && voucher.student" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Penggunaan</h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-green-600 mb-1">Digunakan oleh</p>
                                    <Link :href="route('students.show', voucher.student.id)" class="font-semibold text-green-900 text-lg hover:text-green-700">
                                        {{ voucher.student.user?.name }}
                                    </Link>
                                    <p class="text-sm text-green-700 mt-1">NIS: {{ voucher.student.nis }}</p>
                                    <p class="text-sm text-green-700">{{ voucher.student.kelas }} - {{ voucher.student.jurusan }}</p>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-sm text-gray-500 block mb-1">Tanggal Penggunaan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ formatDate(voucher.used_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voucher Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Voucher</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-sm text-gray-500 block mb-1">ID Voucher</label>
                                <p class="text-lg font-mono font-semibold text-gray-900">#{{ voucher.id }}</p>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-sm text-gray-500 block mb-1">Dibuat Pada</label>
                                <p class="text-gray-900">{{ formatDate(voucher.created_at) }}</p>
                            </div>

                            <div v-if="!voucher.is_used" class="md:col-span-2">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                Voucher ini belum digunakan dan masih bisa digunakan untuk top-up saldo siswa.
                                                <span v-if="!isExpired">Pastikan voucher digunakan sebelum tanggal expired.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!canEdit" class="md:col-span-2">
                                <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-700">
                                                Voucher yang sudah digunakan tidak dapat diubah.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
