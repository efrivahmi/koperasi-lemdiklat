<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import TableToolbar from '@/Components/TableToolbar.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';

import { getUnitGroupName } from '@/Constants/Units';

const { can } = usePermissions();

defineProps({
    categories: Object,
    allCategories: Array,
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

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <div class="relative z-30 mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl p-4">
                    <TableToolbar
                        title="Kategori Produk"
                        description="Kelola kategori produk koperasi"
                        :search-term="filters.search"
                        search-route="categories.index"
                        class="text-white"
                    >
                        <template #search-input>
                             <SearchableSelect
                                :model-value="filters.search"
                                :options="allCategories"
                                placeholder="Cari kategori..."
                                search-placeholder="Ketik nama kategori..."
                                label-key="name"
                                value-key="name"
                                @update:model-value="val => router.get(route('categories.index'), { search: val }, { preserveState: true, replace: true })"
                                class="w-full"
                            />
                        </template>
                        <template #actions>
                            <Link v-if="can('categories.create')" :href="route('categories.create')" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Tambah Kategori</span>
                                <span class="sm:hidden">Tambah</span>
                            </Link>
                        </template>
                    </TableToolbar>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="categories.data.length === 0"
                    icon="tag"
                    title="Belum Ada Kategori"
                    description="Buat kategori pertama untuk mengelompokkan produk di koperasi."
                    :action-url="route('categories.create')"
                    action-text="Tambah Kategori Pertama"
                    class="bg-slate-800/50 border border-white/5 backdrop-blur-sm text-slate-300"
                />

                <!-- Table with Data -->
                <div v-else class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                        Level
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                        Kelompok Satuan
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">
                                        Dibuat
                                    </th>
                                    <th scope="col" class="relative px-6 py-4 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-transparent">
                                <template v-for="category in categories.data" :key="category.id">
                                    <!-- Parent Row -->
                                    <tr 
                                        @click="toggleRow(category.id)"
                                        class="group hover:bg-white/5 transition-colors duration-150 cursor-pointer"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-white group-hover:text-indigo-200">
                                            <div class="flex items-center">
                                                <button 
                                                    v-if="category.children && category.children.length > 0"
                                                    @click.stop="toggleRow(category.id)"
                                                    class="mr-2 p-1 rounded hover:bg-white/10 transition-colors focus:outline-none text-indigo-400"
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
                                                <span class="ml-2 text-xs font-normal text-slate-400">({{ category.children ? category.children.length : 0 }})</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-full bg-indigo-900/30 text-indigo-300 border border-indigo-500/20">
                                                Main Category
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                            {{ resolveUnitGroupName(category.unit_group) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-400 max-w-xs truncate">{{ category.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                                            <AuditInfo :user="category.creator" :timestamp="category.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-3">
                                                 <Link 
                                                    v-if="can('categories.show')"
                                                    :href="route('categories.show', category.id)" 
                                                    class="text-sky-400 hover:text-sky-300 transition-colors"
                                                    title="Detail Kategori"
                                                    @click.stop
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                </Link>
                                                <Link 
                                                    v-if="can('categories.edit')" 
                                                    :href="route('categories.edit', category.id)" 
                                                    class="text-indigo-400 hover:text-indigo-300 transition-colors"
                                                    title="Edit"
                                                    @click.stop
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </Link>
                                                <button 
                                                    v-if="can('categories.delete')" 
                                                    @click.stop="deleteCategory(category.id)" 
                                                    class="text-rose-400 hover:text-rose-300 transition-colors"
                                                    title="Hapus"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Children Rows -->
                                    <template v-if="expandedRows.includes(category.id)">
                                        <tr v-for="child in category.children" :key="child.id" class="bg-slate-900/30 hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-300 pl-12 border-l-4 border-indigo-500/20">
                                                <div class="flex items-center">
                                                     <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ child.name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-400">
                                                <span class="text-xs text-slate-500">Sub dari:</span> {{ category.name }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-400">
                                                {{ resolveUnitGroupName(child.unit_group) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm text-slate-500 max-w-xs truncate">{{ child.description || '-' }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-500 hidden xl:table-cell">
                                                <AuditInfo :user="child.creator" :timestamp="child.created_at" label="Dibuat" />
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-3">
                                                    <!-- Detail button removed for subcategories as requested -->
                                                    <Link v-if="can('categories.edit')" :href="route('categories.edit', child.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                    </Link>
                                                    <button v-if="can('categories.delete')" @click="deleteCategory(child.id)" class="text-rose-400 hover:text-rose-300 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="categories.links && categories.links.length > 3" class="px-6 py-4 border-t border-white/5 bg-slate-900/30 flex justify-center">
                         <div class="flex space-x-1">
                            <template v-for="(link, k) in categories.links" :key="k">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200',
                                        link.active 
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white border border-white/5'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-1.5 rounded-md text-sm font-medium bg-slate-900/50 text-slate-600 border border-white/5 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
