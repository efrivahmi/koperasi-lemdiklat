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
    form.post(route('kasir.students.rfid.store', props.student.id));
};
</script>

<template>
    <Head title="Daftarkan Kartu RFID" />

    <AuthenticatedLayout>
        <template #mobileTitle>RFID</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Daftarkan Kartu RFID</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-2xl rounded-xl border border-white/10">
                    <div class="p-6 sm:p-8 text-white">
                        <!-- Student Info -->
                        <div class="mb-6 p-4 bg-slate-900/50 rounded-xl border border-white/5">
                            <h3 class="text-lg font-semibold mb-3 text-indigo-300">Informasi Siswa</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider">NISN</p>
                                    <p class="font-medium text-white mt-1">{{ student.nis }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider">Nama</p>
                                    <p class="font-medium text-white mt-1">{{ student.user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider">Kelas</p>
                                    <p class="font-medium text-white mt-1">{{ student.kelas }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider">Email</p>
                                    <p class="font-medium text-white mt-1">{{ student.user.email }}</p>
                                </div>
                            </div>
                            <div v-if="student.rfid_uid" class="mt-4 pt-3 border-t border-white/5">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">RFID UID Saat Ini</p>
                                <p class="font-medium text-emerald-300 mt-1">{{ student.rfid_uid }}</p>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6 p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl">
                            <h4 class="font-semibold text-indigo-300 mb-2">Instruksi Pendaftaran RFID:</h4>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-indigo-200/80">
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
                                <label for="rfid_uid" class="block text-sm font-medium text-slate-300 mb-1">RFID UID</label>
                                <input
                                    id="rfid_uid"
                                    type="text"
                                    v-model="form.rfid_uid"
                                    class="w-full bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm py-2.5"
                                    required
                                    autofocus
                                    placeholder="Tempelkan kartu RFID pada scanner..."
                                />
                                <p class="mt-1 text-sm text-slate-400">UID akan otomatis terisi saat kartu ditempelkan pada scanner</p>
                                <InputError :message="form.errors.rfid_uid" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                                <button type="submit" :disabled="form.processing || !form.rfid_uid" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg font-bold text-sm transition-all duration-200 shadow-lg shadow-purple-500/20 hover:shadow-purple-500/40 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5">
                                    {{ student.rfid_uid ? 'Update RFID' : 'Daftarkan RFID' }}
                                </button>
                                <Link :href="route('kasir.students.index')" class="text-slate-400 hover:text-slate-200 transition-colors text-sm">Batal</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
