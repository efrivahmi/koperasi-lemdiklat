<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

import { UNIT_CATEGORIES } from '@/Constants/Units';

const props = defineProps({
    category: Object,
    parents: Array,
});

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id || '',
    unit_group: props.category.unit_group || '',
    description: props.category.description,
});

const submit = () => {
    form.put(route('kasir.categories.update', props.category.id));
};
</script>

<template>
    <Head title="Kategori" />

    <AuthenticatedLayout>
        <template #mobileTitle>Kategori</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Kategori</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-900/80 via-indigo-900/80 to-blue-900/80 backdrop-blur-md rounded-xl shadow-2xl p-6 text-white border border-white/10 relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/5 opacity-20 pointer-events-none"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="p-3 bg-white/10 rounded-lg">
                            <svg class="w-8 h-8 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-indigo-200">Edit Kategori</h3>
                            <p class="text-indigo-200/80 text-sm mt-1">Perbarui informasi kategori produk koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="parent_id" class="block text-sm font-medium text-slate-300 mb-2">
                                    Kategori Induk (Parent)
                                </label>
                                <SearchableSelect
                                    v-model="form.parent_id"
                                    :options="parents"
                                    placeholder="Pilih Kategori Induk (Opsional)"
                                    search-placeholder="Cari kategori..."
                                    label-key="name"
                                    value-key="id"
                                />
                                <p class="text-xs text-slate-400 mt-2">Kosongkan jika ini adalah Kategori Utama.</p>
                                <InputError :message="form.errors.parent_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-300 mb-2">
                                    Nama Kategori
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="w-full bg-slate-900/50 border border-slate-600/50 text-white rounded-lg focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-400 block p-2.5 transition-all shadow-sm"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-slate-300 mb-2">
                                    Deskripsi
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="w-full bg-slate-900/50 border border-slate-600/50 text-white rounded-lg focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-400 block p-2.5 transition-all shadow-sm"
                                    rows="4"
                                ></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4 border-t border-white/5">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Perubahan</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('kasir.categories.index')" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-lg shadow-sm border border-white/5 transition-all">
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
