<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({ description: '', category: '', amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '' });
</script>

<template>
    <Head title="Tambah Pengeluaran" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengeluaran</h2></template>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="form.post(route('expenses.store'))" class="space-y-6">
                        <div>
                            <InputLabel for="description" value="Deskripsi" />
                            <TextInput id="description" v-model="form.description" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel for="category" value="Kategori" />
                            <TextInput id="category" v-model="form.category" type="text" class="mt-1 block w-full" placeholder="Listrik, Air, Gaji, dll" required />
                        </div>
                        <div>
                            <InputLabel for="amount" value="Jumlah (Rp)" />
                            <TextInput id="amount" v-model="form.amount" type="number" class="mt-1 block w-full" min="0" step="0.01" required />
                        </div>
                        <div>
                            <InputLabel for="expense_date" value="Tanggal" />
                            <TextInput id="expense_date" v-model="form.expense_date" type="date" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <InputLabel for="notes" value="Catatan" />
                            <textarea id="notes" v-model="form.notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea>
                        </div>
                        <div class="flex gap-4">
                            <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                            <Link :href="route('expenses.index')" class="text-gray-600 hover:text-gray-900 flex items-center">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
