<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    category_id: '',
    name: '',
    description: '',
    image: null,
    harga_beli: 0,
    harga_jual: 0,
    barcode: '',
});

const imagePreview = ref(null);

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Tambah Produk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Produk</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    required
                                >
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-2" />
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Produk</label>
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
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    rows="4"
                                ></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gambar Produk</label>
                                <input
                                    id="image"
                                    type="file"
                                    @change="handleImageChange"
                                    accept="image/*"
                                    class="w-full text-sm text-gray-500 dark:text-gray-400
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-200
                                        hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800 file:cursor-pointer"
                                />
                                <InputError :message="form.errors.image" class="mt-2" />
                                <div v-if="imagePreview" class="mt-3">
                                    <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded border-2 border-gray-200 dark:border-gray-700" />
                                </div>
                            </div>

                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                                <input
                                    id="barcode"
                                    type="text"
                                    v-model="form.barcode"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                                <InputError :message="form.errors.barcode" class="mt-2" />
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stok akan dikelola di menu "Barang Masuk"</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="harga_beli" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli (Rp)</label>
                                    <input
                                        id="harga_beli"
                                        type="number"
                                        v-model="form.harga_beli"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    <InputError :message="form.errors.harga_beli" class="mt-2" />
                                </div>

                                <div>
                                    <label for="harga_jual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual (Rp)</label>
                                    <input
                                        id="harga_jual"
                                        type="number"
                                        v-model="form.harga_jual"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    <InputError :message="form.errors.harga_jual" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                                >
                                    <span v-if="!form.processing">Simpan</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('products.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">
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
