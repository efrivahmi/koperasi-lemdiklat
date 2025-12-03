<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';

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

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
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
<div class="relative min-h-screen overflow-hidden bg-gray-50">
    <!-- Loading Overlay -->
    <Transition name="fade">
        <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="text-center space-y-4">
                <LoadingSpinner size="xl" color="purple" />
                <p class="text-white text-lg font-semibold animate-pulse">Loading...</p>
            </div>
        </div>
    </Transition>

    <!-- Mobile Menu Overlay -->
    <Transition name="fade">
        <div v-if="mobileMenuOpen && isMobile" @click="closeMobileMenu" class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="[
        'fixed top-0 z-50 h-screen transition-all duration-300 ease-in-out bg-white border-r border-gray-200 shadow-lg',
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
                    <span class="text-3xl group-hover:scale-110 transition-transform duration-300">🏢</span>
                    <span class="text-xl font-bold text-gray-800">
                        Koperasi
                    </span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-all duration-300 hover:scale-110 shadow-md"
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
            <div v-if="user" :class="['mb-6 p-4 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 transition-all duration-300', (sidebarOpen || isMobile) ? '' : 'px-2']">
                <div v-if="sidebarOpen || isMobile" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-indigo-400">
                                <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-md">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-indigo-600 text-white shadow-sm">
                        {{ userRoleLabel }}
                    </span>
                </div>
                <div v-else class="flex justify-center">
                    <div class="relative">
                        <div v-if="user.photo" class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-indigo-400">
                            <img :src="`/storage/${user.photo}`" :alt="user.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold shadow-md">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="space-y-4">
                <div v-for="(section, sectionIndex) in menuItems" :key="sectionIndex">
                    <!-- Section Title -->
                    <div v-if="section.title && (sidebarOpen || isMobile)" class="px-3 mb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">
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
                                    ? 'bg-indigo-600 text-white shadow-md'
                                    : 'text-gray-700 hover:bg-gray-100'
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
            <div class="mt-8 pt-6 border-t border-gray-200 space-y-2">
                <Link
                    :href="route('profile.edit')"
                    @click="closeMobileMenu"
                    :class="[
                        'group flex items-center w-full rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200',
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
                        'group flex items-center w-full rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-200',
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
        <div v-if="isMobile" class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm lg:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <span class="text-2xl">🏢</span>
                    <span class="text-lg font-bold text-gray-800">Koperasi</span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="p-2 rounded-lg bg-indigo-600 text-white shadow-md"
                    title="Open menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Header -->
        <header v-if="$slots.header" class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm hidden lg:block">
            <div class="px-4 lg:px-8 py-6">
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
</style>
