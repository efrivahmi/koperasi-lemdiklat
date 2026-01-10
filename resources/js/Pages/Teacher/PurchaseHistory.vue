<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ teacher: Object, purchases: Object, filters: Object });
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Riwayat Belanja" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold">Riwayat Belanja</h2></template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <div v-for="sale in purchases.data" :key="sale.id" class="border-b py-3">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ formatDate(sale.created_at) }}</p>
                                <p class="text-sm text-gray-500">{{ sale.payment_method }}</p>
                            </div>
                            <p class="font-bold">{{ formatCurrency(sale.total) }}</p>
                        </div>
                    </div>
                    <p v-if="purchases.data.length === 0" class="text-center text-gray-400 py-8">Belum ada pembelian</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
