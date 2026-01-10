<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    teacher: Object,
    transactions: Object,
    filters: Object,
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head title="Riwayat Transaksi" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold">Riwayat Transaksi</h2></template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <div v-for="tx in transactions.data" :key="tx.id" class="flex justify-between py-3 border-b">
                        <div>
                            <p class="font-medium">{{ tx.type }}</p>
                            <p class="text-sm text-gray-500">{{ formatDate(tx.created_at) }}</p>
                        </div>
                        <p class="font-bold">{{ formatCurrency(tx.amount) }}</p>
                    </div>
                    <p v-if="transactions.data.length === 0" class="text-center text-gray-400 py-8">Tidak ada transaksi</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
