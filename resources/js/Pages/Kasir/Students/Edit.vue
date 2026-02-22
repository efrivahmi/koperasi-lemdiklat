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
    form.post(route('kasir.students.update', props.student.id), {
        _method: 'put',
    });
};
</script>

<template>
    <Head title="Edit Siswa" />

    <AuthenticatedLayout>
        <template #mobileTitle>Edit Siswa</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Siswa</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="mb-6 bg-gradient-to-r from-purple-600/80 via-indigo-600/80 to-blue-600/80 backdrop-blur-md rounded-xl shadow-2xl p-6 text-white border border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md p-3 rounded-xl border border-white/20">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">Edit Data Siswa</h3>
                            <p class="text-purple-100/80 text-sm">Perbarui informasi siswa koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8 text-white">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
                                    <input id="name" type="text" v-model="form.name" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required autofocus />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                                    <input id="email" type="email" v-model="form.email" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                    <InputError :message="form.errors.email" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password Baru (Opsional)</label>
                                <input id="password" type="password" v-model="form.password" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" />
                                <p class="mt-1 text-sm text-slate-400">Kosongkan jika tidak ingin mengubah password</p>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nis" class="block text-sm font-medium text-slate-300 mb-1">NISN</label>
                                    <input id="nis" type="text" v-model="form.nis" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Nomor Induk Siswa Nasional" required />
                                    <InputError :message="form.errors.nis" class="mt-2" />
                                </div>
                                <div>
                                    <label for="kelas" class="block text-sm font-medium text-slate-300 mb-1">Kelas</label>
                                    <input id="kelas" type="text" v-model="form.kelas" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                    <InputError :message="form.errors.kelas" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="jenjang" class="block text-sm font-medium text-slate-300 mb-1">Jenjang</label>
                                    <select id="jenjang" v-model="form.jenjang" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required>
                                        <option value="" disabled>Pilih Jenjang</option>
                                        <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                        <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                    </select>
                                    <InputError :message="form.errors.jenjang" class="mt-2" />
                                </div>
                                <div>
                                    <label for="balance" class="block text-sm font-medium text-slate-300 mb-1">Saldo (Rp)</label>
                                    <input id="balance" type="number" v-model="form.balance" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" min="0" step="0.01" />
                                    <InputError :message="form.errors.balance" class="mt-2" />
                                </div>
                                <div>
                                    <label for="rfid_uid" class="block text-sm font-medium text-slate-300 mb-1">RFID UID</label>
                                    <input id="rfid_uid" type="text" v-model="form.rfid_uid" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5 disabled:opacity-50" :disabled="!!student.rfid_uid" />
                                    <p v-if="student.rfid_uid" class="mt-1 text-sm text-slate-400">Gunakan halaman "RFID Register" untuk mengubah RFID</p>
                                    <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label for="foto" class="block text-sm font-medium text-slate-300 mb-1">Foto Siswa</label>
                                <div v-if="student.foto && !fotoPreview" class="mb-3">
                                    <p class="text-sm text-slate-400 mb-2">Foto saat ini:</p>
                                    <img :src="`/storage/${student.foto}`" :alt="student.user.name" class="h-32 w-32 object-cover rounded-full ring-4 ring-indigo-500/30" />
                                </div>
                                <input id="foto" type="file" @change="handleFotoChange" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer file:border file:border-indigo-500/30" />
                                <p class="mt-1 text-sm text-slate-400">Biarkan kosong jika tidak ingin mengubah foto</p>
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <p class="text-sm text-slate-400 mb-2">Preview foto baru:</p>
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full ring-4 ring-indigo-500/30" />
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 pt-4 border-t border-white/10">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/40 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto transform hover:-translate-y-0.5">
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Perubahan</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('kasir.students.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition-all border border-white/10 w-full sm:w-auto">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
