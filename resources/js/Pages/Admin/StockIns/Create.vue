<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    products: Array,
});

const form = useForm({
    product_id: '',
    quantity: '',
    supplier: '',
    keterangan: '',
});

const submit = () => {
    form.post(route('stock-ins.store'));
};
</script>

<template>
    <Head title="Tambah Stok Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Stok Masuk</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="product_id" value="Produk" />
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Pilih Produk</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }} - {{ product.category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="quantity" value="Jumlah / Quantity" />
                                <TextInput
                                    id="quantity"
                                    type="number"
                                    v-model="form.quantity"
                                    class="mt-1 block w-full"
                                    min="1"
                                    required
                                />
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="supplier" value="Supplier / Pemasok" />
                                <TextInput
                                    id="supplier"
                                    type="text"
                                    v-model="form.supplier"
                                    class="mt-1 block w-full"
                                    placeholder="Nama supplier (opsional)"
                                />
                                <InputError :message="form.errors.supplier" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="keterangan" value="Keterangan" />
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3"
                                    placeholder="Catatan tambahan (opsional)"
                                ></textarea>
                                <InputError :message="form.errors.keterangan" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                                <Link :href="route('stock-ins.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
