<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({});

const form = useForm({
    product_id: '',
    quantity: '',
    supplier: '',
    keterangan: '',
});

const submit = () => {
    form.post(route('stock-ins.store'));
};
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <template #mobileTitle>Stok Masuk</template>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Barang Masuk</h2>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-white">
                <!-- Header Card -->
                <div class="mb-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-20 pattern-grid-lg"></div>
                    <div class="relative z-10 flex items-center gap-6">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-3xl font-bold tracking-tight">Tambah Stok Masuk</h3>
                            <p class="text-purple-100 text-sm mt-1">Catat barang masuk ke inventori koperasi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-slate-300 mb-2">Produk</label>
                                <SearchableSelect
                                    v-model="form.product_id"
                                    :api-url="route('api.products.search')"
                                    placeholder="Cari Produk..."
                                    search-placeholder="Ketik nama atau barcode..."
                                    class="w-full"
                                />
                                <InputError :message="form.errors.product_id" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-slate-300 mb-2">Jumlah / Quantity</label>
                                    <div class="relative">
                                        <input
                                            id="quantity"
                                            type="number"
                                            v-model="form.quantity"
                                            class="block w-full pl-4 pr-12 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                            min="1"
                                            required
                                            placeholder="0"
                                        />
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-slate-500 sm:text-sm">Unit</span>
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.quantity" class="mt-2" />
                                </div>

                                <div>
                                    <label for="supplier" class="block text-sm font-medium text-slate-300 mb-2">Supplier / Pemasok</label>
                                    <input
                                        id="supplier"
                                        type="text"
                                        v-model="form.supplier"
                                        class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        placeholder="Nama supplier (opsional)"
                                    />
                                    <InputError :message="form.errors.supplier" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-slate-300 mb-2">Keterangan</label>
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                    rows="3"
                                    placeholder="Catatan tambahan (opsional)"
                                ></textarea>
                                <InputError :message="form.errors.keterangan" class="mt-2" />
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                                <button type="submit" :disabled="form.processing" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span v-if="!form.processing">Simpan Stok Masuk</span>
                                    <span v-else>Menyimpan...</span>
                                </button>
                                <Link :href="route('stock-ins.index')" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl border border-white/10 transition-all duration-200">
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
    </AuthenticatedLayout>
</template>
