<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);
const mobileMenuOpen = ref(false);
const isLoading = ref(false);
const isMobile = ref(false);

const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role || 'siswa');
const userPermissions = computed(() => user.value?.permissions || {});

// Get role label for display
const userRoleLabel = computed(() => {
    const role = userRole.value;
    if (role === 'kasir') return 'STAF KOPERASI';
    if (role === 'admin') return 'ADMIN';
    if (role === 'master') return 'MASTER';
    if (role === 'siswa') return 'SISWA';
    return role.toUpperCase();
});

// Check if viewport is mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
    if (!isMobile.value) {
        sidebarOpen.value = true;
        mobileMenuOpen.value = false;
    } else {
        sidebarOpen.value = false;
    }
};

// Setup loading state for Inertia navigation
const handleStart = () => {
    isLoading.value = true;
};

const handleFinish = () => {
    isLoading.value = false;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);

    // Listen to Inertia navigation events
    router.on('start', handleStart);
    router.on('finish', handleFinish);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);

    // Inertia.js automatically handles event listener cleanup
    // No need to manually remove router event listeners
});

const menuItems = computed(() => {
    const role = userRole.value;
    const permissions = userPermissions.value;
    const sections = [];

    // Dashboard - semua role
    sections.push({
        title: null,
        items: [{
            name: 'Dashboard',
            route: role === 'siswa' ? 'student.dashboard' : 'dashboard',
            icon: '🏠',
            gradient: 'from-blue-500 to-cyan-500'
        }]
    });

    // Menu untuk Admin & Master
    if (role === 'admin' || role === 'master') {
        sections.push({
            title: 'Master Data',
            items: [
                { name: 'Kategori', route: 'categories.index', icon: '📁', gradient: 'from-purple-500 to-pink-500' },
                { name: 'Produk', route: 'products.index', icon: '📦', gradient: 'from-green-500 to-emerald-500' },
                { name: 'Siswa', route: 'students.index', icon: '👥', gradient: 'from-orange-500 to-red-500' }
            ]
        });

        sections.push({
            title: 'Inventori & Keuangan',
            items: [
                { name: 'Stok Masuk', route: 'stock-ins.index', icon: '📥', gradient: 'from-indigo-500 to-blue-500' },
                { name: 'Voucher', route: 'vouchers.index', icon: '🎟️', gradient: 'from-yellow-500 to-orange-500' },
                { name: 'Pengeluaran', route: 'expenses.index', icon: '💸', gradient: 'from-red-500 to-pink-500' },
                { name: 'Transaksi', route: 'transactions.index', icon: '💳', gradient: 'from-cyan-500 to-blue-500' }
            ]
        });

        sections.push({
            title: 'Manajemen User',
            items: [
                { name: 'Daftar Pengguna', route: 'users.index', icon: '👤', gradient: 'from-violet-500 to-purple-500' },
                { name: 'Tambah Pengguna', route: 'users.create', icon: '➕', gradient: 'from-green-500 to-emerald-500' }
            ]
        });

        sections.push({
            title: 'Penjualan',
            items: [
                { name: 'Point of Sale', route: 'pos.index', icon: '🛒', gradient: 'from-pink-500 to-rose-500' }
            ]
        });

        sections.push({
            title: 'Laporan',
            items: [
                { name: 'Laporan Penjualan', route: 'reports.sales', icon: '📊', gradient: 'from-teal-500 to-green-500' },
                { name: 'Laporan Inventori', route: 'reports.inventory', icon: '📈', gradient: 'from-blue-500 to-indigo-500' },
                { name: 'Penyesuaian Stok', route: 'reports.stock-adjustments', icon: '📦', gradient: 'from-orange-500 to-red-500' },
                { name: 'Laporan Keuangan', route: 'reports.financial', icon: '💰', gradient: 'from-green-500 to-teal-500' },
                { name: 'Transaksi Siswa', route: 'reports.student-transactions', icon: '💳', gradient: 'from-purple-500 to-indigo-500' }
            ]
        });
    }

    // Menu untuk Kasir - Berdasarkan permissions
    if (role === 'kasir') {
        // Master Data
        const masterDataItems = [];
        if (permissions.module_categories) {
            masterDataItems.push({ name: 'Kategori', route: 'categories.index', icon: '📁', gradient: 'from-purple-500 to-pink-500' });
        }
        if (permissions.module_products) {
            masterDataItems.push({ name: 'Produk', route: 'products.index', icon: '📦', gradient: 'from-green-500 to-emerald-500' });
        }
        if (permissions.module_students) {
            masterDataItems.push({ name: 'Siswa', route: 'students.index', icon: '👥', gradient: 'from-orange-500 to-red-500' });
        }
        if (masterDataItems.length > 0) {
            sections.push({ title: 'Master Data', items: masterDataItems });
        }

        // Inventori & Keuangan
        const inventoryItems = [];
        if (permissions.module_stock_ins) {
            inventoryItems.push({ name: 'Stok Masuk', route: 'stock-ins.index', icon: '📥', gradient: 'from-indigo-500 to-blue-500' });
        }
        if (permissions.module_vouchers) {
            inventoryItems.push({ name: 'Voucher', route: 'vouchers.index', icon: '🎟️', gradient: 'from-yellow-500 to-orange-500' });
        }
        if (permissions.module_expenses) {
            inventoryItems.push({ name: 'Pengeluaran', route: 'expenses.index', icon: '💸', gradient: 'from-red-500 to-pink-500' });
        }
        if (permissions.module_transactions) {
            inventoryItems.push({ name: 'Transaksi', route: 'transactions.index', icon: '💳', gradient: 'from-cyan-500 to-blue-500' });
        }
        if (inventoryItems.length > 0) {
            sections.push({ title: 'Inventori & Keuangan', items: inventoryItems });
        }

        // Manajemen User
        if (permissions.module_users) {
            sections.push({
                title: 'Manajemen User',
                items: [
                    { name: 'Daftar Pengguna', route: 'users.index', icon: '👤', gradient: 'from-violet-500 to-purple-500' },
                    { name: 'Tambah Pengguna', route: 'users.create', icon: '➕', gradient: 'from-green-500 to-emerald-500' }
                ]
            });
        }

        // POS
        if (permissions.module_pos) {
            sections.push({
                title: 'Penjualan',
                items: [
                    { name: 'Point of Sale', route: 'pos.index', icon: '🛒', gradient: 'from-pink-500 to-rose-500' }
                ]
            });
        }

        // Laporan
        const reportItems = [];
        if (permissions.module_reports_sales) {
            reportItems.push({ name: 'Laporan Penjualan', route: 'reports.sales', icon: '📊', gradient: 'from-teal-500 to-green-500' });
        }
        if (permissions.module_reports_inventory) {
            reportItems.push({ name: 'Laporan Inventori', route: 'reports.inventory', icon: '📈', gradient: 'from-blue-500 to-indigo-500' });
        }
        if (permissions.module_reports_stock_adjustments) {
            reportItems.push({ name: 'Penyesuaian Stok', route: 'reports.stock-adjustments', icon: '📦', gradient: 'from-orange-500 to-red-500' });
        }
        if (permissions.module_reports_financial) {
            reportItems.push({ name: 'Laporan Keuangan', route: 'reports.financial', icon: '💰', gradient: 'from-green-500 to-teal-500' });
        }
        if (permissions.module_reports_student_transactions) {
            reportItems.push({ name: 'Transaksi Siswa', route: 'reports.student-transactions', icon: '💳', gradient: 'from-purple-500 to-indigo-500' });
        }
        if (reportItems.length > 0) {
            sections.push({ title: 'Laporan', items: reportItems });
        }
    }

    // Menu untuk Siswa
    if (role === 'siswa') {
        sections.push({
            title: 'Aktivitas Saya',
            items: [
                { name: 'Transaksi Saya', route: 'student.transactions', icon: '💳', gradient: 'from-purple-500 to-indigo-500' },
                { name: 'Riwayat Pembelian', route: 'student.purchases', icon: '🛍️', gradient: 'from-pink-500 to-purple-500' }
            ]
        });
    }

    return sections;
});

const isActiveRoute = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

// Get page title for mobile header
const pageTitle = computed(() => {
    // Try to get from page props first
    if (page.props.pageTitle) return page.props.pageTitle;

    // Otherwise use document title (set by Head component)
    const docTitle = document.title;
    if (docTitle && docTitle !== 'Laravel') {
        return docTitle;
    }

    return 'Dashboard';
});

const toggleSidebar = () => {
    if (isMobile.value) {
        mobileMenuOpen.value = !mobileMenuOpen.value;
    } else {
        sidebarOpen.value = !sidebarOpen.value;
    }
};

const closeMobileMenu = () => {
    if (isMobile.value) {
        mobileMenuOpen.value = false;
    }
};

page.props.isLoading = isLoading;
</script>

<template>
<div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900">
    <!-- Decorative Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Animated gradient blobs -->
        <div class="absolute top-0 -left-4 w-96 h-96 bg-purple-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-96 h-96 bg-pink-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-96 h-96 bg-indigo-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>

        <!-- Stars -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-[10%] w-1 h-1 bg-white rounded-full animate-pulse"></div>
            <div class="absolute top-40 left-[20%] w-1 h-1 bg-purple-200 rounded-full animate-pulse animation-delay-1000"></div>
            <div class="absolute top-60 left-[15%] w-1 h-1 bg-pink-200 rounded-full animate-pulse animation-delay-2000"></div>
            <div class="absolute top-32 right-[15%] w-1 h-1 bg-white rounded-full animate-pulse animation-delay-3000"></div>
            <div class="absolute top-52 right-[25%] w-1 h-1 bg-purple-200 rounded-full animate-pulse"></div>
            <div class="absolute bottom-40 left-[30%] w-1 h-1 bg-pink-200 rounded-full animate-pulse animation-delay-1000"></div>
            <div class="absolute bottom-60 right-[20%] w-1 h-1 bg-white rounded-full animate-pulse animation-delay-2000"></div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <Transition name="fade">
        <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="flex flex-col items-center space-y-4">
                <!-- Spinning Circle -->
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                </div>
                <p class="text-white text-lg font-semibold">Loading...</p>
            </div>
        </div>
    </Transition>

    <!-- Mobile Menu Overlay -->
    <Transition name="fade">
        <div v-if="mobileMenuOpen && isMobile" @click="closeMobileMenu" class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="[
        'fixed top-0 z-50 h-screen transition-all duration-300 ease-in-out bg-gradient-to-b from-gray-900 via-purple-900 to-indigo-900 border-r border-purple-500/30 shadow-2xl shadow-purple-500/20',
        isMobile ? [
            mobileMenuOpen ? 'left-0' : '-left-full',
            'w-64'
        ] : [
            'left-0',
            sidebarOpen ? 'w-64' : 'w-20'
        ]
    ]">
        <div class="h-full px-3 py-4 overflow-y-auto">
            <!-- Logo & Toggle -->
            <div class="flex items-center justify-between mb-6 px-2">
                <Link :href="route('dashboard')" v-if="sidebarOpen || isMobile" class="flex items-center space-x-3 group">
                    <img src="/storage/logos/icon.png" alt="Logo Koperasi" class="w-10 h-10 object-contain group-hover:scale-110 transition-transform duration-300 drop-shadow-lg flex-shrink-0" />
                    <div class="flex flex-col min-w-0 flex-1">
                        <span class="text-xs font-bold text-white leading-tight drop-shadow-md">Koperasi Lemdiklat</span>
                        <span class="text-xs font-bold text-white leading-tight drop-shadow-md">Taruna Nusantara</span>
                        <span class="text-xs font-medium text-purple-200 leading-tight drop-shadow-md">Indonesia</span>
                    </div>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white transition-all duration-300 hover:scale-110 shadow-lg shadow-purple-500/50"
                    :title="isMobile ? 'Close menu' : (sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar')"
                >
                    <svg v-if="!isMobile" class="w-5 h-5 transition-transform duration-300" :class="{ 'rotate-180': !sidebarOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div v-if="user" :class="['mb-6 p-4 rounded-xl bg-gradient-to-br from-purple-800/50 to-pink-800/50 border border-purple-400/30 backdrop-blur-sm shadow-lg shadow-purple-500/20 transition-all duration-300', (sidebarOpen || isMobile) ? '' : 'px-2']">
                <div v-if="sidebarOpen || isMobile" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-purple-400">
                                <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow-md">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-800"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate drop-shadow-md">{{ user.name }}</p>
                            <p class="text-xs text-purple-200 truncate drop-shadow-md">{{ user.email }}</p>
                        </div>
                    </div>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg shadow-purple-500/50">
                        {{ userRoleLabel }}
                    </span>
                </div>
                <div v-else class="flex justify-center">
                    <div class="relative">
                        <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-purple-400">
                            <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow-md">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-800"></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-4">
                <div v-for="(section, sectionIndex) in menuItems" :key="sectionIndex">
                    <!-- Section Title -->
                    <div v-if="section.title && (sidebarOpen || isMobile)" class="px-3 mb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-purple-300 drop-shadow-md">
                            {{ section.title }}
                        </h3>
                    </div>

                    <!-- Section Items -->
                    <div class="space-y-1">
                        <Link
                            v-for="item in section.items"
                            :key="item.route"
                            :href="route(item.route)"
                            @click="closeMobileMenu"
                            :class="[
                                'group flex items-center rounded-lg transition-all duration-200',
                                (sidebarOpen || isMobile) ? 'px-4 py-3' : 'px-3 py-3 justify-center',
                                isActiveRoute(item.route)
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg shadow-purple-500/50'
                                    : 'text-purple-200 hover:bg-purple-800/50 hover:text-white'
                            ]">
                            <span :class="['text-2xl transition-transform duration-300 group-hover:scale-110', (sidebarOpen || isMobile) ? '' : 'mx-auto']">
                                {{ item.icon }}
                            </span>
                            <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-base">{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Profile & Logout Section -->
            <div class="mt-8 pt-6 border-t border-purple-500/30 space-y-2">
                <Link
                    :href="route('profile.edit')"
                    @click="closeMobileMenu"
                    :class="[
                        'group flex items-center w-full rounded-lg bg-purple-800/50 hover:bg-purple-700/70 text-purple-100 hover:text-white transition-all duration-200 shadow-md',
                        (sidebarOpen || isMobile) ? 'px-4 py-3' : 'px-3 py-3 justify-center'
                    ]">
                    <span :class="['text-2xl transition-transform duration-300 group-hover:scale-110', (sidebarOpen || isMobile) ? '' : 'mx-auto']">⚙️</span>
                    <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-base">Profil</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    @click="closeMobileMenu"
                    :class="[
                        'group flex items-center w-full rounded-lg bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white transition-all duration-200 shadow-lg shadow-red-500/50',
                        (sidebarOpen || isMobile) ? 'px-4 py-3' : 'px-3 py-3 justify-center'
                    ]">
                    <span :class="['text-2xl transition-transform duration-300 group-hover:scale-110', (sidebarOpen || isMobile) ? '' : 'mx-auto']">🚪</span>
                    <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-base">Logout</span>
                </Link>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div :class="[
        'transition-all duration-300',
        !isMobile && (sidebarOpen ? 'lg:ml-64' : 'lg:ml-20')
    ]">
        <!-- Mobile Header -->
        <div v-if="isMobile" class="sticky top-0 z-30 bg-gradient-to-r from-purple-900 via-indigo-900 to-blue-900 border-b border-purple-500/30 shadow-lg shadow-purple-500/20 lg:hidden backdrop-blur-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-3 min-w-0 flex-1">
                    <Link :href="route('dashboard')" class="flex-shrink-0">
                        <img src="/storage/logos/icon.png" alt="Logo" class="w-8 h-8 object-contain drop-shadow-lg" />
                    </Link>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-base font-bold text-white drop-shadow-md truncate">
                            {{ pageTitle }}
                        </h1>
                    </div>
                </div>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600 text-white shadow-lg shadow-purple-500/50 flex-shrink-0 ml-2"
                    title="Open menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Header -->
        <header v-if="$slots.header" class="sticky top-0 z-20 bg-gradient-to-r from-purple-900/95 via-indigo-900/95 to-blue-900/95 border-b border-purple-500/30 shadow-lg shadow-purple-500/10 backdrop-blur-md hidden lg:block">
            <div class="px-4 lg:px-8 py-6 text-white">
                <slot name="header" />
            </div>
        </header>

        <!-- Main -->
        <main class="relative z-10 p-4 lg:p-6">
            <slot />
        </main>
    </div>
</div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
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

.animation-delay-1000 {
    animation-delay: 1s;
}

.animation-delay-3000 {
    animation-delay: 3s;
}
</style>
