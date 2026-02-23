<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    user: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
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
        'admin': 'bg-rose-500/20 border border-rose-500/30 text-rose-300',
        'kasir': 'bg-blue-500/20 border border-blue-500/30 text-blue-300',
        'siswa': 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-300',
    };
    return classes[role] || 'bg-slate-500/20 border border-slate-500/30 text-slate-300';
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
    <Head title="Detail User" />

    <AuthenticatedLayout>
        <template #mobileTitle>Pengguna</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Detail User
            </h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Action Buttons Toolbar -->
                <div class="flex justify-end gap-3 px-3 sm:px-0 mb-2">
                    <Link :href="route('users.index')" class="bg-slate-800/50 hover:bg-slate-700/50 backdrop-blur-md border border-white/10 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition-all">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Kembali
                        </span>
                    </Link>
                    <Link v-if="can('users.edit')" :href="route('users.edit', user.id)" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-semibold py-2 px-6 rounded-lg shadow-lg shadow-amber-500/20 transition-all">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit User
                        </span>
                    </Link>
                </div>

                <!-- User Profile Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-xl border border-white/10 mx-3 sm:mx-0">
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 ring-4 ring-white/5">
                                    <span class="text-4xl font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <h3 class="text-2xl font-bold text-white">{{ user.name }}</h3>
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="getRoleBadgeClass(user.role)"
                                    >
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </div>
                                <p class="text-slate-400 mb-4">{{ user.email }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-slate-900/50 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-slate-400 block mb-1">User ID</label>
                                        <p class="text-sm font-mono text-white">#{{ user.id }}</p>
                                    </div>
                                    <div class="bg-slate-900/50 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-slate-400 block mb-1">Status Akun</label>
                                        <p class="text-sm">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/20 border border-emerald-500/30 text-emerald-300">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif
                                            </span>
                                        </p>
                                    </div>
                                    <div class="bg-slate-900/50 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-slate-400 block mb-1">Dibuat Pada</label>
                                        <p class="text-sm text-white">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                    <div v-if="user.last_login_at" class="bg-slate-900/50 p-3 rounded-lg border border-white/5">
                                        <label class="text-xs text-slate-400 block mb-1">Terakhir Login</label>
                                        <p class="text-sm text-white">{{ formatDate(user.last_login_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Info (if role is siswa) -->
                <div v-if="user.role === 'siswa' && user.student" class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-xl border border-white/10 mx-3 sm:mx-0">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-white">Informasi Siswa</h3>
                            <Link :href="route('students.show', user.student.id)" class="text-indigo-400 hover:text-indigo-300 font-medium text-sm transition-colors">
                                Lihat Detail Lengkap &rarr;
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm text-slate-400">NIS</label>
                                <p class="text-lg font-semibold text-white font-mono">{{ user.student.nis }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">NISN</label>
                                <p class="text-lg font-semibold text-white font-mono">{{ user.student.nisn || '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">Kelas</label>
                                <p class="text-lg text-white">{{ user.student.kelas }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">Jurusan</label>
                                <p class="text-lg text-white">{{ user.student.jurusan }}</p>
                            </div>
                            <div class="md:col-span-2 bg-slate-900/40 p-4 rounded-xl border border-white/5">
                                <label class="text-sm text-slate-400">Saldo Dompet</label>
                                <p class="text-3xl font-bold mt-1"
                                    :class="[
                                        user.student.balance > 50000 ? 'text-emerald-400' :
                                        user.student.balance >= 10000 ? 'text-amber-400' :
                                        'text-rose-400'
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
                                <label class="text-sm text-slate-400">Alamat</label>
                                <p class="text-white mt-1">{{ user.student.alamat }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-slate-400">Status RFID</label>
                                <div class="mt-2">
                                    <span v-if="user.student.rfid_uid" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 border border-emerald-500/30 text-emerald-300">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Terdaftar ({{ user.student.rfid_uid }})
                                    </span>
                                    <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-700/50 border border-white/10 text-slate-300">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Belum Terdaftar
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="user.student.photo_path" class="mt-6 pt-6 border-t border-white/10">
                            <label class="text-sm text-slate-400 block mb-3">Foto Siswa</label>
                            <div class="inline-block p-1 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-xl shadow-lg">
                                <img
                                    :src="`/storage/${user.student.photo_path}`"
                                    :alt="user.name"
                                    class="h-48 w-48 object-cover rounded-lg block"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl sm:rounded-xl border border-white/10 mx-3 sm:mx-0">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Informasi Akun</h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-4 p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl">
                                <span class="bg-indigo-500/20 p-2 rounded-lg text-indigo-400 mt-0.5">
                                    <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-medium text-indigo-300">Hak Akses Sistem</p>
                                    <p class="text-sm text-indigo-200/70 mt-1">
                                        <span v-if="user.role === 'admin'">Memiliki akses penuh ke seluruh sistem termasuk manajemen user, produk, dan laporan.</span>
                                        <span v-else-if="user.role === 'kasir'">Staf Koperasi dapat melakukan transaksi penjualan dan mengakses fitur operasional sesuai izin yang diberikan.</span>
                                        <span v-else-if="user.role === 'siswa'">Dapat melihat profil, saldo, dan riwayat transaksi pribadi.</span>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-slate-900/50 border border-white/5 rounded-xl">
                                    <label class="text-sm text-slate-400">Status Verifikasi Email</label>
                                    <p class="mt-2">
                                        <span v-if="user.email_verified_at" class="inline-flex items-center text-emerald-400 font-medium text-sm">
                                            <svg class="w-5 h-5 inline mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Terverifikasi
                                        </span>
                                        <span v-else class="inline-flex items-center text-slate-400 font-medium text-sm">
                                            Belum terverifikasi
                                        </span>
                                    </p>
                                </div>

                                <div class="p-4 bg-slate-900/50 border border-white/5 rounded-xl">
                                    <label class="text-sm text-slate-400">Terakhir Update</label>
                                    <div class="flex items-center gap-2 mt-2 text-white font-medium text-sm">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ formatDate(user.updated_at) }}
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
