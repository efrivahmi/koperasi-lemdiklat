<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generate Voucher</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="nominal" value="Nominal Voucher (Rp)" />
                                <TextInput id="nominal" type="number" v-model="form.nominal" class="mt-1 block w-full" min="1000" step="1000" required />
                                <InputError :message="form.errors.nominal" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="quantity" value="Jumlah Voucher" />
                                <TextInput id="quantity" type="number" v-model="form.quantity" class="mt-1 block w-full" min="1" max="100" required />
                                <p class="text-sm text-gray-500 mt-1">Maks. 100 voucher per generate</p>
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="expired_at" value="Tanggal Expired (Opsional)" />
                                <TextInput id="expired_at" type="date" v-model="form.expired_at" class="mt-1 block w-full" />
                                <InputError :message="form.errors.expired_at" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Generate Voucher</PrimaryButton>
                                <Link :href="route('vouchers.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
