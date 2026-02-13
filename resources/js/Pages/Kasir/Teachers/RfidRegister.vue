<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    teacher: Object,
});

const form = useForm({
    rfid_uid: props.teacher.rfid_uid || '',
});

const submit = () => {
    form.post(route('kasir.teachers.rfid.store', props.teacher.id));
};
</script>

<template>
    <Head title="Registrasi RFID Guru" />
    <AuthenticatedLayout>
        <template #mobileTitle>RFID Guru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Registrasi RFID Guru</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg p-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ teacher.user.name }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">NIP: {{ teacher.nip }}</p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="rfid_uid" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">RFID UID</label>
                            <input
                                id="rfid_uid"
                                type="text"
                                v-model="form.rfid_uid"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-3 text-lg"
                                placeholder="Tap kartu RFID di reader..."
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.rfid_uid" class="mt-2" />
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Dekatkan kartu RFID ke reader untuk mengisi UID secara otomatis</p>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-bold shadow-lg disabled:opacity-50">
                                <span v-if="!form.processing">Daftarkan RFID</span>
                                <span v-else>Mendaftarkan...</span>
                            </button>
                            <Link :href="route('kasir.teachers.index')" class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold shadow-sm">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
