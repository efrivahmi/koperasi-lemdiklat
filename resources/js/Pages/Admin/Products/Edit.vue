<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    category_id: props.product.category_id,
    name: props.product.name,
    description: props.product.description,
    image: null,
    stock: props.product.stock,
    harga_beli: props.product.harga_beli,
    harga_jual: props.product.harga_jual,
    barcode: props.product.barcode,
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
    form.post(route('products.update', props.product.id), {
        _method: 'put',
    });
};
</script>

<template>
    <Head title="Edit Produk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Produk</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="category_id" value="Kategori" />
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                                <InputLabel for="name" value="Nama Produk" />
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
                                <InputLabel for="description" value="Deskripsi" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="4"
                                ></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="image" value="Gambar Produk" />
                                <div v-if="product.image_path && !imagePreview" class="mb-3">
                                    <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                                    <img :src="`/storage/${product.image_path}`" :alt="product.name" class="h-32 w-32 object-cover rounded" />
                                </div>
                                <input
                                    id="image"
                                    type="file"
                                    @change="handleImageChange"
                                    accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100"
                                />
                                <p class="mt-1 text-sm text-gray-500">Biarkan kosong jika tidak ingin mengubah gambar</p>
                                <InputError :message="form.errors.image" class="mt-2" />
                                <div v-if="imagePreview" class="mt-3">
                                    <p class="text-sm text-gray-600 mb-2">Preview gambar baru:</p>
                                    <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="stock" value="Stok" />
                                    <TextInput
                                        id="stock"
                                        type="number"
                                        v-model="form.stock"
                                        class="mt-1 block w-full"
                                        min="0"
                                        required
                                    />
                                    <InputError :message="form.errors.stock" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="barcode" value="Barcode" />
                                    <TextInput
                                        id="barcode"
                                        type="text"
                                        v-model="form.barcode"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError :message="form.errors.barcode" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="harga_beli" value="Harga Beli (Rp)" />
                                    <TextInput
                                        id="harga_beli"
                                        type="number"
                                        v-model="form.harga_beli"
                                        class="mt-1 block w-full"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    <InputError :message="form.errors.harga_beli" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="harga_jual" value="Harga Jual (Rp)" />
                                    <TextInput
                                        id="harga_jual"
                                        type="number"
                                        v-model="form.harga_jual"
                                        class="mt-1 block w-full"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    <InputError :message="form.errors.harga_jual" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link :href="route('products.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
