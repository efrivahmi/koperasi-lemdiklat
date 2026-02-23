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
            <h2 class="font-semibold text-xl text-white leading-tight">Tambah Pengguna</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg shadow-indigo-500/20 p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Tambah Pengguna Baru</h3>
                            <p class="text-indigo-100 text-sm">Buat akun pengguna baru untuk mengelola sistem koperasi</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                                <input id="name" type="text" v-model="form.name" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm sm:text-base py-2 transition-colors" required autofocus />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                                <input id="email" type="email" v-model="form.email" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm sm:text-base py-2 transition-colors" required />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                                    <input id="password" type="password" v-model="form.password" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm sm:text-base py-2 transition-colors" required />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">Konfirmasi Password</label>
                                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm sm:text-base py-2 transition-colors" required />
                                </div>
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-slate-300 mb-1">Role</label>
                                <select id="role" v-model="form.role" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm sm:text-base py-2 transition-colors" required>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Staf Koperasi (Kasir)</option>
                                    <option v-if="$page.props.auth.user.role === 'master'" value="master">Master (Super Admin)</option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-2" />
                            </div>

                            <div v-if="['kasir', 'admin'].includes(form.role)" class="bg-slate-900/40 p-6 rounded-xl border border-white/5 shadow-inner">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <label class="text-lg font-bold text-white">Hak Akses {{ form.role === 'admin' ? 'Administrator' : 'Staf Koperasi' }}</label>
                                </div>

                                <div class="mb-6 space-y-3">
                                    <p class="text-sm text-slate-300 font-medium">Pilih tipe akses:</p>
                                    <div class="space-y-3">
                                        <label class="flex items-start p-4 border rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'full' ? 'bg-indigo-500/20 border-indigo-500 shadow-md' : 'bg-slate-800/50 border-white/10 hover:border-indigo-500/50'">
                                            <input type="radio" value="full" v-model="accessType" class="mt-1 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-600" />
                                            <div class="ml-3">
                                                <span class="font-bold text-white flex items-center gap-2">🔓 Akses Penuh</span>
                                                <p class="text-sm text-slate-400 mt-1">Mendapatkan akses ke semua fitur yang tersedia.</p>
                                            </div>
                                        </label>

                                        <label class="flex items-start p-4 border rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'custom' ? 'bg-indigo-500/20 border-indigo-500 shadow-md' : 'bg-slate-800/50 border-white/10 hover:border-indigo-500/50'">
                                            <input type="radio" value="custom" v-model="accessType" class="mt-1 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-600" />
                                            <div class="ml-3">
                                                <span class="font-bold text-white flex items-center gap-2">⚙️ Akses Custom</span>
                                                <p class="text-sm text-slate-400 mt-1">Pilih fitur tertentu secara manual.</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="accessType === 'custom'" class="mt-4 pt-4 border-t border-white/10">
                                    <p class="text-sm font-semibold text-slate-300 mb-4">Pilih modul yang diizinkan:</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(items, groupName) in availablePermissions" :key="groupName" 
                                             class="bg-slate-800/80 p-4 rounded-xl border border-white/5 hover:border-indigo-500/50 transition-all shadow-sm">
                                            
                                            <h4 class="font-bold text-sm text-white mb-3 flex items-center gap-2">
                                                <span>{{ getGroupIcon(groupName) }}</span>
                                                {{ groupName }}
                                            </h4>
                                            
                                            <div class="space-y-2">
                                                <div v-for="permission in items" :key="permission.key" class="flex items-start">
                                                    <input type="checkbox" :id="`perm-${permission.key}`" :value="permission.key" v-model="form.permissions"
                                                           class="rounded mt-1 border-slate-600 text-indigo-600 bg-slate-900 focus:ring-indigo-500 shadow-sm" />
                                                    <label :for="`perm-${permission.key}`" class="ml-3 text-sm text-slate-300 cursor-pointer hover:text-indigo-400 transition-colors">
                                                        {{ permission.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.permissions" class="mt-2" />
                                </div>

                                <div v-if="accessType === 'full'" class="mt-4 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-sm font-semibold text-center">
                                    ✅ Semua modul ({{ Object.keys(availablePermissions).length }} Kategori) telah dipilih secara otomatis.
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-6 border-t border-white/10">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg font-bold text-sm transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50">
                                    <span v-if="!form.processing">Simpan User</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('users.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 border border-white/10 text-white rounded-lg font-semibold text-sm transition shadow-md">
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