<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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

// Group permissions into 2 main sections: Pages and Actions
const groupedPermissions = computed(() => {
    const sections = {
        pages: {},
        actions: {}
    };

    const permissions = props.availablePermissions;

    Object.entries(permissions).forEach(([key, label]) => {
        const isPage = key.startsWith('page_');
        const isAction = key.startsWith('action_');

        if (isPage) {
            // Group pages by category
            let category = 'Lainnya';

            if (key.includes('categories') || key.includes('products') || key.includes('students')) {
                category = '📁 Master Data';
            } else if (key.includes('stock') || key.includes('vouchers') || key.includes('expenses') || key.includes('transactions')) {
                category = '💼 Inventori & Keuangan';
            } else if (key.includes('pos')) {
                category = '🛒 Point of Sale';
            } else if (key.includes('reports')) {
                category = '📊 Laporan';
            } else if (key.includes('users')) {
                category = '👤 Manajemen User';
            }

            if (!sections.pages[category]) {
                sections.pages[category] = [];
            }
            sections.pages[category].push({ key, label });
        } else if (isAction) {
            // Group actions by entity
            let category = 'Lainnya';

            if (key.includes('category')) {
                category = '📁 Kategori';
            } else if (key.includes('product') || key.includes('barcode')) {
                category = '📦 Produk';
            } else if (key.includes('student') || key.includes('rfid') || key.includes('card')) {
                category = '👥 Siswa';
            } else if (key.includes('stock_in')) {
                category = '📥 Stok Masuk';
            } else if (key.includes('voucher')) {
                category = '🎟️ Voucher';
            } else if (key.includes('expense')) {
                category = '💸 Pengeluaran';
            } else if (key.includes('topup') || key.includes('transaction') || key.includes('receipt')) {
                category = '💳 Transaksi';
            } else if (key.includes('sale') && !key.includes('report')) {
                category = '🛒 POS';
            } else if (key.includes('report') || key.includes('export') || key.includes('print_report')) {
                category = '📊 Laporan';
            } else if (key.includes('user') || key.includes('permissions')) {
                category = '👤 Manajemen User';
            }

            if (!sections.actions[category]) {
                sections.actions[category] = [];
            }
            sections.actions[category].push({ key, label });
        }
    });

    return sections;
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Nama Lengkap" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="role" value="Role" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Staf Koperasi</option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-2" />
                            </div>

                            <!-- Permissions (Hanya untuk Kasir) -->
                            <div v-if="form.role === 'kasir'" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <InputLabel value="Hak Akses Staf Koperasi" class="mb-3 text-lg font-semibold" />

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
                                    <p class="text-sm text-gray-600 mb-4">Pilih fitur-fitur yang dapat diakses:</p>

                                    <!-- Section 1: Akses Halaman -->
                                    <div class="mb-6">
                                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-3">
                                            <h3 class="font-bold text-blue-900 text-base flex items-center gap-2">
                                                🌐 AKSES HALAMAN (VIEW/READ)
                                            </h3>
                                            <p class="text-xs text-blue-700 mt-1">Pilih halaman-halaman yang dapat diakses oleh staf koperasi</p>
                                        </div>
                                        <div class="space-y-3">
                                            <div v-for="(permissions, category) in groupedPermissions.pages" :key="`page-${category}`" class="bg-white p-3 rounded-lg border border-blue-200">
                                                <h4 class="font-semibold text-sm text-gray-700 mb-2">{{ category }}</h4>
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
                                                            class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                        />
                                                        <label
                                                            :for="`permission-${permission.key}`"
                                                            class="ml-3 text-sm text-gray-600 cursor-pointer hover:text-gray-900"
                                                        >
                                                            {{ permission.label.replace('[Halaman] ', '') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section 2: Aksi/Tindakan -->
                                    <div>
                                        <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-3">
                                            <h3 class="font-bold text-green-900 text-base flex items-center gap-2">
                                                ⚡ AKSI/TINDAKAN (ACTIONS)
                                            </h3>
                                            <p class="text-xs text-green-700 mt-1">Pilih aksi-aksi yang dapat dilakukan oleh staf koperasi</p>
                                        </div>
                                        <div class="space-y-3">
                                            <div v-for="(permissions, category) in groupedPermissions.actions" :key="`action-${category}`" class="bg-white p-3 rounded-lg border border-green-200">
                                                <h4 class="font-semibold text-sm text-gray-700 mb-2">{{ category }}</h4>
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
                                                            class="mt-1 rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                                                        />
                                                        <label
                                                            :for="`permission-${permission.key}`"
                                                            class="ml-3 text-sm text-gray-600 cursor-pointer hover:text-gray-900"
                                                        >
                                                            {{ permission.label.replace('[Aksi] ', '') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Show all selected permissions when full access -->
                                <div v-if="accessType === 'full'" class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm font-semibold text-green-700 mb-4 flex items-center gap-2">
                                        ✅ Semua fitur dapat diakses ({{ Object.keys(availablePermissions).length }} permissions):
                                    </p>

                                    <!-- Halaman Section -->
                                    <div class="mb-4">
                                        <div class="bg-blue-50 border-l-4 border-blue-500 p-2 mb-2">
                                            <h4 class="font-semibold text-sm text-blue-900">🌐 Akses Halaman</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="(permissions, category) in groupedPermissions.pages" :key="`full-page-${category}`" class="bg-blue-50/50 p-2 rounded border border-blue-200">
                                                <p class="font-medium text-xs text-blue-800 mb-1">{{ category }}</p>
                                                <div class="space-y-0.5">
                                                    <div
                                                        v-for="permission in permissions"
                                                        :key="permission.key"
                                                        class="text-xs text-blue-700 pl-2"
                                                    >
                                                        ✓ {{ permission.label.replace('[Halaman] ', '') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions Section -->
                                    <div>
                                        <div class="bg-green-50 border-l-4 border-green-500 p-2 mb-2">
                                            <h4 class="font-semibold text-sm text-green-900">⚡ Aksi/Tindakan</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="(permissions, category) in groupedPermissions.actions" :key="`full-action-${category}`" class="bg-green-50/50 p-2 rounded border border-green-200">
                                                <p class="font-medium text-xs text-green-800 mb-1">{{ category }}</p>
                                                <div class="space-y-0.5">
                                                    <div
                                                        v-for="permission in permissions"
                                                        :key="permission.key"
                                                        class="text-xs text-green-700 pl-2"
                                                    >
                                                        ✓ {{ permission.label.replace('[Aksi] ', '') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <InputError :message="form.errors.permissions" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Password Baru (Opsional)" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    class="mt-1 block w-full"
                                />
                                <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Konfirmasi Password Baru" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link :href="route('users.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
