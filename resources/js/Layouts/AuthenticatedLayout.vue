<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

const page = usePage();
const sidebarOpen = ref(false); // Closed by default on mobile
const mobileMenuOpen = ref(false);
const isLoading = ref(false);
const isMobile = ref(false);

const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role || 'siswa');

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
    isMobile.value = window.innerWidth < 1024; // lg breakpoint
    if (!isMobile.value) {
        sidebarOpen.value = true; // Open sidebar on desktop
        mobileMenuOpen.value = false;
    } else {
        sidebarOpen.value = false; // Close sidebar on mobile
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

const menuItems = computed(() => {
    const role = userRole.value;
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

    // Menu untuk Admin & Master - Master Data
    if (role === 'admin' || role === 'master') {
        sections.push({
            title: 'Master Data',
            items: [
                { name: 'Kategori', route: 'categories.index', icon: '📁', gradient: 'from-purple-500 to-pink-500' },
                { name: 'Produk', route: 'products.index', icon: '📦', gradient: 'from-green-500 to-emerald-500' },
                { name: 'Siswa', route: 'students.index', icon: '👥', gradient: 'from-orange-500 to-red-500' }
            ]
        });

        // Inventory & Finance
        sections.push({
            title: 'Inventori & Keuangan',
            items: [
                { name: 'Stok Masuk', route: 'stock-ins.index', icon: '📥', gradient: 'from-indigo-500 to-blue-500' },
                { name: 'Voucher', route: 'vouchers.index', icon: '🎟️', gradient: 'from-yellow-500 to-orange-500' },
                { name: 'Pengeluaran', route: 'expenses.index', icon: '💸', gradient: 'from-red-500 to-pink-500' },
                { name: 'Transaksi', route: 'transactions.index', icon: '💳', gradient: 'from-cyan-500 to-blue-500' }
            ]
        });
    }

    // Menu Manajemen User untuk Admin & Master
    if (role === 'admin' || role === 'master') {
        sections.push({
            title: 'Manajemen User',
            items: [
                { name: 'Daftar Pengguna', route: 'users.index', icon: '👤', gradient: 'from-violet-500 to-purple-500' },
                { name: 'Tambah Pengguna', route: 'users.create', icon: '➕', gradient: 'from-green-500 to-emerald-500' }
            ]
        });
    } else if (role === 'kasir') {
        // Menu Manajemen User untuk Staf Koperasi jika punya permission
        const userPermissions = user.value?.permissions || {};
        const hasFullAccess = Object.keys(userPermissions).length === 0 || Object.keys(userPermissions).length >= 30;
        const userManagementItems = [];

        if (hasFullAccess || userPermissions.view_users || userPermissions.manage_users) {
            userManagementItems.push({ name: 'Daftar Pengguna', route: 'users.index', icon: '👤', gradient: 'from-violet-500 to-purple-500' });
        }

        if (hasFullAccess || userPermissions.manage_users) {
            userManagementItems.push({ name: 'Tambah Pengguna', route: 'users.create', icon: '➕', gradient: 'from-green-500 to-emerald-500' });
        }

        if (userManagementItems.length > 0) {
            sections.push({
                title: 'Manajemen User',
                items: userManagementItems
            });
        }
    }

    // Menu untuk Staf Koperasi - Dinamis berdasarkan permissions
    if (role === 'kasir') {
        const userPermissions = user.value?.permissions || {};
        const hasFullAccess = Object.keys(userPermissions).length === 0 || Object.keys(userPermissions).length >= 30;

        // Master Data Section
        const masterDataItems = [];

        // Kategori
        if (hasFullAccess || userPermissions.view_categories || userPermissions.manage_categories) {
            masterDataItems.push({ name: 'Kategori', route: 'categories.index', icon: '📁', gradient: 'from-purple-500 to-pink-500' });
        }

        // Produk
        if (hasFullAccess || userPermissions.view_products || userPermissions.manage_products) {
            masterDataItems.push({ name: 'Produk', route: 'products.index', icon: '📦', gradient: 'from-green-500 to-emerald-500' });
        }

        // Siswa
        if (hasFullAccess || userPermissions.view_students || userPermissions.manage_students) {
            masterDataItems.push({ name: 'Siswa', route: 'students.index', icon: '👥', gradient: 'from-orange-500 to-red-500' });
        }

        if (masterDataItems.length > 0) {
            sections.push({
                title: 'Master Data',
                items: masterDataItems
            });
        }

        // Inventori & Keuangan Section
        const inventoryFinanceItems = [];

        // Stok Masuk
        if (hasFullAccess || userPermissions.view_stock_ins || userPermissions.manage_stock) {
            inventoryFinanceItems.push({ name: 'Stok Masuk', route: 'stock-ins.index', icon: '📥', gradient: 'from-indigo-500 to-blue-500' });
        }

        // Voucher
        if (hasFullAccess || userPermissions.view_vouchers || userPermissions.manage_vouchers || userPermissions.redeem_voucher) {
            inventoryFinanceItems.push({ name: 'Voucher', route: 'vouchers.index', icon: '🎟️', gradient: 'from-yellow-500 to-orange-500' });
        }

        // Pengeluaran
        if (hasFullAccess || userPermissions.view_expenses || userPermissions.manage_expenses) {
            inventoryFinanceItems.push({ name: 'Pengeluaran', route: 'expenses.index', icon: '💸', gradient: 'from-red-500 to-pink-500' });
        }

        // Transaksi
        if (hasFullAccess || userPermissions.view_transactions || userPermissions.topup_balance) {
            inventoryFinanceItems.push({ name: 'Transaksi', route: 'transactions.index', icon: '💳', gradient: 'from-cyan-500 to-blue-500' });
        }

        if (inventoryFinanceItems.length > 0) {
            sections.push({
                title: 'Inventori & Keuangan',
                items: inventoryFinanceItems
            });
        }
    }

    // Menu POS untuk Admin, Master & Staf Koperasi
    if (role === 'admin' || role === 'master') {
        sections.push({
            title: 'Penjualan',
            items: [
                { name: 'Point of Sale', route: 'pos.index', icon: '🛒', gradient: 'from-pink-500 to-rose-500' }
            ]
        });
    } else if (role === 'kasir') {
        const userPermissions = user.value?.permissions || {};
        const hasFullAccess = Object.keys(userPermissions).length === 0 || Object.keys(userPermissions).length >= 30;

        if (hasFullAccess || userPermissions.access_pos) {
            sections.push({
                title: 'Penjualan',
                items: [
                    { name: 'Point of Sale', route: 'pos.index', icon: '🛒', gradient: 'from-pink-500 to-rose-500' }
                ]
            });
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

    // Menu Laporan untuk Admin & Master
    if (role === 'admin' || role === 'master') {
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
    } else if (role === 'kasir') {
        // Menu Laporan untuk Staf Koperasi - Dinamis berdasarkan permissions
        const userPermissions = user.value?.permissions || {};
        const hasFullAccess = Object.keys(userPermissions).length === 0 || Object.keys(userPermissions).length >= 30;
        const reportItems = [];

        if (hasFullAccess || userPermissions.view_sales_reports) {
            reportItems.push({ name: 'Laporan Penjualan', route: 'reports.sales', icon: '📊', gradient: 'from-teal-500 to-green-500' });
        }

        if (hasFullAccess || userPermissions.view_inventory_reports) {
            reportItems.push({ name: 'Laporan Inventori', route: 'reports.inventory', icon: '📈', gradient: 'from-blue-500 to-indigo-500' });
        }

        if (hasFullAccess || userPermissions.view_stock_adjustment_reports) {
            reportItems.push({ name: 'Penyesuaian Stok', route: 'reports.stock-adjustments', icon: '📦', gradient: 'from-orange-500 to-red-500' });
        }

        if (hasFullAccess || userPermissions.view_financial_reports) {
            reportItems.push({ name: 'Laporan Keuangan', route: 'reports.financial', icon: '💰', gradient: 'from-green-500 to-teal-500' });
        }

        if (hasFullAccess || userPermissions.view_student_transaction_reports) {
            reportItems.push({ name: 'Transaksi Siswa', route: 'reports.student-transactions', icon: '💳', gradient: 'from-purple-500 to-indigo-500' });
        }

        if (reportItems.length > 0) {
            sections.push({
                title: 'Laporan',
                items: reportItems
            });
        }
    }

    return sections;
});

const isActiveRoute = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

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
    <div class="absolute inset-0 overflow-hidden pointer-events-none hidden md:block">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Mobile Menu Overlay -->
    <Transition name="fade">
        <div v-if="mobileMenuOpen && isMobile" @click="closeMobileMenu" class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="[
        'fixed top-0 z-50 h-screen transition-all duration-300 ease-in-out',
        // Mobile: slide from left
        isMobile ? [
            mobileMenuOpen ? 'left-0' : '-left-full',
            'w-64'
        ] : [
            // Desktop: normal behavior
            'left-0',
            sidebarOpen ? 'w-64' : 'w-20'
        ]
    ]">
        <div class="h-full px-3 py-4 overflow-y-auto bg-gradient-to-b from-gray-900/95 to-gray-800/95 backdrop-blur-xl border-r border-purple-500/30 shadow-2xl">
            <!-- Logo & Toggle -->
            <div class="flex items-center justify-between mb-6 px-2">
                <Link :href="route('dashboard')" v-if="sidebarOpen || isMobile" class="flex items-center space-x-3 group">
                    <span class="text-2xl md:text-3xl group-hover:scale-110 transition-transform duration-300">🏢</span>
                    <span class="text-lg md:text-xl font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent animate-gradient">
                        Koperasi
                    </span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white transition-all duration-300 hover:scale-110 shadow-lg hover:shadow-purple-500/50"
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
            <div v-if="user" :class="['mb-6 p-3 md:p-4 rounded-xl bg-gradient-to-r from-purple-600/50 to-pink-600/50 backdrop-blur-sm border border-purple-400/30 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/30', (sidebarOpen || isMobile) ? '' : 'px-2']">
                <div v-if="sidebarOpen || isMobile" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-lg border-2 border-purple-400 animate-pulse-slow">
                                <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg animate-pulse-slow">
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
                        {{ userRoleLabel }}
                    </span>
                </div>
                <div v-else class="flex justify-center">
                    <div class="relative">
                        <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-lg border-2 border-purple-400">
                            <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900"></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-4 md:space-y-6">
                <div v-for="(section, sectionIndex) in menuItems" :key="sectionIndex">
                    <!-- Section Title -->
                    <div v-if="section.title && (sidebarOpen || isMobile)" class="px-3 mb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-purple-300/70">
                            {{ section.title }}
                        </h3>
                    </div>

                    <!-- Section Items -->
                    <div class="space-y-1 md:space-y-2">
                        <Link
                            v-for="item in section.items"
                            :key="item.route"
                            :href="route(item.route)"
                            @click="closeMobileMenu"
                            :class="[
                                'group flex items-center rounded-xl transition-all duration-300 transform hover:scale-105',
                                (sidebarOpen || isMobile) ? 'px-3 md:px-4 py-2 md:py-3' : 'px-3 py-3 justify-center',
                                isActiveRoute(item.route)
                                    ? 'bg-gradient-to-r ' + item.gradient + ' text-white shadow-lg shadow-purple-500/50'
                                    : 'text-gray-300 hover:bg-white/10 hover:text-white'
                            ]">
                            <span :class="['text-xl md:text-2xl transition-transform duration-300 group-hover:scale-125 group-hover:rotate-12', (sidebarOpen || isMobile) ? '' : 'mx-auto']">
                                {{ item.icon }}
                            </span>
                            <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-sm md:text-base">{{ item.name }}</span>

                            <!-- Active indicator -->
                            <span v-if="isActiveRoute(item.route) && (sidebarOpen || isMobile)" class="ml-auto">
                                <span class="flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                </span>
                            </span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Profile & Logout Section -->
            <div class="mt-6 md:mt-8 pt-6 border-t border-purple-500/30 space-y-2">
                <Link
                    :href="route('profile.edit')"
                    @click="closeMobileMenu"
                    :class="[
                        'group flex items-center w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-indigo-500/50',
                        (sidebarOpen || isMobile) ? 'px-3 md:px-4 py-2 md:py-3' : 'px-3 py-3 justify-center'
                    ]">
                    <span :class="['text-xl md:text-2xl transition-transform duration-300 group-hover:scale-125', (sidebarOpen || isMobile) ? '' : 'mx-auto']">⚙️</span>
                    <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-sm md:text-base">Profil</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    @click="closeMobileMenu"
                    :class="[
                        'group flex items-center w-full rounded-xl bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-red-500/50',
                        (sidebarOpen || isMobile) ? 'px-3 md:px-4 py-2 md:py-3' : 'px-3 py-3 justify-center'
                    ]">
                    <span :class="['text-xl md:text-2xl transition-transform duration-300 group-hover:scale-125', (sidebarOpen || isMobile) ? '' : 'mx-auto']">🚪</span>
                    <span v-if="sidebarOpen || isMobile" class="ml-3 font-medium text-sm md:text-base">Logout</span>
                </Link>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div :class="[
        'transition-all duration-300',
        !isMobile && (sidebarOpen ? 'lg:ml-64' : 'lg:ml-20')
    ]">
        <!-- Mobile Header with Menu Button -->
        <div v-if="isMobile" class="sticky top-0 z-30 bg-white/10 backdrop-blur-xl border-b border-purple-500/30 shadow-lg lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <span class="text-2xl">🏢</span>
                    <span class="text-lg font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent">
                        Koperasi
                    </span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg"
                    title="Open menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Header -->
        <header v-if="$slots.header" class="sticky top-0 z-30 bg-white/10 backdrop-blur-xl border-b border-purple-500/30 shadow-lg animate-slide-down hidden lg:block">
            <div class="px-3 sm:px-4 md:px-6 lg:px-8 py-4 md:py-6">
                <div class="bg-gradient-to-r from-purple-600/30 to-pink-600/30 rounded-xl md:rounded-2xl p-4 md:p-6 backdrop-blur-sm border border-purple-400/30 hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300">
                    <slot name="header" />
                </div>
            </div>
        </header>

        <!-- Main -->
        <main class="relative z-10 p-3 sm:p-4 md:p-6">
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

@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
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

.animate-gradient {
    background-size: 200% auto;
    animation: gradient 3s ease infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
