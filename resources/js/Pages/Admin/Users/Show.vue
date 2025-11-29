<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getRoleBadgeClass = (role) => {
    const classes = {
        'admin': 'bg-red-100 text-red-800',
        'kasir': 'bg-blue-100 text-blue-800',
        'siswa': 'bg-green-100 text-green-800',
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
};

const getRoleLabel = (role) => {
    const labels = {
        'admin': 'Administrator',
        'kasir': 'Staf Koperasi',
        'siswa': 'Siswa',
    };
    return labels[role] || role;
};
</script>

<template>
    <Head :title="`Detail User - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail User
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('users.edit', user.id)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit
                    </Link>
                    <Link :href="route('users.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- User Profile Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="h-24 w-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-2xl font-bold text-gray-900">{{ user.name }}</h3>
                                    <span
                                        class="px-3 py-1 text-sm font-semibold rounded-full"
                                        :class="getRoleBadgeClass(user.role)"
                                    >
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-4">{{ user.email }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-500">User ID</label>
                                        <p class="text-lg font-mono text-gray-900">#{{ user.id }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Status Akun</label>
                                        <p class="text-lg">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Dibuat Pada</label>
                                        <p class="text-gray-900">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                    <div v-if="user.last_login_at">
                                        <label class="text-sm text-gray-500">Terakhir Login</label>
                                        <p class="text-gray-900">{{ formatDate(user.last_login_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Info (if role is siswa) -->
                <div v-if="user.role === 'siswa' && user.student" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Informasi Siswa</h3>
                            <Link :href="route('students.show', user.student.id)" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                Lihat Detail Lengkap →
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm text-gray-500">NISN</label>
                                <p class="text-lg font-semibold text-gray-900 font-mono">{{ user.student.nis }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">NISN</label>
                                <p class="text-lg font-semibold text-gray-900 font-mono">{{ user.student.nisn || '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Kelas</label>
                                <p class="text-lg text-gray-900">{{ user.student.kelas }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Jurusan</label>
                                <p class="text-lg text-gray-900">{{ user.student.jurusan }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-500">Saldo</label>
                                <p class="text-3xl font-bold"
                                    :class="[
                                        user.student.balance > 50000 ? 'text-green-600' :
                                        user.student.balance >= 10000 ? 'text-yellow-600' :
                                        'text-red-600'
                                    ]"
                                >
                                    {{ new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0
                                    }).format(user.student.balance) }}
                                </p>
                            </div>
                            <div class="md:col-span-2" v-if="user.student.alamat">
                                <label class="text-sm text-gray-500">Alamat</label>
                                <p class="text-gray-900">{{ user.student.alamat }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Status RFID</label>
                                <div class="mt-1">
                                    <span v-if="user.student.rfid_uid" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Terdaftar ({{ user.student.rfid_uid }})
                                    </span>
                                    <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Belum Terdaftar
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="user.student.photo_path" class="mt-6">
                            <label class="text-sm text-gray-500 block mb-2">Foto Siswa</label>
                            <img
                                :src="`/storage/${user.student.photo_path}`"
                                :alt="user.name"
                                class="h-48 w-48 object-cover rounded-lg shadow-md"
                            />
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Akun</h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-blue-900">Hak Akses</p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        <span v-if="user.role === 'admin'">Memiliki akses penuh ke seluruh sistem termasuk manajemen user, produk, dan laporan.</span>
                                        <span v-else-if="user.role === 'kasir'">Staf Koperasi dapat melakukan transaksi penjualan dan mengakses fitur operasional sesuai izin yang diberikan.</span>
                                        <span v-else-if="user.role === 'siswa'">Dapat melihat profil, saldo, dan riwayat transaksi pribadi.</span>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500">Email Verified</label>
                                    <p class="mt-1">
                                        <span v-if="user.email_verified_at" class="text-green-600 font-medium">
                                            <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Terverifikasi
                                        </span>
                                        <span v-else class="text-gray-500 font-medium">
                                            Belum terverifikasi
                                        </span>
                                    </p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <label class="text-sm text-gray-500">Terakhir Update</label>
                                    <p class="text-gray-900 font-medium mt-1">{{ formatDate(user.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
