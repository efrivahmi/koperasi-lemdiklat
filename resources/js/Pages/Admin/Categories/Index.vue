<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

defineProps({
    categories: Object,
    filters: Object,
});

const deleteCategory = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        router.delete(route('categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Kategori Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Kategori</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Kategori Produk</h2>
        </template>

        <div class="py-6 sm:py-12 bg-gray-100 dark:bg-slate-900 min-h-screen transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <TableToolbar
                    title="Kategori Produk"
                    description="Kelola kategori produk koperasi"
                    :search-term="filters.search"
                    search-route="categories.index"
                >
                    <template #actions>
                        <Link v-if="can('categories.create')" :href="route('categories.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Kategori
                        </Link>
                    </template>
                </TableToolbar>
                <!-- Empty State -->
                <EmptyState
                    v-if="categories.data.length === 0"
                    icon="tag"
                    title="Belum Ada Kategori"
                    description="Buat kategori pertama untuk mengelompokkan produk di koperasi."
                    :action-url="route('categories.create')"
                    action-text="Tambah Kategori Pertama"
                />

                <!-- Table with Data -->
                <div v-else class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-slate-700 transition-colors">
                    <div class="p-6 text-slate-900 dark:text-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ category.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ category.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ category.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="category.creator" :timestamp="category.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="category.updater" :timestamp="category.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link v-if="can('categories.edit')" :href="route('categories.edit', category.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</Link>
                                            <button v-if="can('categories.delete')" @click="deleteCategory(category.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
