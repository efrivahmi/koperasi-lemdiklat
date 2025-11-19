<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

const page = usePage();
const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);
const isLoading = ref(false);

const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role || 'siswa');

const menuItems = computed(() => {
    const role = userRole.value;
    const items = [];

    // Dashboard - semua role
    items.push({
        name: 'Dashboard',
        route: role === 'siswa' ? 'student.dashboard' : 'dashboard',
        icon: '🏠',
        gradient: 'from-blue-500 to-cyan-500'
    });

    // Menu untuk Admin & Master
    if (role === 'admin' || role === 'master') {
        items.push(
            { name: 'Kategori', route: 'categories.index', icon: '📁', gradient: 'from-purple-500 to-pink-500' },
            { name: 'Produk', route: 'products.index', icon: '📦', gradient: 'from-green-500 to-emerald-500' },
            { name: 'Siswa', route: 'students.index', icon: '👥', gradient: 'from-orange-500 to-red-500' },
            { name: 'Stok Masuk', route: 'stock-ins.index', icon: '📥', gradient: 'from-indigo-500 to-blue-500' },
            { name: 'Voucher', route: 'vouchers.index', icon: '🎟️', gradient: 'from-yellow-500 to-orange-500' },
            { name: 'Pengeluaran', route: 'expenses.index', icon: '💸', gradient: 'from-red-500 to-pink-500' },
            { name: 'Transaksi', route: 'transactions.index', icon: '💳', gradient: 'from-cyan-500 to-blue-500' }
        );
    }

    // Menu untuk Master saja
    if (role === 'master') {
        items.push({ name: 'Pengguna', route: 'users.index', icon: '👤', gradient: 'from-violet-500 to-purple-500' });
    }

    // Menu POS untuk Admin, Master & Kasir
    if (role === 'admin' || role === 'master' || role === 'kasir') {
        items.push({ name: 'Point of Sale', route: 'pos.index', icon: '🛒', gradient: 'from-pink-500 to-rose-500' });
    }

    // Menu untuk Siswa
    if (role === 'siswa') {
        items.push(
            { name: 'Transaksi Saya', route: 'student.transactions', icon: '💳', gradient: 'from-purple-500 to-indigo-500' },
            { name: 'Riwayat Pembelian', route: 'student.purchases', icon: '🛍️', gradient: 'from-pink-500 to-purple-500' }
        );
    }

    // Menu Laporan untuk Admin & Master
    if (role === 'admin' || role === 'master') {
        items.push(
            { name: 'Laporan Penjualan', route: 'reports.sales', icon: '📊', gradient: 'from-teal-500 to-green-500' },
            { name: 'Laporan Inventori', route: 'reports.inventory', icon: '📈', gradient: 'from-blue-500 to-indigo-500' },
            { name: 'Laporan Keuangan', route: 'reports.financial', icon: '💰', gradient: 'from-green-500 to-teal-500' }
        );
    }

    return items;
});

const isActiveRoute = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Show loading on navigation
page.props.isLoading = isLoading;
</script>

<template>
<div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-gray-900 via-purple-900 to-indigo-900">
    <!-- Loading Overlay -->
    <Transition name="fade">
        <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="text-center space-y-4">
                <LoadingSpinner size="xl" color="purple" />
                <p class="text-white text-lg font-semibold animate-pulse">Loading...</p>
            </div>
        </div>
    </Transition>

    <!-- Animated Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out',
        sidebarOpen ? 'w-64' : 'w-20'
    ]">
        <div class="h-full px-3 py-4 overflow-y-auto bg-gradient-to-b from-gray-900/95 to-gray-800/95 backdrop-blur-xl border-r border-purple-500/30 shadow-2xl">
            <!-- Logo & Toggle -->
            <div class="flex items-center justify-between mb-8 px-2">
                <Link :href="route('dashboard')" v-if="sidebarOpen" class="flex items-center space-x-3 group">
                    <span class="text-3xl group-hover:scale-110 transition-transform duration-300">🏢</span>
                    <span class="text-xl font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent animate-gradient">
                        Koperasi
                    </span>
                </Link>
                <button @click="toggleSidebar" class="p-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white transition-all duration-300 hover:scale-110 shadow-lg hover:shadow-purple-500/50">
                    <svg class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-180': !sidebarOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div v-if="user" :class="['mb-6 p-4 rounded-xl bg-gradient-to-r from-purple-600/50 to-pink-600/50 backdrop-blur-sm border border-purple-400/30 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/30', sidebarOpen ? '' : 'px-2']">
                <div v-if="sidebarOpen" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg animate-pulse-slow">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900 animate-ping"></span>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ user.name }}</p>
                            <p class="text-xs text-purple-200 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 shadow-lg animate-pulse">
                        {{ userRole.toUpperCase() }}
                    </span>
                </div>
                <div v-else class="flex justify-center">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900"></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-2">
                <Link v-for="item in menuItems" :key="item.route" :href="route(item.route)"
                    :class="[
                        'group flex items-center rounded-xl transition-all duration-300 transform hover:scale-105 hover:translate-x-1',
                        sidebarOpen ? 'px-4 py-3' : 'px-3 py-3 justify-center',
                        isActiveRoute(item.route)
                            ? 'bg-gradient-to-r ' + item.gradient + ' text-white shadow-lg shadow-purple-500/50'
                            : 'text-gray-300 hover:bg-white/10 hover:text-white'
                    ]">
                    <span :class="['text-2xl transition-transform duration-300 group-hover:scale-125 group-hover:rotate-12', sidebarOpen ? '' : 'mx-auto']">
                        {{ item.icon }}
                    </span>
                    <span v-if="sidebarOpen" class="ml-3 font-medium">{{ item.name }}</span>

                    <!-- Active indicator -->
                    <span v-if="isActiveRoute(item.route) && sidebarOpen" class="ml-auto">
                        <span class="flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                    </span>
                </Link>
            </nav>

            <!-- Logout Button -->
            <div class="mt-8 pt-6 border-t border-purple-500/30">
                <Link :href="route('logout')" method="post" as="button"
                    :class="[
                        'group flex items-center w-full rounded-xl bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-red-500/50',
                        sidebarOpen ? 'px-4 py-3' : 'px-3 py-3 justify-center'
                    ]">
                    <span :class="['text-2xl transition-transform duration-300 group-hover:scale-125', sidebarOpen ? '' : 'mx-auto']">🚪</span>
                    <span v-if="sidebarOpen" class="ml-3 font-medium">Logout</span>
                </Link>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-300', sidebarOpen ? 'ml-64' : 'ml-20']">
        <!-- Header -->
        <header v-if="$slots.header" class="sticky top-0 z-30 bg-white/10 backdrop-blur-xl border-b border-purple-500/30 shadow-lg animate-slide-down">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-purple-600/30 to-pink-600/30 rounded-2xl p-6 backdrop-blur-sm border border-purple-400/30 hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300">
                    <slot name="header" />
                </div>
            </div>
        </header>

        <!-- Main -->
        <main class="relative z-10 p-6">
            <div class="animate-fade-in">
                <slot />
            </div>
        </main>
    </div>
</div>
</template>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gradient {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}

.animate-slide-down {
    animation: slide-down 0.3s ease-out;
}

.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
