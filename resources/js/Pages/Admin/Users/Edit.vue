<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    user: Object,
    availablePermissions: Object,
});

// Convert permissions object to array for checkboxes
const getUserPermissions = () => {
    if (!props.user.permissions) return [];

    const perms = [];
    for (const [key, value] of Object.entries(props.user.permissions)) {
        if (value === true) {
            perms.push(key);
        }
    }
    return perms;
};

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    permissions: getUserPermissions(),
});

// Group permissions by category for simpler display
const groupedPermissions = computed(() => {
    const groups = {
        'Master Data': [],
        'Inventori & Keuangan': [],
        'Point of Sale': [],
        'Laporan': [],
        'Manajemen User': []
    };

    Object.entries(props.availablePermissions).forEach(([key, label]) => {
        if (key.includes('categories') || key.includes('products') || key.includes('students')) {
            groups['Master Data'].push({ key, label });
        } else if (key.includes('stock_ins') || key.includes('vouchers') || key.includes('expenses') || key.includes('transactions')) {
            groups['Inventori & Keuangan'].push({ key, label });
        } else if (key.includes('pos')) {
            groups['Point of Sale'].push({ key, label });
        } else if (key.includes('reports')) {
            groups['Laporan'].push({ key, label });
        } else if (key.includes('users')) {
            groups['Manajemen User'].push({ key, label });
        }
    });

    return groups;
});

// Determine initial accessType based on current permissions
const getInitialAccessType = () => {
    const userPerms = getUserPermissions();
    const allPerms = Object.keys(props.availablePermissions);

    // If user has all permissions, set to 'full'
    if (userPerms.length === allPerms.length && allPerms.every(perm => userPerms.includes(perm))) {
        return 'full';
    }

    return 'custom';
};

// Access type: 'full' or 'custom'
const accessType = ref(getInitialAccessType());

// Watch accessType to auto-select all permissions when 'full' is selected
watch(accessType, (newValue) => {
    if (newValue === 'full') {
        // Select all permissions
        form.permissions = Object.keys(props.availablePermissions);
    } else {
        // Clear permissions when switching to custom
        form.permissions = [];
    }
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="Edit Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pengguna</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Edit Data Pengguna</h3>
                            <p class="text-purple-100 text-sm">Perbarui informasi dan hak akses pengguna sistem</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30">
                    <div class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                >
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Staf Koperasi</option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-2" />
                            </div>

                            <!-- Permissions (Hanya untuk Kasir) -->
                            <div v-if="form.role === 'kasir'" class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 dark:from-gray-700 dark:via-gray-700 dark:to-gray-700 p-6 rounded-xl border-2 border-purple-300 dark:border-purple-500/50 shadow-lg">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <label class="text-lg font-bold text-gray-800 dark:text-gray-100">Hak Akses Staf Koperasi</label>
                                </div>

                                <!-- Access Type Selection -->
                                <div class="mb-4 space-y-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">Pilih tipe akses untuk staf koperasi ini:</p>

                                    <div class="space-y-3">
                                        <label class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'full' ? 'bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 border-purple-500 shadow-md' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 hover:border-purple-300 dark:hover:border-purple-500'">
                                            <input
                                                type="radio"
                                                value="full"
                                                v-model="accessType"
                                                class="mt-1 text-purple-600 focus:ring-purple-500"
                                            />
                                            <div class="ml-3">
                                                <span class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                    Akses Penuh (Seperti Admin)
                                                </span>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Staf Koperasi mendapatkan akses ke semua fitur yang tersedia</p>
                                            </div>
                                        </label>

                                        <label class="flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="accessType === 'custom' ? 'bg-gradient-to-r from-indigo-100 to-blue-100 dark:from-indigo-900/30 dark:to-blue-900/30 border-indigo-500 shadow-md' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500'">
                                            <input
                                                type="radio"
                                                value="custom"
                                                v-model="accessType"
                                                class="mt-1 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <div class="ml-3">
                                                <span class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                                    </svg>
                                                    Akses Custom
                                                </span>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pilih fitur-fitur tertentu yang dapat diakses staf koperasi</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Custom Permissions Checkboxes (Hanya tampil jika custom) -->
                                <div v-if="accessType === 'custom'" class="mt-4 pt-4 border-t-2 border-purple-200 dark:border-purple-500/30">
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                        Pilih modul-modul fitur yang dapat diakses:
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(permissions, category) in groupedPermissions" :key="category" class="bg-white dark:bg-gray-900 p-4 rounded-xl border-2 border-indigo-200 dark:border-indigo-500/30 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all duration-200 shadow-md hover:shadow-lg">
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                                <span v-if="category === 'Master Data'">📁</span>
                                                <span v-else-if="category === 'Inventori & Keuangan'">💼</span>
                                                <span v-else-if="category === 'Point of Sale'">🛒</span>
                                                <span v-else-if="category === 'Laporan'">📊</span>
                                                <span v-else-if="category === 'Manajemen User'">👤</span>
                                                {{ category }}
                                            </h4>
                                            <div class="space-y-2">
                                                <div
                                                    v-for="permission in permissions"
                                                    :key="permission.key"
                                                    class="flex items-start"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :id="`permission-${permission.key}`"
                                                        :value="permission.key"
                                                        v-model="form.permissions"
                                                        class="mt-1 rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500"
                                                    />
                                                    <label
                                                        :for="`permission-${permission.key}`"
                                                        class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-purple-600 dark:hover:text-purple-400 leading-relaxed transition-colors"
                                                    >
                                                        {{ permission.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Show all selected permissions when full access -->
                                <div v-if="accessType === 'full'" class="mt-4 pt-4 border-t-2 border-purple-200 dark:border-purple-500/30">
                                    <p class="text-sm font-bold text-green-700 dark:text-green-400 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Semua modul dapat diakses ({{ Object.keys(availablePermissions).length }} modul):
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(permissions, category) in groupedPermissions" :key="`full-${category}`" class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-4 rounded-xl border-2 border-green-300 dark:border-green-500/30 shadow-sm">
                                            <p class="font-bold text-sm text-green-800 dark:text-green-300 mb-3 flex items-center gap-2">
                                                <span v-if="category === 'Master Data'">📁</span>
                                                <span v-else-if="category === 'Inventori & Keuangan'">💼</span>
                                                <span v-else-if="category === 'Point of Sale'">🛒</span>
                                                <span v-else-if="category === 'Laporan'">📊</span>
                                                <span v-else-if="category === 'Manajemen User'">👤</span>
                                                {{ category }}
                                            </p>
                                            <div class="space-y-1.5">
                                                <div
                                                    v-for="permission in permissions"
                                                    :key="permission.key"
                                                    class="text-xs text-green-700 dark:text-green-400 pl-2 flex items-center gap-1.5"
                                                >
                                                    <svg class="w-3 h-3 text-green-600 dark:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ permission.label }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <InputError :message="form.errors.permissions" class="mt-2" />
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru (Opsional)</label>
                                <input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-6 border-t-2 border-gray-200 dark:border-gray-700">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:from-purple-800 active:to-indigo-800 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Perubahan</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('users.index')" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold text-sm transition-all duration-200 shadow-md hover:shadow-lg w-full sm:w-auto">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
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
