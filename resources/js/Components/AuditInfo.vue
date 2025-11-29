<script setup>
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        default: null
    },
    timestamp: {
        type: String,
        default: null
    },
    label: {
        type: String,
        default: 'Data'
    }
});

const getRoleLabel = (role) => {
    const roleMap = {
        'master': 'MASTER',
        'admin': 'ADMIN',
        'kasir': 'STAF KOPERASI',
        'siswa': 'SISWA'
    };
    return roleMap[role] || role?.toUpperCase() || 'USER';
};

const getRoleBadgeClass = (role) => {
    const classMap = {
        'master': 'bg-purple-100 text-purple-800 border-purple-200',
        'admin': 'bg-blue-100 text-blue-800 border-blue-200',
        'kasir': 'bg-green-100 text-green-800 border-green-200',
        'siswa': 'bg-gray-100 text-gray-800 border-gray-200'
    };
    return classMap[role] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <div v-if="user" class="group relative inline-block">
        <!-- Compact View -->
        <div class="flex items-center gap-2 cursor-help">
            <!-- Avatar/Photo -->
            <div v-if="user.photo" class="w-8 h-8 rounded-full overflow-hidden shadow-sm border-2 border-gray-200">
                <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
            </div>
            <div v-else class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs shadow-sm border-2 border-white">
                {{ user.name?.charAt(0).toUpperCase() }}
            </div>

            <!-- Name (truncated on mobile) -->
            <span class="text-sm font-medium text-white max-w-[100px] truncate">
                {{ user.name }}
            </span>
        </div>

        <!-- Tooltip/Hover Card -->
        <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 transition-all duration-200">
            <div class="bg-gray-900 text-white text-xs rounded-lg shadow-xl p-3 min-w-[200px]">
                <!-- Tooltip Arrow -->
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px">
                    <div class="border-8 border-transparent border-t-gray-900"></div>
                </div>

                <!-- Content -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-700">
                        <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-sm border-2 border-gray-600">
                            <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-sm border-2 border-gray-600">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold">{{ user.name }}</div>
                            <div class="text-xs text-gray-400">{{ user.email }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-400 text-xs mb-1">Role:</div>
                        <div class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold"
                             :class="getRoleBadgeClass(user.role)">
                            {{ getRoleLabel(user.role) }}
                        </div>
                    </div>

                    <div v-if="timestamp">
                        <div class="text-gray-400 text-xs mb-1">{{ label }} pada:</div>
                        <div class="text-xs font-mono">{{ formatDateTime(timestamp) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-xs text-gray-400">
        -
    </div>
</template>

<style scoped>
/* Ensure tooltip appears above table */
.group:hover .group-hover\:visible {
    z-index: 9999;
}
</style>
