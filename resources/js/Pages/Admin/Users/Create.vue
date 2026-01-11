<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    availablePermissions: Object, // Struktur Data: { "Nama Grup": [ {key, label}, ... ] }
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'kasir',
    permissions: [],
});

const accessType = ref('custom');

// Helper Icon berdasarkan nama grup
const getGroupIcon = (groupName) => {
    const name = groupName.toLowerCase();
    if (name.includes('produk') || name.includes('kategori') || name.includes('master')) return '📁';
    if (name.includes('transaksi') || name.includes('kasir') || name.includes('pos')) return '🛒';
    if (name.includes('laporan') || name.includes('keuangan')) return '📊';
    if (name.includes('user') || name.includes('siswa') || name.includes('guru')) return '👤';
    return '🔒';
};

// Watcher: Auto-select jika Full Access dipilih
watch(accessType, (newValue) => {
    if (newValue === 'full') {
        // Ambil semua key dari semua grup di props.availablePermissions
        const allKeys = [];
        Object.values(props.availablePermissions).forEach(groupItems => {
            groupItems.forEach(item => allKeys.push(item.key));
        });
        form.permissions = allKeys;
    } else {
        form.permissions = [];
    }
});

const submit = () => {
    form.post(route('users.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Tambah Pengguna" />

    <AuthenticatedLayout>
        <template #mobileTitle>Pengguna</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengguna</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Tambah Pengguna Baru</h3>
                            <p class="text-purple-100 text-sm">Buat akun pengguna baru untuk mengelola sistem koperasi</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30">
                    <div class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                                <input id="name" type="text" v-model="form.name" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2" required autofocus />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input id="email" type="email" v-model="form.email" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2" required />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                    <input id="password" type="password" v-model="form.password" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2" required />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2" required />
                                </div>
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                                <select id="role" v-model="form.role" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2" required>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Staf Koperasi (Kasir)</option>
                                    <option value="master">Master (Super Admin)</option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-2" />
                            </div>

                            <div v-if="form.role === 'kasir'" class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 dark:from-gray-700 dark:via-gray-700 dark:to-gray-700 p-6 rounded-xl border-2 border-purple-300 dark:border-purple-500/50 shadow-lg">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <label class="text-lg font-bold text-gray-800 dark:text-gray-100">Hak Akses Staf Koperasi</label>
                                </div>

                                <div class="mb-6 space-y-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">Pilih tipe akses:</p>
                                    <div class="space-y-3">
                                        <label class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'full' ? 'bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 border-purple-500 shadow-md' : 'bg-white dark:bg-gray-800 border-gray-300 hover:border-purple-300'">
                                            <input type="radio" value="full" v-model="accessType" class="mt-1 text-purple-600 focus:ring-purple-500" />
                                            <div class="ml-3">
                                                <span class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">🔓 Akses Penuh</span>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Mendapatkan akses ke semua fitur yang tersedia.</p>
                                            </div>
                                        </label>

                                        <label class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'custom' ? 'bg-gradient-to-r from-indigo-100 to-blue-100 dark:from-indigo-900/30 dark:to-blue-900/30 border-indigo-500 shadow-md' : 'bg-white dark:bg-gray-800 border-gray-300 hover:border-indigo-300'">
                                            <input type="radio" value="custom" v-model="accessType" class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                                            <div class="ml-3">
                                                <span class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">⚙️ Akses Custom</span>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pilih fitur tertentu secara manual.</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="accessType === 'custom'" class="mt-4 pt-4 border-t-2 border-purple-200 dark:border-purple-500/30">
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Pilih modul yang diizinkan:</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(items, groupName) in availablePermissions" :key="groupName" 
                                             class="bg-white dark:bg-gray-900 p-4 rounded-xl border-2 border-indigo-200 dark:border-indigo-500/30 hover:border-indigo-500 transition-all shadow-sm">
                                            
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                                <span>{{ getGroupIcon(groupName) }}</span>
                                                {{ groupName }}
                                            </h4>
                                            
                                            <div class="space-y-2">
                                                <div v-for="permission in items" :key="permission.key" class="flex items-start">
                                                    <input type="checkbox" :id="`perm-${permission.key}`" :value="permission.key" v-model="form.permissions"
                                                           class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" />
                                                    <label :for="`perm-${permission.key}`" class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-purple-600 transition-colors">
                                                        {{ permission.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.permissions" class="mt-2" />
                                </div>

                                <div v-if="accessType === 'full'" class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 rounded-lg text-green-800 dark:text-green-300 text-sm font-semibold text-center">
                                    ✅ Semua modul ({{ Object.keys(availablePermissions).length }} Kategori) telah dipilih secara otomatis.
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-6 border-t-2 border-gray-200 dark:border-gray-700">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-bold text-sm transition-all shadow-lg disabled:opacity-50">
                                    <span v-if="!form.processing">Simpan User</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('users.index')" class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-md">
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