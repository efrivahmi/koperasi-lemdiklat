<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    teachers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('kasir.teachers.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteTeacher = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus guru ini?')) {
        router.delete(route('kasir.teachers.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Data Guru" />

    <AuthenticatedLayout>
        <template #mobileTitle>Guru</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Data Guru</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Toolbar Section -->
                <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-xl shadow-2xl p-4">
                    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                        <!-- Search Bar -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari guru (NIP, NUPTK, nama, email, jabatan, mata pelajaran)..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm transition-all"
                                />
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <Link v-if="can('teachers.create')" :href="route('kasir.teachers.create')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Tambah Guru</span>
                                <span class="sm:hidden">Guru</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="teachers.data.length === 0 && !search"
                    icon="users"
                    title="Belum Ada Guru"
                    description="Tambahkan guru pertama untuk mulai mengelola data guru koperasi. Guru yang terdaftar dapat melakukan transaksi menggunakan RFID atau manual."
                    :action-url="route('kasir.teachers.create')"
                    action-text="Tambah Guru Pertama"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-xl shadow-xl overflow-hidden">
                    <div class="p-4 sm:p-6 text-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Foto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">NIP</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Jabatan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">RFID</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Diubah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <img
                                                v-if="teacher.foto"
                                                :src="`/storage/${teacher.foto}`"
                                                :alt="teacher.user.name"
                                                class="h-10 w-10 object-cover rounded-full ring-2 ring-white/20"
                                            />
                                            <div v-else class="h-10 w-10 bg-slate-700 rounded-full flex items-center justify-center ring-2 ring-white/10">
                                                <span class="text-slate-400 text-xs">No</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-200 font-mono">{{ teacher.nip }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-white">{{ teacher.user.name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-400">{{ teacher.user.email }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ teacher.jabatan || '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ teacher.mata_pelajaran || '-' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-emerald-300 font-semibold">{{ formatCurrency(teacher.balance) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <span v-if="teacher.rfid_uid" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Terdaftar
                                            </span>
                                            <span v-else class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Belum
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">
                                            <AuditInfo :user="teacher.creator" :timestamp="teacher.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">
                                            <AuditInfo :user="teacher.updater" :timestamp="teacher.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <Link v-if="can('teachers.view')" :href="route('kasir.teachers.show', teacher.id)" class="text-sky-400 hover:text-sky-300 transition-colors">Detail</Link>
                                                <Link v-if="can('teachers.edit')" :href="route('kasir.teachers.edit', teacher.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</Link>
                                                <Link v-if="can('teachers.edit')" :href="route('kasir.teachers.rfid.register', teacher.id)" class="text-emerald-400 hover:text-emerald-300 transition-colors">RFID</Link>
                                                <button v-if="can('teachers.delete')" @click="deleteTeacher(teacher.id)" class="text-rose-400 hover:text-rose-300 transition-colors">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="teachers.data.length === 0 && search">
                                        <td colspan="11" class="px-6 py-12 text-center">
                                            <div class="text-slate-400">
                                                <svg class="mx-auto h-12 w-12 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-slate-200 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-slate-400">Tidak ditemukan guru dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                            <p class="text-sm text-slate-400">
                                Menampilkan <span class="font-semibold text-slate-200">{{ teachers.from || 0 }}</span> - <span class="font-semibold text-slate-200">{{ teachers.to || 0 }}</span> dari <span class="font-semibold text-slate-200">{{ teachers.total }}</span> guru
                            </p>
                            <div v-if="teachers.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                <template v-for="link in teachers.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'px-3 py-2 rounded-lg text-sm transition-all',
                                            link.active ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20' : 'bg-slate-700/50 border border-white/10 hover:bg-slate-700 text-slate-300'
                                        ]"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        :class="'px-3 py-2 rounded-lg text-sm bg-slate-800/50 text-slate-500 opacity-50 cursor-not-allowed'"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
