<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const page = usePage();
const search = ref(props.filters.search || '');

// Get flash messages
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

watch(search, (value) => {
    router.get(route('users.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        router.delete(route('users.destroy', id), {
            preserveState: false,
            onSuccess: () => {
                // Message will be shown via flash
            },
            onError: (errors) => {
                alert('Terjadi kesalahan: ' + (errors.message || 'Silakan coba lagi'));
            }
        });
    }
};

const getRoleBadge = (role) => {
    if (role === 'admin') {
        return 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300';
    } else if (role === 'kasir') {
        return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
    }
    return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};

const getRoleLabel = (role) => {
    if (role === 'admin') return 'Admin';
    if (role === 'kasir') return 'Staf Koperasi';
    return role;
};
</script>

<template>
    <Head title="Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Pengguna (Staf)</h2>
                <Link :href="route('users.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition">
                    Tambah Pengguna
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="flashSuccess" class="mb-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flashSuccess }}</span>
                </div>

                <!-- Error Message -->
                <div v-if="flashError" class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flashError }}</span>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="users.data.length === 0 && !search"
                    icon="users"
                    title="Belum Ada Pengguna"
                    description="Tambahkan pengguna untuk mengelola akses sistem koperasi."
                    :action-url="route('users.create')"
                    action-text="Tambah Pengguna Pertama"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Search Bar -->
                        <div class="mb-4">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari pengguna (nama, email)..."
                                class="w-full md:w-1/2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="getRoleBadge(user.role)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('users.edit', user.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</Link>
                                            <button @click="deleteUser(user.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0 && search">
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ditemukan pengguna dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in users.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded',
                                        link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
