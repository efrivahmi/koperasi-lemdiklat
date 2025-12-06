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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Pengguna</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
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
                            <div v-if="form.role === 'kasir'" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                <label class="block text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3">Hak Akses Staf Koperasi</label>

                                <!-- Access Type Selection -->
                                <div class="mb-4 space-y-3">
                                    <p class="text-sm text-gray-600">Pilih tipe akses untuk staf koperasi ini:</p>

                                    <div class="space-y-2">
                                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-100 transition-colors" :class="{ 'bg-indigo-50 border-indigo-500': accessType === 'full' }">
                                            <input
                                                type="radio"
                                                value="full"
                                                v-model="accessType"
                                                class="mt-1 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <div class="ml-3">
                                                <span class="font-semibold text-gray-800">🔓 Akses Penuh (Seperti Admin)</span>
                                                <p class="text-sm text-gray-600 mt-1">Staf Koperasi mendapatkan akses ke semua fitur yang tersedia</p>
                                            </div>
                                        </label>

                                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-100 transition-colors" :class="{ 'bg-indigo-50 border-indigo-500': accessType === 'custom' }">
                                            <input
                                                type="radio"
                                                value="custom"
                                                v-model="accessType"
                                                class="mt-1 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <div class="ml-3">
                                                <span class="font-semibold text-gray-800">⚙️ Akses Custom</span>
                                                <p class="text-sm text-gray-600 mt-1">Pilih fitur-fitur tertentu yang dapat diakses staf koperasi</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Custom Permissions Checkboxes (Hanya tampil jika custom) -->
                                <div v-if="accessType === 'custom'" class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600 mb-4">Pilih modul-modul fitur yang dapat diakses:</p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(permissions, category) in groupedPermissions" :key="category" class="bg-white p-4 rounded-lg border-2 border-indigo-200 hover:border-indigo-400 transition-colors">
                                            <h4 class="font-bold text-sm text-gray-800 mb-3 flex items-center gap-2">
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
                                                        class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    />
                                                    <label
                                                        :for="`permission-${permission.key}`"
                                                        class="ml-3 text-sm text-gray-700 cursor-pointer hover:text-gray-900 leading-relaxed"
                                                    >
                                                        {{ permission.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Show all selected permissions when full access -->
                                <div v-if="accessType === 'full'" class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm font-semibold text-green-700 mb-4 flex items-center gap-2">
                                        ✅ Semua modul dapat diakses ({{ Object.keys(availablePermissions).length }} modul):
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="(permissions, category) in groupedPermissions" :key="`full-${category}`" class="bg-green-50 p-3 rounded-lg border border-green-200">
                                            <p class="font-semibold text-sm text-green-800 mb-2 flex items-center gap-2">
                                                <span v-if="category === 'Master Data'">📁</span>
                                                <span v-else-if="category === 'Inventori & Keuangan'">💼</span>
                                                <span v-else-if="category === 'Point of Sale'">🛒</span>
                                                <span v-else-if="category === 'Laporan'">📊</span>
                                                <span v-else-if="category === 'Manajemen User'">👤</span>
                                                {{ category }}
                                            </p>
                                            <div class="space-y-1">
                                                <div
                                                    v-for="permission in permissions"
                                                    :key="permission.key"
                                                    class="text-xs text-green-700 pl-2"
                                                >
                                                    ✓ {{ permission.label }}
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

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <span v-if="!form.processing">Update</span>
                                    <span v-else>Mengupdate...</span>
                                </button>
                                <Link :href="route('users.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
