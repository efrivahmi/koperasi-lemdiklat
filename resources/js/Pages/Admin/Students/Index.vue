<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
    <Head title="Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Siswa</h2>
                <div class="flex gap-2">
                    <button
                        v-if="selectedStudents.size > 0"
                        @click="printBatchCards"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2"
                    >
                        🖨️ Cetak {{ selectedStudents.size }} Kartu
                    </button>
                    <Link :href="route('students.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition">
                        Tambah Siswa
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Empty State -->
                <EmptyState
                    v-if="students.data.length === 0 && !search"
                    icon="users"
                    title="Belum Ada Siswa"
                    description="Tambahkan siswa pertama untuk mulai mengelola data siswa koperasi. Siswa yang terdaftar dapat melakukan transaksi menggunakan RFID atau manual."
                    :action-url="route('students.create')"
                    action-text="Tambah Siswa Pertama"
                />

                <!-- Table with Data or Search Results -->
                <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Search Bar -->
                        <div class="mb-4">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari siswa (NISN, nama, email, kelas)..."
                                class="w-full md:w-1/2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <input
                                                v-model="selectAll"
                                                @change="toggleSelectAll"
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                            />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Foto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">NISN</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kelas</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Saldo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">RFID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider hidden xl:table-cell">Diubah</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="student in students.data" :key="student.id" :class="{ 'bg-purple-50 dark:bg-purple-900/20': isSelected(student.id), 'hover:bg-gray-50 dark:hover:bg-gray-700': !isSelected(student.id) }">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input
                                                :checked="isSelected(student.id)"
                                                @change="toggleStudent(student.id)"
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img
                                                v-if="student.foto"
                                                :src="`/storage/${student.foto}`"
                                                :alt="student.user.name"
                                                class="h-12 w-12 object-cover rounded-full"
                                            />
                                            <div v-else class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                <span class="text-gray-400 dark:text-gray-500 text-xs">No</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ student.nis }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ student.user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ student.user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ student.kelas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ formatCurrency(student.balance) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span v-if="student.rfid_uid" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                Terdaftar
                                            </span>
                                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                Belum
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="student.creator" :timestamp="student.created_at" label="Dibuat" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 hidden xl:table-cell">
                                            <AuditInfo :user="student.updater" :timestamp="student.updated_at" label="Diubah" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="printCard(student.id)" class="text-purple-600 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300 mr-3">🖨️ Kartu</button>
                                            <Link :href="route('students.edit', student.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</Link>
                                            <Link v-if="!student.rfid_uid" :href="route('students.rfid.register', student.id)" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 mr-3">RFID</Link>
                                            <button @click="deleteStudent(student.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="students.data.length === 0 && search">
                                        <td colspan="11" class="px-6 py-12 text-center">
                                            <div class="text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Tidak ada hasil</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ditemukan siswa dengan kata kunci "{{ search }}"</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="students.links.length > 3" class="mt-4 flex justify-center space-x-2">
                            <template v-for="link in students.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 rounded',
                                        link.active ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    :class="'px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-400 opacity-50 cursor-not-allowed'"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
