<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { ref, computed, watch } from 'vue';
import { FLAT_UNITS, getUnitsByGroup } from '@/Constants/Units';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    category_id: '',
    name: '',
    description: '',
    image: null,
    harga_beli: 0,
    harga_jual: 0,
    barcode: '',
    unit: 'Pcs', // Default UOM
    netto: '',
});

const selectedMainCategoryId = ref('');

const mainCategories = computed(() => {
    return props.categories.filter(c => !c.parent_id).sort((a, b) => a.name.localeCompare(b.name));
});

const subCategories = computed(() => {
    if (!selectedMainCategoryId.value) return [];
    return props.categories
        .filter(c => c.parent_id === selectedMainCategoryId.value)
        .sort((a, b) => a.name.localeCompare(b.name));
});

// Watch for main category change to clear sub-category
watch(selectedMainCategoryId, (newVal) => {
    form.category_id = '';
});

const availableUnits = computed(() => {
    if (!form.category_id) return FLAT_UNITS;
    
    const selectedCategory = props.categories.find(c => c.id === form.category_id);
    if (selectedCategory && selectedCategory.unit_group) {
        return getUnitsByGroup(selectedCategory.unit_group);
    }
    
    return FLAT_UNITS;
});

// Reset unit only if current unit is not in new list (optional, but good UX)
watch(() => form.category_id, (newVal) => {
    // If we wanted to enforce unit change, we could do it here
    // For now, let's keep the user selection or default unless they change it
});

const imagePreview = ref(null);

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Tambah Produk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Tambah Produk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Tambah Produk</h2>
        </template>

        <div class="min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <!-- Header Card -->
                <div class="mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-20 pattern-grid-lg"></div>
                     <div class="relative z-10 flex items-center gap-6">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold tracking-tight">Produk Baru</h3>
                            <p class="text-indigo-100 text-sm mt-1">Tambahkan item baru ke inventaris koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            
                            <!-- Category Section -->
                            <div class="bg-slate-900/50 rounded-xl p-6 border border-white/5 space-y-6">
                                <h4 class="text-lg font-medium text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                    Kategorisasi
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-slate-300">Kategori Utama</label>
                                        <SearchableSelect
                                            v-model="selectedMainCategoryId"
                                            :options="mainCategories"
                                            label-key="name"
                                            value-key="id"
                                            placeholder="Pilih Kategori Utama"
                                            search-placeholder="Cari kategori..."
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-slate-300">Sub Kategori</label>
                                        <SearchableSelect
                                            v-model="form.category_id"
                                            :options="subCategories"
                                            label-key="name"
                                            value-key="id"
                                            placeholder="Pilih Sub Kategori"
                                            search-placeholder="Cari sub kategori..."
                                            :disabled="!selectedMainCategoryId"
                                        />
                                        <InputError :message="form.errors.category_id" />
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Info -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="name" class="block text-sm font-medium text-slate-300">Nama Produk</label>
                                        <input
                                            id="name"
                                            type="text"
                                            v-model="form.name"
                                            class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                            placeholder="Contoh: Buku Tulis Sinar Dunia"
                                            required
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="barcode" class="block text-sm font-medium text-slate-300">Barcode / SKU</label>
                                        <div class="relative">
                                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            </div>
                                            <input
                                                id="barcode"
                                                type="text"
                                                v-model="form.barcode"
                                                class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-mono"
                                                placeholder="Scan atau ketik kode..."
                                            />
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">Kosongkan untuk auto-generate kode.</p>
                                        <InputError :message="form.errors.barcode" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="description" class="block text-sm font-medium text-slate-300">Deskripsi</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        placeholder="Informasi detail tentang produk..."
                                    ></textarea>
                                    <InputError :message="form.errors.description" />
                                </div>
                            </div>

                            <!-- Pricing & Unit -->
                             <div class="bg-slate-900/50 rounded-xl p-6 border border-white/5 space-y-6">
                                <h4 class="text-lg font-medium text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Harga & Satuan
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="harga_beli" class="block text-sm font-medium text-slate-300">Harga Beli</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-bold">Rp</span>
                                            </div>
                                            <input
                                                id="harga_beli"
                                                type="number"
                                                v-model="form.harga_beli"
                                                class="block w-full pl-12 pr-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-right font-mono"
                                                min="0"
                                                required
                                            />
                                        </div>
                                        <InputError :message="form.errors.harga_beli" />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="harga_jual" class="block text-sm font-medium text-slate-300">Harga Jual</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-bold">Rp</span>
                                            </div>
                                            <input
                                                id="harga_jual"
                                                type="number"
                                                v-model="form.harga_jual"
                                                class="block w-full pl-12 pr-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-right font-mono"
                                                min="0"
                                                required
                                            />
                                        </div>
                                        <InputError :message="form.errors.harga_jual" />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="unit" class="block text-sm font-medium text-slate-300">Satuan (UOM)</label>
                                        <SearchableSelect
                                            v-model="form.unit"
                                            :options="availableUnits"
                                            placeholder="Pilih Satuan"
                                            search-placeholder="Cari..."
                                        />
                                        <InputError :message="form.errors.unit" />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="netto" class="block text-sm font-medium text-slate-300">Netto / Isi (Opsional)</label>
                                        <input
                                            id="netto"
                                            type="text"
                                            v-model="form.netto"
                                            class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                            placeholder="Contoh: 500gr / 1 Liter"
                                        />
                                        <InputError :message="form.errors.netto" />
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-300">Gambar Produk</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-600 border-dashed rounded-xl hover:bg-slate-800/50 transition-colors">
                                    <div class="space-y-1 text-center">
                                         <div v-if="!imagePreview" class="flex flex-col items-center">
                                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-slate-400">
                                                <label for="image" class="relative cursor-pointer bg-transparent rounded-md font-medium text-indigo-400 hover:text-indigo-300 focus-within:outline-none">
                                                    <span>Upload gambar</span>
                                                    <input id="image" type="file" @change="handleImageChange" accept="image/*" class="sr-only" />
                                                </label>
                                                <p class="pl-1">atau drag and drop</p>
                                            </div>
                                            <p class="text-xs text-slate-500">PNG, JPG, GIF up to 5MB</p>
                                        </div>
                                        <div v-else class="relative">
                                            <img :src="imagePreview" alt="Preview" class="mx-auto h-48 object-cover rounded-lg border border-slate-600 shadow-md" />
                                            <button 
                                                @click.prevent="imagePreview = null; form.image = null;"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 shadow-sm"
                                            >
                                                <svg class="min-w-4 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <InputError :message="form.errors.image" />
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                                <Link
                                    :href="route('products.index')"
                                    class="px-5 py-2.5 rounded-lg border border-white/10 bg-slate-700 text-slate-200 hover:bg-slate-600 hover:text-white font-medium transition-all"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-6 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-else>Simpan Produk</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
