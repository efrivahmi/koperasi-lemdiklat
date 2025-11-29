<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    nis: '',
    kelas: '',
    jenjang: '',
    balance: 0,
    rfid_uid: '',
    foto: null,
});

const fotoPreview = ref(null);

const handleFotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.foto = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            fotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('students.store'));
};
</script>

<template>
    <Head title="Tambah Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Siswa</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="name" value="Nama Lengkap" />
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
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="password" value="Password" />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        v-model="form.password"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="nis" value="NISN" />
                                    <TextInput
                                        id="nis"
                                        type="text"
                                        v-model="form.nis"
                                        class="mt-1 block w-full"
                                        placeholder="Nomor Induk Siswa Nasional"
                                        required
                                    />
                                    <InputError :message="form.errors.nis" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="kelas" value="Kelas" />
                                    <TextInput
                                        id="kelas"
                                        type="text"
                                        v-model="form.kelas"
                                        class="mt-1 block w-full"
                                        placeholder="Contoh: X IPA 1"
                                        required
                                    />
                                    <InputError :message="form.errors.kelas" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="jenjang" value="Jenjang" />
                                    <select
                                        id="jenjang"
                                        v-model="form.jenjang"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="" disabled>Pilih Jenjang</option>
                                        <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                        <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                    </select>
                                    <InputError :message="form.errors.jenjang" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="balance" value="Saldo Awal (Rp)" />
                                    <TextInput
                                        id="balance"
                                        type="number"
                                        v-model="form.balance"
                                        class="mt-1 block w-full"
                                        min="0"
                                        step="0.01"
                                    />
                                    <InputError :message="form.errors.balance" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="rfid_uid" value="RFID UID (Opsional)" />
                                    <TextInput
                                        id="rfid_uid"
                                        type="text"
                                        v-model="form.rfid_uid"
                                        class="mt-1 block w-full"
                                        placeholder="Kosongkan untuk auto-generate"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">RFID UID akan di-generate otomatis jika dikosongkan</p>
                                    <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="foto" value="Foto Siswa" />
                                <input
                                    id="foto"
                                    type="file"
                                    @change="handleFotoChange"
                                    accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100"
                                />
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                                <Link :href="route('students.index')" class="text-gray-600 hover:text-gray-900">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
