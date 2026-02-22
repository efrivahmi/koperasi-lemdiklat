<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

const { can } = usePermissions();

const props = defineProps({
    students: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedStudents = ref(new Set());
const selectAll = ref(false);

watch(search, (value) => {
    router.get(route('students.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const deleteStudent = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus siswa ini?')) {
        router.delete(route('students.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        props.students.data.forEach(student => {
            selectedStudents.value.add(student.id);
        });
    } else {
        selectedStudents.value.clear();
    }
};

const toggleStudent = (studentId) => {
    if (selectedStudents.value.has(studentId)) {
        selectedStudents.value.delete(studentId);
    } else {
        selectedStudents.value.add(studentId);
    }
    selectAll.value = selectedStudents.value.size === props.students.data.length;
};

const isSelected = (studentId) => {
    return selectedStudents.value.has(studentId);
};

const printCard = (studentId) => {
    window.open(route('students.card.generate', studentId), '_blank');
};

const printBatchCards = () => {
    if (selectedStudents.value.size === 0) {
        alert('Pilih minimal 1 siswa untuk mencetak kartu');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('students.cards.batch');
    form.target = '_blank';

    // CSRF Token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.appendChild(csrfInput);

    // Student IDs
    selectedStudents.value.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'student_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};
</script>

<template>
    <Head title="Data Siswa" />

    <AuthenticatedLayout>
        <template #mobileTitle>Siswa</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Data Siswa</h2>
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
                                    placeholder="Cari siswa (NIS, NISN, nama, kelas, jurusan, email)..."
                                    class="block w-full pl-10 pr-3 py-2.5 bg-slate-900/70 border-slate-600 text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm transition-all"
                                />
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                             <button 
                                v-if="can('students.cards') && selectedStudents.size > 0"
                                @click="printBatchCards"
                                class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 text-white font-semibold rounded-lg shadow-lg shadow-teal-500/20 transition-all"
                            >
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                <span class="hidden sm:inline">Cetak Kartu ({{ selectedStudents.size }})</span>
                                <span class="sm:hidden">Cetak ({{ selectedStudents.size }})</span>
                            </button>

                            <Link v-if="can('students.create')" :href="route('students.create')" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-200 transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden sm:inline">Tambah Siswa</span>
                                <span class="sm:hidden">Siswa</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="students.data.length === 0 && !search"
                    icon="users"
                    title="Belum Ada Siswa"
                    description="Tambahkan siswa pertama untuk mulai mengelola data anggota koperasi. Siswa yang terdaftar dapat melakukan transaksi menggunakan RFID atau manual."
                    :action-url="route('students.create')"
                    action-text="Tambah Siswa Pertama"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="bg-slate-800/40 backdrop-blur-md border border-white/10 rounded-xl shadow-xl overflow-hidden">
                    <div class="p-4 sm:p-6 text-white">
                        <div class="mb-4 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    :checked="selectAll"
                                    @change="selectAll = !selectAll; toggleSelectAll()"
                                    class="rounded border-slate-600 bg-slate-900/50 text-indigo-500 shadow-sm focus:border-indigo-400 focus:ring focus:ring-indigo-500/30"
                                >
                                <span class="text-sm text-slate-400">Pilih Semua</span>
                            </div>
                            <span class="text-xs text-slate-400">
                                Menampilkan {{ students.from }}-{{ students.to }} dari {{ students.total }} siswa
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <span class="sr-only">Select</span>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Foto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">NIS</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Kelas</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Saldo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">RFID</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Dibuat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Diubah</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="student in students.data" :key="student.id" :class="{ 'bg-indigo-500/10': isSelected(student.id), 'hover:bg-white/5': !isSelected(student.id) }" class="transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <input
                                                :checked="isSelected(student.id)"
                                                @change="toggleStudent(student.id)"
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-slate-600 bg-slate-900/50 text-indigo-500 focus:ring-indigo-500/30"
                                            />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <img
                                                v-if="student.foto"
                                                :src="`/storage/${student.foto}`"
                                                :alt="student.user.name"
                                                class="h-10 w-10 object-cover rounded-full ring-2 ring-white/20"
                                            />
                                            <div v-else class="h-10 w-10 bg-slate-700 rounded-full flex items-center justify-center ring-2 ring-white/10">
                                                <span class="text-slate-400 text-xs">No</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-200 font-mono">{{ student.nis }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-white">{{ student.user.name }}</div>
                                            <div class="text-xs text-slate-400">{{ student.user.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-300">{{ student.kelas }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-emerald-300 font-semibold">{{ formatCurrency(student.balance) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <span v-if="student.rfid_uid" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Terdaftar
                                            </span>
                                            <span v-else class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Belum
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">
                                            <AuditInfo :user="student.creator" :timestamp="student.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">
                                            <AuditInfo :user="student.updater" :timestamp="student.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <Link v-if="can('students.show')" :href="route('students.show', student.id)" class="text-sky-400 hover:text-sky-300 transition-colors">Detail</Link>
                                                <button v-if="can('students.view')" @click="printCard(student.id)" class="text-purple-400 hover:text-purple-300 transition-colors" title="Cetak Kartu">🖨️</button>
                                                <Link v-if="can('students.edit')" :href="route('students.edit', student.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</Link>
                                                <Link v-if="can('students.edit')" :href="route('students.rfid.register', student.id)" class="text-emerald-400 hover:text-emerald-300 transition-colors">RFID</Link>
                                                <button v-if="can('students.delete')" @click="deleteStudent(student.id)" class="text-rose-400 hover:text-rose-300 transition-colors">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="students.data.length === 0 && search">
                                        <td colspan="10" class="px-6 py-12 text-center">
                                            <div class="text-slate-400">
                                                <svg class="mx-auto h-12 w-12 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-slate-200 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-slate-400">Tidak ditemukan siswa dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 px-2">
                            <p class="text-sm text-slate-400">
                                Menampilkan <span class="font-semibold text-slate-200">{{ students.from || 0 }}</span> - <span class="font-semibold text-slate-200">{{ students.to || 0 }}</span> dari <span class="font-semibold text-slate-200">{{ students.total }}</span> siswa
                            </p>
                            <div v-if="students.links.length > 3" class="flex flex-wrap justify-center gap-1">
                                <template v-for="link in students.links" :key="link.label">
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
