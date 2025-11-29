<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Memproses...'
    },
    fullscreen: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            :class="[
                'loading-overlay',
                fullscreen ? 'fixed inset-0' : 'absolute inset-0'
            ]"
            class="flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl p-8 flex flex-col items-center space-y-4 max-w-sm mx-4">
                <!-- Spinner -->
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-200 rounded-full"></div>
                    <div class="absolute top-0 left-0 w-16 h-16 border-4 border-blue-600 rounded-full animate-spin border-t-transparent"></div>
                </div>

                <!-- Message -->
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-800">{{ message }}</p>
                    <p class="text-sm text-gray-500 mt-1">Mohon tunggu sebentar...</p>
                </div>

                <!-- Progress dots animation -->
                <div class="flex space-x-2">
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
