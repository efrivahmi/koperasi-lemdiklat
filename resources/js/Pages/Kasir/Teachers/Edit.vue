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
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Data Guru</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-600/80 via-indigo-600/80 to-blue-600/80 backdrop-blur-md rounded-xl shadow-2xl p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md p-3 rounded-xl border border-white/20">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">Edit Data Guru</h3>
                            <p class="text-purple-100/80 text-sm">Perbarui informasi guru koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8 text-white">
                        <!-- Current Photo -->
                        <div v-if="teacher.foto && !fotoPreview" class="mb-6">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Foto Saat Ini</label>
                            <img :src="`/storage/${teacher.foto}`" :alt="teacher.user.name" class="h-32 w-32 object-cover rounded-full ring-4 ring-indigo-500/30" />
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                                    <input type="text" v-model="form.name" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                                    <input type="email" v-model="form.email" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Password (Kosongkan jika tidak diubah)</label>
                                    <input type="password" v-model="form.password" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">NIP</label>
                                    <input type="text" v-model="form.nip" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                    <InputError :message="form.errors.nip" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">NUPTK</label>
                                    <input type="text" v-model="form.nuptk" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                    <InputError :message="form.errors.nuptk" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Jabatan</label>
                                    <input type="text" v-model="form.jabatan" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                    <InputError :message="form.errors.jabatan" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Mata Pelajaran</label>
                                    <input type="text" v-model="form.mata_pelajaran" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                    <InputError :message="form.errors.mata_pelajaran" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Jenjang</label>
                                    <select v-model="form.jenjang" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5">
                                        <option value="">Pilih Jenjang</option>
                                        <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                        <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                    </select>
                                    <InputError :message="form.errors.jenjang" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Saldo (Rp)</label>
                                    <input type="number" v-model="form.balance" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" min="0" step="0.01" />
                                    <InputError :message="form.errors.balance" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">RFID UID</label>
                                    <input type="text" v-model="form.rfid_uid" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                    <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Alamat Lengkap</label>
                                <textarea v-model="form.alamat_lengkap" rows="3" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5"></textarea>
                                <InputError :message="form.errors.alamat_lengkap" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Foto Guru (Biarkan kosong jika tidak diubah)</label>
                                <input type="file" @change="handleFotoChange" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer file:border file:border-indigo-500/30" />
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full ring-4 ring-indigo-500/30" />
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 pt-4 border-t border-white/10">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/40 disabled:opacity-50 w-full sm:w-auto transform hover:-translate-y-0.5">
                                    <span v-if="!form.processing">Update Guru</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('kasir.teachers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition-all border border-white/10 w-full sm:w-auto">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
