<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: [String, Number, Object],
    options: {
        type: Array,
        default: () => []
    },
    apiUrl: {
        type: String,
        default: null
    },
    labelKey: {
        type: String,
        default: 'name'
    },
    valueKey: {
        type: String,
        default: 'id'
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    enableLazyLoad: {
        type: Boolean,
        default: false
    },
    hasNextPage: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'search', 'loadMore', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const internalOptions = ref([]);
const internalLoading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const internalHasNextPage = ref(false);
const triggerRef = ref(null);
const observer = ref(null);
const sentinel = ref(null);

// Initialize internal options with props.options if provided (useful for edit mode with initial value)
onMounted(() => {
    if (props.options && props.options.length > 0) {
        internalOptions.value = [...props.options];
    }
    document.addEventListener('click', handleClickOutside);
});

const activeOptions = computed(() => {
    return props.apiUrl ? internalOptions.value : props.options;
});

const isLoading = computed(() => {
    return props.loading || internalLoading.value;
});

const selectedOption = computed(() => {
    if (!props.modelValue) return null;
    return activeOptions.value.find(opt => {
        const optValue = typeof opt === 'object' ? opt[props.valueKey] : opt;
        return optValue == props.modelValue;
    });
});

const displayValue = computed(() => {
    if (selectedOption.value) {
        return typeof selectedOption.value === 'object' 
            ? selectedOption.value[props.labelKey] 
            : selectedOption.value;
    }
    // If modelValue is set but not found in options (e.g. initial load), try to show it if provided in options
    // This is a common issue with async components.
    // For now, return placeholder or try to find in initial options if passed separately.
    return '';
});

const filteredOptions = computed(() => {
    if (props.apiUrl || props.enableLazyLoad) return activeOptions.value;
    
    if (!searchQuery.value) return props.options;
    
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt => {
        const label = typeof opt === 'object' ? opt[props.labelKey] : String(opt);
        return label.toLowerCase().includes(query);
    });
});

const fetchOptions = async (page = 1, search = '') => {
    if (!props.apiUrl) return;
    
    internalLoading.value = true;
    try {
        const response = await axios.get(props.apiUrl, {
            params: {
                page: page,
                search: search
            }
        });
        
        const data = response.data; // Assumes Laravel paginate structure or resource collection
        const newOptions = data.data || data;
        
        if (page === 1) {
            internalOptions.value = newOptions;
        } else {
            internalOptions.value = [...internalOptions.value, ...newOptions];
        }
        
        currentPage.value = data.current_page || page;
        lastPage.value = data.last_page || 1;
        internalHasNextPage.value = currentPage.value < lastPage.value;
        
    } catch (error) {
        console.error("Error fetching options:", error);
    } finally {
        internalLoading.value = false;
    }
};

watch(searchQuery, (newQuery) => {
    if (props.apiUrl) {
        // Debounce could be added here
        currentPage.value = 1;
        fetchOptions(1, newQuery);
    } else if (props.enableLazyLoad) {
        emit('search', newQuery);
    }
});

watch(isOpen, (val) => {
    if (val) {
        if (props.apiUrl && internalOptions.value.length === 0) {
            fetchOptions();
        }
        nextTick(() => {
            const input = listContainer.value?.querySelector('input');
            if (input) input.focus();
            setupObserver();
        });
    } else {
        if (!props.apiUrl) searchQuery.value = '';
    }
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const selectOption = (option) => {
    const value = typeof option === 'object' ? option[props.valueKey] : option;
    emit('update:modelValue', value);
    emit('change', option);
    closeDropdown();
};

const clearSelection = (e) => {
    e.stopPropagation();
    emit('update:modelValue', null);
    emit('change', null);
};

// Lazy Loading Observer
const setupObserver = () => {
    if ((!props.enableLazyLoad && !props.apiUrl) || !sentinel.value) return;

    if (observer.value) observer.value.disconnect();

    observer.value = new IntersectionObserver((entries) => {
        const hasNext = props.apiUrl ? internalHasNextPage.value : props.hasNextPage;
        
        if (entries[0].isIntersecting && hasNext && !isLoading.value) {
            if (props.apiUrl) {
                fetchOptions(currentPage.value + 1, searchQuery.value);
            } else {
                emit('loadMore');
            }
        }
    }, {
        root: listContainer.value,
        threshold: 0.1
    });

    observer.value.observe(sentinel.value);
};

// Click outside directive equivalent
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Initial fetch if value exists?
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (observer.value) observer.value.disconnect();
});

const handleClickOutside = (event) => {
    if (triggerRef.value && !triggerRef.value.contains(event.target) && 
        listContainer.value && !listContainer.value.contains(event.target)) {
        closeDropdown();
    }
};

</script>

<template>
    <div class="relative w-full" ref="triggerRef">
        <!-- Trigger Button -->
        <div
            @click="toggleDropdown"
            :class="[
                'w-full px-3 py-2 text-left bg-white dark:bg-gray-900 border rounded-md shadow-sm cursor-pointer flex justify-between items-center transition-colors',
                disabled ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-75' : 'hover:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500',
                isOpen ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-300 dark:border-gray-700'
            ]"
        >
            <span v-if="displayValue" class="block truncate text-gray-900 dark:text-gray-100">
                {{ displayValue }}
            </span>
            <span v-else class="block truncate text-gray-400">
                {{ placeholder }}
            </span>

            <div class="flex items-center ml-2">
                <button 
                    v-if="modelValue && !disabled" 
                    @click="clearSelection"
                    class="p-1 mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-full"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <svg
                    class="w-5 h-5 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            ref="listContainer"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-auto py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
        >
            <!-- Search Input -->
            <div class="sticky top-0 z-10 px-2 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                />
            </div>

            <!-- Options List -->
            <ul class="py-1">
                <li
                    v-for="(option, index) in filteredOptions"
                    :key="typeof option === 'object' ? option[valueKey] : index"
                    @click="selectOption(option)"
                    :class="[
                        'cursor-pointer select-none relative py-2 pl-3 pr-9',
                        (typeof option === 'object' ? option[valueKey] : option) == modelValue 
                            ? 'text-white bg-indigo-600' 
                            : 'text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                >
                    <span :class="['block truncate', (typeof option === 'object' ? option[valueKey] : option) == modelValue ? 'font-semibold' : 'font-normal']">
                        {{ typeof option === 'object' ? option[labelKey] : option }}
                    </span>

                    <span
                        v-if="(typeof option === 'object' ? option[valueKey] : option) == modelValue"
                        :class="['absolute inset-y-0 right-0 flex items-center pr-4', (typeof option === 'object' ? option[valueKey] : option) == modelValue ? 'text-white' : 'text-indigo-600']"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>

                <!-- Empty State -->
                <li v-if="filteredOptions.length === 0 && !loading" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500">
                    No results found
                </li>

                <!-- Loading State -->
                <li v-if="loading" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 text-center">
                   <svg class="animate-spin h-5 w-5 text-indigo-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </li>
                
                <!-- Sentinel for Lazy Loading -->
                <div ref="sentinel" class="h-1"></div>
            </ul>
        </div>
    </div>
</template>
