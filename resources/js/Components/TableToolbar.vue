<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    title: String,
    description: String,
    searchTerm: {
        type: String,
        default: ''
    },
    searchRoute: {
        type: String,
        required: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Cari...'
    }
});

const search = ref(props.searchTerm);

// Simple debounce implementation
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

// Debounce the search to avoid too many requests
const performSearch = debounce((value) => {
    router.get(route(props.searchRoute), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300);

watch(search, (value) => {
    performSearch(value);
});
</script>

<template>
    <div class="mb-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-sm p-4 transition-colors duration-200">
        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            <div v-if="title || description" class="flex flex-col mr-4">
                 <h3 v-if="title" class="text-lg font-medium text-slate-900 dark:text-gray-100">{{ title }}</h3>
                 <p v-if="description" class="text-sm text-slate-500 dark:text-slate-400 hidden lg:block">{{ description }}</p>
            </div>

            <div class="flex-1 max-w-lg">
                    <div v-if="$slots['search-input']" class="w-full">
                        <slot name="search-input" />
                    </div>
                    <div v-else class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-slate-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-lg shadow-sm text-sm transition-colors duration-200"
                        />
                    </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
