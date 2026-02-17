<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

import { getUnitGroupName } from '@/Constants/Units';

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

const resolveUnitGroupName = (id) => getUnitGroupName(id);

const expandedRows = ref([]);

const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
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
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Parent Kategori
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Kelompok Satuan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Dibuat Oleh
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template v-for="category in categories.data" :key="category.id">
                                    <!-- Parent Row -->
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                            <div class="flex items-center">
                                                <button 
                                                    v-if="category.children && category.children.length > 0"
                                                    @click="toggleRow(category.id)"
                                                    class="mr-2 p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none"
                                                >
                                                    <svg 
                                                        class="w-4 h-4 transform transition-transform duration-200"
                                                        :class="expandedRows.includes(category.id) ? 'rotate-90' : ''"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                                <span v-else class="w-6 inline-block"></span> <!-- Spacer -->
                                                {{ category.name }}
                                                <span class="ml-2 text-xs font-normal text-gray-500">({{ category.children ? category.children.length : 0 }})</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                Main Category
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ resolveUnitGroupName(category.unit_group) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ category.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <AuditInfo :user="category.creator" :timestamp="category.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link v-if="can('categories.edit')" :href="route('categories.edit', category.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</Link>
                                            <button v-if="can('categories.delete')" @click="deleteCategory(category.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                        </td>
                                    </tr>

                                    <!-- Children Rows -->
                                    <template v-if="expandedRows.includes(category.id)">
                                        <tr v-for="child in category.children" :key="child.id" class="bg-gray-50 dark:bg-slate-800/50 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 pl-12 border-l-4 border-indigo-500/20">
                                                <div class="flex items-center">
                                                     <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ child.name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <span class="text-xs text-gray-400">Sub dari:</span> {{ category.name }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ resolveUnitGroupName(child.unit_group) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ child.description || '-' }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                <AuditInfo :user="child.creator" :timestamp="child.created_at" label="Dibuat" />
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                <Link v-if="can('categories.edit')" :href="route('categories.edit', child.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</Link>
                                                <button v-if="can('categories.delete')" @click="deleteCategory(child.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
