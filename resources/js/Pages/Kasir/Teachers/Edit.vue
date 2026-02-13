<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    teacher: Object,
});

const form = useForm({
    name: props.teacher.user.name,
    email: props.teacher.user.email,
    password: '',
    nip: props.teacher.nip,
    nuptk: props.teacher.nuptk || '',
    jabatan: props.teacher.jabatan || '',
    mata_pelajaran: props.teacher.mata_pelajaran || '',
    jenjang: props.teacher.jenjang || '',
    alamat_lengkap: props.teacher.alamat_lengkap || '',
    balance: props.teacher.balance,
    rfid_uid: props.teacher.rfid_uid || '',
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
    form.post(route('kasir.teachers.update', props.teacher.id), {
        _method: 'put',
    });
};
</script>

<template>
    <Head title="Edit Guru" />

    <AuthenticatedLayout>
        <template #mobileTitle>Edit Guru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Data Guru</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold">Edit Data Guru</h3>
                            <p class="text-purple-100 text-sm">Perbarui informasi guru koperasi</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-lg border-2 border-purple-200 dark:border-purple-500/30">
                    <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                        <!-- Current Photo -->
                        <div v-if="teacher.foto && !fotoPreview" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Saat Ini</label>
                            <img :src="`/storage/${teacher.foto}`" :alt="teacher.user.name" class="h-32 w-32 object-cover rounded-full border-2 border-gray-200 dark:border-gray-700" />
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                                    <input type="text" v-model="form.name" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" required />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input type="email" v-model="form.email" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" required />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password (Kosongkan jika tidak diubah)</label>
                                    <input type="password" v-model="form.password" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIP</label>
                                    <input type="text" v-model="form.nip" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" required />
                                    <InputError :message="form.errors.nip" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NUPTK</label>
                                    <input type="text" v-model="form.nuptk" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" />
                                    <InputError :message="form.errors.nuptk" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                                    <input type="text" v-model="form.jabatan" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" />
                                    <InputError :message="form.errors.jabatan" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                                    <input type="text" v-model="form.mata_pelajaran" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" />
                                    <InputError :message="form.errors.mata_pelajaran" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenjang</label>
                                    <select v-model="form.jenjang" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2">
                                        <option value="">Pilih Jenjang</option>
                                        <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                        <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                    </select>
                                    <InputError :message="form.errors.jenjang" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Saldo (Rp)</label>
                                    <input type="number" v-model="form.balance" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" min="0" step="0.01" />
                                    <InputError :message="form.errors.balance" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RFID UID</label>
                                    <input type="text" v-model="form.rfid_uid" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2" />
                                    <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Lengkap</label>
                                <textarea v-model="form.alamat_lengkap" rows="3" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm py-2"></textarea>
                                <InputError :message="form.errors.alamat_lengkap" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Guru (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" @change="handleFotoChange" accept="image/*" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-200 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800 file:cursor-pointer" />
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full border-2 border-gray-200 dark:border-gray-700" />
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-bold shadow-lg disabled:opacity-50 w-full sm:w-auto">
                                    <span v-if="!form.processing">Update Guru</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('teachers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold shadow-sm w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
