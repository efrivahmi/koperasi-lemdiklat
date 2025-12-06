<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    nominal: '',
    quantity: 1,
    expired_at: '',
});

const submit = () => {
    form.post(route('vouchers.store'));
};
</script>

<template>
    <Head title="Generate Voucher" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Generate Voucher</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="nominal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nominal Voucher (Rp)</label>
                                <input
                                    id="nominal"
                                    type="number"
                                    v-model="form.nominal"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    min="1000"
                                    step="1000"
                                    required
                                />
                                <InputError :message="form.errors.nominal" class="mt-2" />
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Voucher</label>
                                <input
                                    id="quantity"
                                    type="number"
                                    v-model="form.quantity"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                    min="1"
                                    max="100"
                                    required
                                />
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Maks. 100 voucher per generate</p>
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <label for="expired_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Expired (Opsional)</label>
                                <input
                                    id="expired_at"
                                    type="date"
                                    v-model="form.expired_at"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                                <InputError :message="form.errors.expired_at" class="mt-2" />
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <span v-if="!form.processing">Generate Voucher</span>
                                    <span v-else>Generating...</span>
                                </button>
                                <Link :href="route('vouchers.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
