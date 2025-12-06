<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
});

const form = useForm({
    name: props.student.user.name,
    email: props.student.user.email,
    password: '',
    nis: props.student.nis,
    kelas: props.student.kelas,
    jenjang: props.student.jenjang || '',
    balance: props.student.balance,
    rfid_uid: props.student.rfid_uid || '',
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
    form.post(route('students.update', props.student.id), {
        _method: 'put',
    });
};
</script>

<template>
    <Head title="Edit Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Siswa</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
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
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        required
                                    />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru (Opsional)</label>
                                <input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NISN</label>
                                    <input
                                        id="nis"
                                        type="text"
                                        v-model="form.nis"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        placeholder="Nomor Induk Siswa Nasional"
                                        required
                                    />
                                    <InputError :message="form.errors.nis" class="mt-2" />
                                </div>

                                <div>
                                    <label for="kelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                                    <input
                                        id="kelas"
                                        type="text"
                                        v-model="form.kelas"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        required
                                    />
                                    <InputError :message="form.errors.kelas" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="jenjang" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenjang</label>
                                    <select
                                        id="jenjang"
                                        v-model="form.jenjang"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        required
                                    >
                                        <option value="" disabled>Pilih Jenjang</option>
                                        <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                        <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                    </select>
                                    <InputError :message="form.errors.jenjang" class="mt-2" />
                                </div>

                                <div>
                                    <label for="balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Saldo (Rp)</label>
                                    <input
                                        id="balance"
                                        type="number"
                                        v-model="form.balance"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        min="0"
                                        step="0.01"
                                    />
                                    <InputError :message="form.errors.balance" class="mt-2" />
                                </div>

                                <div>
                                    <label for="rfid_uid" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RFID UID</label>
                                    <input
                                        id="rfid_uid"
                                        type="text"
                                        v-model="form.rfid_uid"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm sm:text-base py-2"
                                        :disabled="!!student.rfid_uid"
                                    />
                                    <p v-if="student.rfid_uid" class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gunakan halaman "RFID Register" untuk mengubah RFID</p>
                                    <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label for="foto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Siswa</label>
                                <div v-if="student.foto && !fotoPreview" class="mb-3">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Foto saat ini:</p>
                                    <img :src="`/storage/${student.foto}`" :alt="student.user.name" class="h-32 w-32 object-cover rounded-full border-2 border-gray-200 dark:border-gray-700" />
                                </div>
                                <input
                                    id="foto"
                                    type="file"
                                    @change="handleFotoChange"
                                    accept="image/*"
                                    class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-200 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800 file:cursor-pointer"
                                />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Biarkan kosong jika tidak ingin mengubah foto</p>
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview foto baru:</p>
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full border-2 border-gray-200 dark:border-gray-700" />
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg font-semibold text-sm transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                                    <span v-if="!form.processing">Update</span>
                                    <span v-else>Mengupdate...</span>
                                </button>
                                <Link :href="route('students.index')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold text-sm transition shadow-sm w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
