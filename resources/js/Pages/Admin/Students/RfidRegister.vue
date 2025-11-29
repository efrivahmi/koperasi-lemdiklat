<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    student: Object,
});

const form = useForm({
    rfid_uid: props.student.rfid_uid || '',
});

const submit = () => {
    form.post(route('students.rfid.store', props.student.id));
};
</script>

<template>
    <Head title="Daftarkan Kartu RFID" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftarkan Kartu RFID</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Student Info -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-3">Informasi Siswa</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">NISN</p>
                                    <p class="font-medium">{{ student.nis }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Nama</p>
                                    <p class="font-medium">{{ student.user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Kelas</p>
                                    <p class="font-medium">{{ student.kelas }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-medium">{{ student.user.email }}</p>
                                </div>
                            </div>
                            <div v-if="student.rfid_uid" class="mt-4">
                                <p class="text-sm text-gray-600">RFID UID Saat Ini</p>
                                <p class="font-medium text-green-600">{{ student.rfid_uid }}</p>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">Instruksi Pendaftaran RFID:</h4>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-blue-700">
                                <li>Pastikan RFID scanner sudah terhubung dengan komputer</li>
                                <li>Klik pada kolom input di bawah</li>
                                <li>Tempelkan kartu RFID ke scanner</li>
                                <li>UID kartu akan otomatis terisi</li>
                                <li>Klik tombol "Daftarkan" untuk menyimpan</li>
                            </ol>
                        </div>

                        <!-- RFID Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="rfid_uid" value="RFID UID" />
                                <TextInput
                                    id="rfid_uid"
                                    type="text"
                                    v-model="form.rfid_uid"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                    placeholder="Tempelkan kartu RFID pada scanner..."
                                />
                                <p class="mt-1 text-sm text-gray-500">UID akan otomatis terisi saat kartu ditempelkan pada scanner</p>
                                <InputError :message="form.errors.rfid_uid" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing || !form.rfid_uid">
                                    {{ student.rfid_uid ? 'Update RFID' : 'Daftarkan RFID' }}
                                </PrimaryButton>
                                <Link :href="route('students.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
