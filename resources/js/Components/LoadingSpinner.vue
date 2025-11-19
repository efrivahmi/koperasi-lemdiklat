<script setup>
defineProps({
    size: {
        type: String,
        default: 'md' // xs, sm, md, lg, xl
    },
    color: {
        type: String,
        default: 'blue' // blue, green, purple, red, yellow
    }
});

const sizeClasses = {
    xs: 'w-4 h-4',
    sm: 'w-6 h-6',
    md: 'w-8 h-8',
    lg: 'w-12 h-12',
    xl: 'w-16 h-16'
};

const colorClasses = {
    blue: 'border-blue-500',
    green: 'border-green-500',
    purple: 'border-purple-500',
    red: 'border-red-500',
    yellow: 'border-yellow-500'
};
</script>

<template>
    <div class="flex items-center justify-center">
        <div :class="[sizeClasses[size], colorClasses[color]]"
             class="animate-spin rounded-full border-4 border-t-transparent">
        </div>
    </div>
</template>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
