<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

defineProps({
    categories: Object,
});

const deleteCategory = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        router.delete(route('categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kategori Produk</h2>
                <Link :href="route('categories.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Tambah Kategori
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="category in categories.data" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ category.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ category.name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ category.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="category.creator" :timestamp="category.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="category.updater" :timestamp="category.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                            <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900">Hapus</button>
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
