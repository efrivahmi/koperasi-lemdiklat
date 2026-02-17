<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuditInfo from '@/Components/AuditInfo.vue';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({
    products: {
        type: Array,
        required: true,
        default: () => []
    },
    itemsPerPage: {
        type: Number,
        default: 10
    }
});

const { can } = usePermissions();
const currentPage = ref(1);

const totalPages = computed(() => {
    return Math.ceil(props.products.length / props.itemsPerPage);
});

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * props.itemsPerPage;
    const end = start + props.itemsPerPage;
    return props.products.slice(start, end);
});

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
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
    <div>
        <div v-if="paginatedProducts.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-indigo-300 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-indigo-300 uppercase tracking-wider hidden xl:table-cell">Dibuat</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-indigo-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 bg-transparent">
                    <tr v-for="product in paginatedProducts" :key="product.id" class="group hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-lg bg-slate-700 flex-shrink-0 border border-white/10 overflow-hidden">
                                    <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name" class="h-full w-full object-cover" />
                                    <div v-else class="h-full w-full flex items-center justify-center text-slate-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white group-hover:text-indigo-200 transition-colors">{{ product.name }}</div>
                                    <div class="text-xs text-slate-500 font-mono mt-0.5">{{ product.barcode }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-emerald-400">{{ formatCurrency(product.harga_jual) }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Beli: {{ formatCurrency(product.harga_beli) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full border" :class="[product.stock === 0 ? 'bg-rose-900/30 text-rose-300 border-rose-500/20' : product.stock <= 5 ? 'bg-amber-900/30 text-amber-300 border-amber-500/20' : 'bg-emerald-900/30 text-emerald-300 border-emerald-500/20']">
                                {{ product.stock }} unit
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 hidden xl:table-cell">
                            <AuditInfo :user="product.creator" :timestamp="product.created_at" label="Dibuat" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <Link :href="route('products.show', product.id)" class="text-sky-400 hover:text-sky-300 transition-colors" title="Lihat">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </Link>
                                <Link v-if="can && can('products.edit')" :href="route('products.edit', product.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Simple Pagination Controls -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-400">
                            Menampilkan
                            <span class="font-medium text-white">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                            sampai
                            <span class="font-medium text-white">{{ Math.min(currentPage * itemsPerPage, products.length) }}</span>
                            dari
                            <span class="font-medium text-white">{{ products.length }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button
                                @click="changePage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-white/10 bg-slate-800 text-sm font-medium text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <!-- Page Numbers (Simplified) -->
                            <button
                                v-for="page in totalPages"
                                :key="page"
                                @click="changePage(page)"
                                class="relative inline-flex items-center px-4 py-2 border border-white/10 text-sm font-medium"
                                :class="currentPage === page ? 'z-10 bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'"
                            >
                                {{ page }}
                            </button>

                            <button
                                @click="changePage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-white/10 bg-slate-800 text-sm font-medium text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
                <!-- Mobile Pagination -->
                 <div class="flex items-center justify-between sm:hidden w-full">
                    <button
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-white/10 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700"
                    >
                        Previous
                    </button>
                    <span class="text-sm text-slate-400">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center px-4 py-2 border border-white/10 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700"
                    >
                        Next
                    </button>
                 </div>
            </div>
        </div>
        
        <div v-else class="text-center py-8">
            <p class="text-sm text-slate-400">Tidak ada produk dalam daftar ini.</p>
        </div>
    </div>
</template>
