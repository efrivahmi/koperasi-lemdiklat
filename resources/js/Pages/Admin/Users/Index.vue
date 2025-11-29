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
        return 'bg-purple-100 text-purple-800';
    } else if (role === 'kasir') {
        return 'bg-blue-100 text-blue-800';
    }
    return 'bg-gray-100 text-gray-800';
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
                <Link :href="route('users.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Tambah Pengguna
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="flashSuccess" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flashSuccess }}</span>
                </div>

                <!-- Error Message -->
                <div v-if="flashError" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
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
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Search Bar -->
                        <div class="mb-4">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari pengguna (nama, email)..."
                                class="w-full md:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="getRoleBadge(user.role)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                            <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0 && search">
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500">Tidak ditemukan pengguna dengan kata kunci "{{ search }}"</p>
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
                                        link.active ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded bg-gray-200 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
