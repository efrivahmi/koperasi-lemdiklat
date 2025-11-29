<script setup>
import { computed } from 'vue';

const props = defineProps({
    headers: {
        type: Array,
        required: true,
        // Format: [{ key: 'name', label: 'Nama', class: 'text-left' }]
    },
    items: {
        type: Array,
        required: true
    },
    mobileCardView: {
        type: Boolean,
        default: false // If true, shows card layout on mobile instead of scrollable table
    },
    stickyHeader: {
        type: Boolean,
        default: true
    },
    emptyMessage: {
        type: String,
        default: 'Tidak ada data'
    }
});

const slots = defineSlots();
</script>

<template>
    <!-- Desktop/Tablet: Traditional Table with Horizontal Scroll -->
    <div
        v-if="!mobileCardView"
        class="overflow-x-auto -mx-3 sm:mx-0 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
    >
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
                <thead :class="[
                    'bg-gray-50',
                    stickyHeader ? 'sticky top-0 z-10' : ''
                ]">
                    <tr>
                        <th
                            v-for="header in headers"
                            :key="header.key"
                            :class="[
                                'px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-xs font-medium text-gray-500 uppercase tracking-wider',
                                header.class || 'text-left'
                            ]"
                        >
                            {{ header.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template v-if="items.length > 0">
                        <slot name="body" :items="items" />
                    </template>
                    <tr v-else>
                        <td :colspan="headers.length" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm">{{ emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile: Card Layout (Optional) -->
    <div v-else class="space-y-3 sm:space-y-4">
        <template v-if="items.length > 0">
            <slot name="mobile-card" :items="items" />
        </template>
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mb-3 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-sm text-gray-500">{{ emptyMessage }}</p>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
.scrollbar-thin::-webkit-scrollbar {
    height: 8px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
    background-color: #d1d5db;
    border-radius: 4px;
}

.scrollbar-thumb-gray-300::-webkit-scrollbar-thumb:hover {
    background-color: #9ca3af;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
    background-color: #f3f4f6;
    border-radius: 4px;
}

/* Ensure table cells are responsive */
table {
    font-size: 0.875rem; /* text-sm */
}

@media (min-width: 640px) {
    table {
        font-size: 0.9375rem; /* slightly larger on tablets */
    }
}

@media (min-width: 1024px) {
    table {
        font-size: 1rem; /* text-base on desktop */
    }
}
</style>
