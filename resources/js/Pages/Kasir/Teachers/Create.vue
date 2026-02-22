<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    nip: '',
    nuptk: '',
    jabatan: '',
    mata_pelajaran: '',
    jenjang: '',
    alamat_lengkap: '',
    balance: 0,
    rfid_uid: '',
    foto: null,
    auto_generate_rfid: true,
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
    form.post(route('kasir.teachers.store'));
};
</script>

<template>
    <Head title="Tambah Guru" />

    <AuthenticatedLayout>
        <template #mobileTitle>Guru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Tambah Guru</h2>
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
                            <h3 class="text-2xl font-bold">Tambah Guru Baru</h3>
                            <p class="text-purple-100/80 text-sm">Daftarkan guru baru ke sistem koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8 text-white">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Informasi Akun -->
                            <div class="border-b border-white/10 pb-4">
                                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                                    Informasi Akun
                                </h4>
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
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                                        <input id="password" type="password" v-model="form.password" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" required />
                                        <InputError :message="form.errors.password" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Data Guru -->
                            <div class="border-b border-white/10 pb-4">
                                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                                    Data Guru
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="nip" class="block text-sm font-medium text-slate-300 mb-1">NIP</label>
                                        <input id="nip" type="text" v-model="form.nip" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Nomor Induk Pegawai" required />
                                        <InputError :message="form.errors.nip" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="nuptk" class="block text-sm font-medium text-slate-300 mb-1">NUPTK (Opsional)</label>
                                        <input id="nuptk" type="text" v-model="form.nuptk" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Nomor Unik Pendidik dan Tenaga Kependidikan" />
                                        <InputError :message="form.errors.nuptk" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="jabatan" class="block text-sm font-medium text-slate-300 mb-1">Jabatan (Opsional)</label>
                                        <input id="jabatan" type="text" v-model="form.jabatan" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Contoh: Guru Tetap, Guru Honorer" />
                                        <InputError :message="form.errors.jabatan" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="mata_pelajaran" class="block text-sm font-medium text-slate-300 mb-1">Mata Pelajaran (Opsional)</label>
                                        <input id="mata_pelajaran" type="text" v-model="form.mata_pelajaran" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Contoh: Matematika, Bahasa Indonesia" />
                                        <InputError :message="form.errors.mata_pelajaran" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="jenjang" class="block text-sm font-medium text-slate-300 mb-1">Jenjang (Opsional)</label>
                                        <select id="jenjang" v-model="form.jenjang" class="w-full bg-slate-900/70 border-slate-600 text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5">
                                            <option value="">Pilih Jenjang</option>
                                            <option value="SMA Taruna Nusantara Indonesia">SMA Taruna Nusantara Indonesia</option>
                                            <option value="SMK Taruna Nusantara Jaya">SMK Taruna Nusantara Jaya</option>
                                        </select>
                                        <InputError :message="form.errors.jenjang" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="alamat_lengkap" class="block text-sm font-medium text-slate-300 mb-1">Alamat Lengkap (Opsional)</label>
                                        <textarea id="alamat_lengkap" v-model="form.alamat_lengkap" rows="3" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Alamat lengkap guru"></textarea>
                                        <InputError :message="form.errors.alamat_lengkap" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Saldo dan RFID -->
                            <div class="border-b border-white/10 pb-4">
                                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                    Saldo & RFID
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="balance" class="block text-sm font-medium text-slate-300 mb-1">Saldo Awal (Rp)</label>
                                        <input id="balance" type="number" v-model="form.balance" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" min="0" step="0.01" />
                                        <InputError :message="form.errors.balance" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="rfid_uid" class="block text-sm font-medium text-slate-300 mb-1">RFID UID (Opsional)</label>
                                        <input id="rfid_uid" type="text" v-model="form.rfid_uid" class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5" placeholder="Kosongkan untuk auto-generate" />
                                        <p class="mt-1 text-sm text-slate-400">RFID UID akan di-generate otomatis jika dikosongkan</p>
                                        <InputError :message="form.errors.rfid_uid" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Foto -->
                            <div>
                                <label for="foto" class="block text-sm font-medium text-slate-300 mb-1">Foto Guru</label>
                                <input id="foto" type="file" @change="handleFotoChange" accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer file:border file:border-indigo-500/30" />
                                <InputError :message="form.errors.foto" class="mt-2" />
                                <div v-if="fotoPreview" class="mt-3">
                                    <img :src="fotoPreview" alt="Preview" class="h-32 w-32 object-cover rounded-full ring-4 ring-indigo-500/30" />
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 pt-4 border-t border-white/10">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/40 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto transform hover:-translate-y-0.5">
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Guru</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('kasir.teachers.index')" class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition-all border border-white/10 w-full sm:w-auto">
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
