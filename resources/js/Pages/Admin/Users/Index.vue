<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

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
        return 'bg-purple-500/20 text-purple-300 border border-purple-500/30';
    } else if (role === 'kasir') {
        return 'bg-blue-500/20 text-blue-300 border border-blue-500/30';
    }
    return 'bg-slate-500/20 text-slate-300 border border-slate-500/30';
};

const getRoleLabel = (role) => {
    if (role === 'admin') return 'Admin';
    if (role === 'kasir') return 'Staf Koperasi';
    return role;
};
</script>

<template>
    <Head title="Manajemen Pengguna (Staf)" />

    <AuthenticatedLayout>
        <template #mobileTitle>Pengguna</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Manajemen Pengguna (Staf)</h2>
        </template>

        <div class="py-6 sm:py-12 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl p-4 sm:p-6 transition-colors">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <!-- Search Bar -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari pengguna (nama, email)..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-slate-900/50 border-white/10 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm transition-all"
                                />
                            </div>
                        </div>
                        <!-- Action Button -->
                        <Link v-if="can('users.create')" :href="route('users.create')" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all">
                            <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Tambah Pengguna</span>
                            <span class="sm:hidden">Pengguna</span>
                        </Link>
                    </div>
                </div>
                <!-- Success Message -->
                <div v-if="flashSuccess" class="mb-6 bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-lg relative" role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline ml-1">{{ flashSuccess }}</span>
                </div>

                <!-- Error Message -->
                <div v-if="flashError" class="mb-6 bg-rose-500/20 border border-rose-500/30 text-rose-300 px-4 py-3 rounded-lg relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline ml-1">{{ flashError }}</span>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="users.data.length === 0 && !search"
                    icon="users"
                    title="Belum Ada Pengguna"
                    description="Tambahkan pengguna baru untuk mengelola sistem koperasi."
                    :action-url="route('users.create')"
                    action-text="Tambah Pengguna Pertama"
                />

                <!-- Table with Data -->
                <div v-else class="bg-slate-800/50 backdrop-blur-md overflow-hidden rounded-xl border border-white/10 shadow-xl">
                    <div class="p-0 sm:p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden sm:table-cell">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 bg-transparent">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="getRoleBadge(user.role)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden sm:table-cell">
                                            <AuditInfo :user="user.creator" :timestamp="user.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="user.updater" :timestamp="user.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link v-if="can('users.show')" :href="route('users.show', user.id)" class="text-emerald-400 hover:text-emerald-300 mr-3">Detail</Link>
                                            <Link v-if="can('users.edit')" :href="route('users.edit', user.id)" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</Link>
                                            <button v-if="can('users.delete')" @click="deleteUser(user.id)" class="text-rose-400 hover:text-rose-300">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0 && search">
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="text-slate-400">
                                                <svg class="mx-auto h-12 w-12 text-slate-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-white mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-slate-400">Tidak ditemukan pengguna dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links.length > 3" class="p-4 border-t border-white/10 flex justify-center space-x-2">
                            <template v-for="link in users.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded-lg transition-colors text-sm',
                                        link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 hover:bg-slate-600/50 border border-white/5 text-slate-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded-lg bg-slate-800/50 border border-white/5 text-slate-500 opacity-50 cursor-not-allowed text-sm'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
