<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    teacher: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Detail Guru" />
    <AuthenticatedLayout>
        <template #mobileTitle>Detail Guru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Detail Guru</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg p-8">
                    <div class="flex items-center gap-6 mb-8">
                        <img v-if="teacher.foto" :src="`/storage/${teacher.foto}`" :alt="teacher.user.name" class="h-32 w-32 object-cover rounded-full border-4 border-purple-200 dark:border-purple-500" />
                        <div v-else class="h-32 w-32 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-gray-400 text-2xl">No Photo</span>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ teacher.user.name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ teacher.user.email }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">NIP: {{ teacher.nip }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">NUPTK</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ teacher.nuptk || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Jabatan</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ teacher.jabatan || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Mata Pelajaran</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ teacher.mata_pelajaran || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenjang</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ teacher.jenjang || '-' }}</p>
                        </div>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 p-4 rounded-lg border border-green-200 dark:border-green-500/30">
                            <label class="text-sm font-medium text-green-700 dark:text-green-400">Saldo</label>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-300">{{ formatCurrency(teacher.balance) }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">RFID UID</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ teacher.rfid_uid || 'Belum Terdaftar' }}</p>
                        </div>
                    </div>

                    <div v-if="teacher.alamat_lengkap" class="mb-8">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Alamat Lengkap</label>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">{{ teacher.alamat_lengkap }}</p>
                    </div>

                    <div class="flex gap-4">
                        <Link :href="route('kasir.teachers.edit', teacher.id)" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold shadow-sm">Edit Guru</Link>
                        <Link :href="route('kasir.teachers.index')" class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold shadow-sm">Kembali</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
