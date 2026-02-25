<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Imported but utilized differently if needed, mostly we go custom
import LoadingOverlay from '@/Components/LoadingOverlay.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import CameraScanner from '@/Components/CameraScanner.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    categories: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- STATE MANAGEMENT ---
const viewMode = ref('marketplace'); // 'marketplace' | 'express'
const currentTab = ref('catalog'); // 'catalog', 'cart', 'transactions'
const products = ref([]);
const cart = ref([]);
// Filter & Search
const selectedProductFilter = ref(null);
const selectedCategory = ref('');
// ... (Hardware Inputs)
const barcodeInput = ref('');
// ... (Transaction Data)
const selectedStudent = ref(null);
const showStudentModal = ref(false);
const studentSearchQuery = ref('');
const studentSearchResults = ref([]);
const searchingStudent = ref(false);
const searchDebounceTimer = ref(null);
const paymentMethod = ref('cash');
const cashAmount = ref(0);
const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });
const recentSales = ref([]);
const posMode = ref('manual'); 

const searchQuery = ref(''); 
const visibleCount = ref(30);
const itemsPerPage = 30;
const showCategoryModal = ref(false);
const activeMegaMenuCategory = ref(null); // For Tokopedia-style hover effect

const showScanner = ref(false);
const scannerMode = ref('product'); // 'product' or 'student'

const currentTime = ref(new Date());
let timeInterval;

const categoryScrollContainer = ref(null);

const scrollCategories = (direction) => {
    if (!categoryScrollContainer.value) return;
    const scrollAmount = 300;
    if (direction === 'left') {
        categoryScrollContainer.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        categoryScrollContainer.value.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
};

// --- COMPUTED PROPERTIES ---
const userRoleLabel = computed(() => {
    const role = user.value?.role; 
    if (role === 'master') return 'Master';
    if (role === 'admin') return 'Admin';
    if (role === 'kasir') return 'Kasir';
    return 'User';
});

const mainCategories = computed(() => {
    return props.categories.filter(c => !c.parent_id);
});

const getCategoryChildren = (parentId) => {
    return props.categories.filter(c => c.parent_id === parentId);
};

const filteredProducts = computed(() => {
    let result = products.value;

    // 1. Filter by Category (Recursive)
    if (selectedCategory.value) {
        let allowedCategoryIds = [selectedCategory.value];
        const children = props.categories.filter(c => c.parent_id === selectedCategory.value).map(c => c.id);
        allowedCategoryIds = [...allowedCategoryIds, ...children];
        const grandChildren = props.categories.filter(c => children.includes(c.parent_id)).map(c => c.id);
        allowedCategoryIds = [...allowedCategoryIds, ...grandChildren];
        
        result = result.filter(p => allowedCategoryIds.includes(p.category_id));
    }

    // 2. Filter by Search Query
    if (searchQuery.value) {
        const lowerQ = searchQuery.value.toLowerCase();
        result = result.filter(p => 
            p.name.toLowerCase().includes(lowerQ) || 
            (p.barcode && p.barcode.toLowerCase().includes(lowerQ))
        );
    }

    return result;
});

const paginatedProducts = computed(() => {
    return filteredProducts.value.slice(0, visibleCount.value);
});

const hasMoreProducts = computed(() => {
    return visibleCount.value < filteredProducts.value.length;
});

const loadMoreProducts = () => {
    visibleCount.value += itemsPerPage;
};

// Watchers to reset pagination
watch([selectedCategory, searchQuery], () => {
    visibleCount.value = itemsPerPage;
});

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const cartItemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

const changeAmount = computed(() => {
    if (paymentMethod.value === 'cash') {
        return Math.max(0, cashAmount.value - cartTotal.value);
    }
    return 0;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// --- CORE ACTIONS ---
const switchViewMode = (mode) => {
    viewMode.value = mode;
    showNotification(`Mode ${mode === 'express' ? 'EXPRESS' : 'MARKETPLACE'} Aktif`, 'info');
    nextTick(() => document.getElementById('hidden-scanner-input')?.focus());
};

const loadProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('kasir.pos.api.products'));
        products.value = response.data.products;
    } catch (error) {
        showNotification('Gagal memuat produk.', 'error');
    } finally {
        loading.value = false;
    }
};

const addToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id);
    if (existing) {
         if (existing.quantity < product.stock) {
            existing.quantity++;
         } else {
             showNotification(`Stok ${product.name} habis!`, 'error');
             return;
         }
    } else {
        if (product.stock > 0) {
            cart.value.push({ ...product, price: parseFloat(product.harga_jual), quantity: 1 });
        } else {
             showNotification(`Stok ${product.name} habis!`, 'error');
             return;
        }
    }
    if (viewMode.value === 'marketplace') {
        showNotification(`${product.name} +1`, 'success');
    }
};

const decreaseQuantity = (itemOrId) => {
    const id = itemOrId.id || itemOrId;
    const index = cart.value.findIndex(item => item.id === id);
    if (index === -1) return;
    const item = cart.value[index];
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        cart.value.splice(index, 1);
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const clearCart = () => {
    if (viewMode.value === 'express' || confirm('Kosongkan keranjang?')) {
        cart.value = [];
        selectedStudent.value = null;
        paymentMethod.value = 'balance'; // Default to member in purple theme
        cashAmount.value = 0;
        selectedProductFilter.value = null;
    }
};

// --- CHECKOUT ---
const processCheckout = async (auto = false) => {
    if (cart.value.length === 0) return;
    if (auto && paymentMethod.value === 'balance') {
        if (!selectedStudent.value || selectedStudent.value.balance < cartTotal.value) {
            showNotification('Saldo Gagal / Tidak Cukup!', 'error');
            return;
        }
    }
    loading.value = true;
    try {
        const payload = {
            items: cart.value.map(item => ({ product_id: item.id, quantity: item.quantity, price: item.price })),
            payment_method: paymentMethod.value,
            cash_amount: paymentMethod.value === 'cash' ? cashAmount.value : null,
        };
        if (paymentMethod.value === 'balance') {
            payload.buyer_type = 'student';
            payload.buyer_id = selectedStudent.value?.id;
            payload.student_id = selectedStudent.value?.id;
        }
        const response = await axios.post(route('kasir.pos.api.checkout'), payload);
        if (response.data.success) {
            showNotification('Transaksi Berhasil!', 'success');
            if (response.data.receipt_url) {
                const iframe = document.getElementById('receipt-frame');
                if (iframe) iframe.src = response.data.receipt_url;
            }
            cart.value = [];
            selectedStudent.value = null;
            paymentMethod.value = 'balance';
            cashAmount.value = 0;
            selectedProductFilter.value = null;
            if (viewMode.value === 'marketplace') currentTab.value = 'catalog';
            loadRecentSales();
            loadProducts();
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Transaksi Gagal', 'error');
    } finally {
        loading.value = false;
        nextTick(() => document.getElementById('hidden-scanner-input')?.focus());
    }
};

const loadRecentSales = async () => {
    try {
        const response = await axios.get(route('kasir.pos.api.recent-sales'));
        recentSales.value = response.data.sales || [];
    } catch (error) {
        console.error(error);
    }
};

// --- SEARCH & HARDWARE ---
const searchStudents = () => { /* ... (Same logic) ... */ 
    if (searchDebounceTimer.value) clearTimeout(searchDebounceTimer.value);
    if (studentSearchQuery.value.length < 2) { studentSearchResults.value = []; return; }
    searchDebounceTimer.value = setTimeout(async () => {
        searchingStudent.value = true;
        try {
            const response = await axios.get(route('kasir.pos.api.search'), { params: { q: studentSearchQuery.value } });
            studentSearchResults.value = response.data.students || [];
        } catch (error) { showNotification('Error searching', 'error'); } finally { searchingStudent.value = false; }
    }, 300);
};
const selectStudent = (student) => {
    selectedStudent.value = student;
    showStudentModal.value = false;
    paymentMethod.value = 'balance';
    showNotification(`Member: ${student.user.name}`, 'success');
};
const handleRfidScan = async (uid) => {
    try {
        const response = await axios.get(route('kasir.pos.api.rfid', uid));
        if (response.data.success) {
            selectedStudent.value = response.data.student;
            paymentMethod.value = 'balance';
            if (viewMode.value === 'express' && cart.value.length > 0) {
                if (selectedStudent.value.balance >= cartTotal.value) {
                    showNotification(`Processing ${selectedStudent.value.user.name}...`, 'info');
                    await processCheckout(true);
                } else {
                    showNotification('Saldo Kurang!', 'error');
                }
            } else {
                showNotification(`Member: ${selectedStudent.value.user.name}`, 'success');
                if (cart.value.length > 0) currentTab.value = 'cart';
            }
        }
    } catch (error) { showNotification('Kartu Gagal', 'error'); }
};
const handleScannerInput = async () => {
    if (!barcodeInput.value) return;
    const input = barcodeInput.value;
    barcodeInput.value = '';
    if (posMode.value === 'rfid') await handleRfidScan(input);
    else await handleBarcodeScan(input);
    nextTick(() => document.getElementById('hidden-scanner-input')?.focus());
};
const handleBarcodeScan = async (code) => {
    try {
        const response = await axios.get(route('kasir.pos.api.barcode', code));
        if (response.data.success) addToCart(response.data.product);
    } catch (error) { showNotification('Produk 404', 'error'); }
};

const handleCameraScan = async (scannedText) => {
    if (scannerMode.value === 'product') {
        await handleBarcodeScan(scannedText);
    } else if (scannerMode.value === 'student') {
        await handleRfidScan(scannedText);
    }
    showScanner.value = false;
};

const handleGlobalKeydown = (e) => {
     if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
         document.getElementById('hidden-scanner-input')?.focus();
    }
};
const switchMode = (mode) => {
    posMode.value = mode;
    showNotification(`Input: ${mode.toUpperCase()}`, 'info');
    nextTick(() => document.getElementById('hidden-scanner-input')?.focus());
};
const showNotification = (message, type = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};
const getProductQuantityInCart = (productId) => {
    const item = cart.value.find(i => i.id === productId);
    return item ? item.quantity : 0;
};

// --- LIFECYCLE ---
onMounted(() => {
    loadProducts();
    loadRecentSales();
    window.addEventListener('keydown', handleGlobalKeydown);
    setTimeout(() => document.getElementById('hidden-scanner-input')?.focus(), 500);
    // Live Clock
    timeInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});
onUnmounted(() => { 
    window.removeEventListener('keydown', handleGlobalKeydown); 
    if (timeInterval) clearInterval(timeInterval);
});
</script>

<template>
    <Head title="Point of Sale" />

    <!-- 
        LAYOUT STRUKTUR: TOKOPEDIA STYLE (FULL WIDTH)
        Theme: Purple/Indigo Gradient (Restored Consistency)
    -->
    <div class="min-h-screen flex flex-col bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 text-white font-sans relative overflow-x-hidden">
        
        <!-- Animated Background Effects -->
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
             <div class="absolute top-0 -left-4 w-96 h-96 bg-purple-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
             <div class="absolute top-0 -right-4 w-96 h-96 bg-pink-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
             <div class="absolute -bottom-8 left-20 w-96 h-96 bg-indigo-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Hidden Global Elements -->
        <iframe id="receipt-frame" class="hidden"></iframe>
        <input id="hidden-scanner-input" v-model="barcodeInput" @keydown.enter.prevent="handleScannerInput" type="text" class="fixed top-0 left-0 opacity-0 pointer-events-none" autocomplete="off" />

        <!-- Loading & Toast -->
        <LoadingOverlay :show="loading" message="Memproses..." fullscreen />
        <Transition name="slide-down">
            <div v-if="notification.show" :class="['fixed top-24 left-1/2 transform -translate-x-1/2 z-[100] px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border backdrop-blur-md min-w-[320px]', notification.type === 'success' ? 'bg-emerald-500/90 border-emerald-400' : notification.type === 'error' ? 'bg-rose-500/90 border-rose-400' : 'bg-blue-500/90 border-blue-400']">
                <span class="text-xl">{{ notification.type === 'success' ? '✅' : notification.type === 'error' ? '⚠️' : 'ℹ️' }}</span>
                <span class="font-bold text-sm tracking-wide">{{ notification.message }}</span>
            </div>
        </Transition>

        <!-- HEADER (Tokopedia Style - Full Width) -->
        <header class="sticky top-0 z-50 bg-gradient-to-r from-purple-900/95 via-indigo-900/95 to-blue-900/95 border-b border-purple-500/30 shadow-lg backdrop-blur-md">
            <div class="max-w-[1600px] mx-auto px-4 h-20 flex items-center gap-6">
                
                <!-- 1. LEFT: Logo & Home -->
                <div class="flex items-center gap-4 min-w-[200px]">
                    <Link :href="route('dashboard')" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-105 transition-all">
                             <img src="/storage/logos/icon.png" class="w-8 h-8 object-contain drop-shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-lg leading-tight group-hover:text-pink-300 transition-colors">GalaxyPOS</span>
                            <span class="text-[10px] text-purple-300">Koperasi Lemdiklat</span>
                        </div>
                    </Link>
                </div>

                <!-- 2. CENTER: Search (Galaxy Theme Consitent) -->
                <div class="flex-1 max-w-4xl hidden md:flex items-center gap-3">
                     <!-- Category Trigger (Moved to Navbar) -->
                     <button @click="showCategoryModal = true" class="px-3 py-2.5 rounded-xl border border-pink-500/50 bg-pink-900/20 text-pink-300 font-bold whitespace-nowrap hover:bg-pink-600 hover:text-white transition-all shadow-lg hover:shadow-pink-500/30 flex items-center gap-2 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <span class="hidden lg:inline">Kategori</span>
                     </button>

                    <div class="relative group flex-1">
                        <div class="relative w-full transition-all duration-300 hover:scale-[1.02]">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="block w-full pl-10 pr-12 py-2.5 border border-purple-500/30 rounded-xl leading-5 bg-white/10 text-white placeholder-purple-200/50 focus:outline-none focus:bg-white/20 focus:ring-2 focus:ring-purple-500 sm:text-sm transition-all shadow-lg backdrop-blur-sm placeholder:italic"
                                placeholder="Cari produk (Nama / Barcode)..."
                            />
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                                <button @click="scannerMode = 'product'; showScanner = true" class="p-1.5 bg-pink-600/20 text-pink-400 hover:bg-pink-600 hover:text-white rounded-lg transition-colors border border-pink-500/30" title="Scan Barcode via Kamera">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. RIGHT: Actions & Profile -->
                <div class="flex items-center gap-4 ml-auto">
                    <!-- Home Button -->
                    <Link :href="route('dashboard')" class="hidden xl:flex px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-sm font-bold items-center gap-2 border border-white/5 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Dashboard
                    </Link>

                    <!-- Mode Switch -->
                    <div class="hidden lg:flex bg-black/20 p-1 rounded-lg border border-purple-500/30">
                        <button @click="switchViewMode('marketplace')" :class="['px-4 py-1.5 rounded textxs font-bold uppercase tracking-wider transition-all', viewMode === 'marketplace' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-purple-300 hover:text-white']">Market</button>
                        <button @click="switchViewMode('express')" :class="['px-4 py-1.5 rounded text-xs font-bold uppercase tracking-wider transition-all', viewMode === 'express' ? 'bg-gradient-to-r from-red-600 to-orange-600 text-white shadow-lg' : 'text-purple-300 hover:text-white']">Express</button>
                    </div>

                    <div class="h-8 w-px bg-purple-500/30"></div>

                    <!-- Cart -->
                    <button @click="currentTab = 'cart'; viewMode = 'marketplace'" class="relative p-2 rounded-lg hover:bg-white/10 transition-colors group">
                        <svg class="w-7 h-7 text-purple-200 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-pink-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-purple-900 shadow-sm">{{ cartItemCount }}</span>
                    </button>

                    <!-- User Profile -->
                    <button class="flex items-center gap-3 hover:bg-white/10 pl-2 pr-4 py-1.5 rounded-full transition-colors border border-transparent hover:border-purple-500/30">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold shadow-md ring-2 ring-purple-500/30">{{ user.name.substring(0,2).toUpperCase() }}</div>
                        <div class="hidden xl:flex flex-col items-start">
                            <span class="text-sm font-bold text-white leading-tight truncate max-w-[120px]">{{ user.name }}</span>
                            <span class="text-[10px] text-purple-300 leading-tight">{{ userRoleLabel }}</span>
                        </div>
                    </button>
                </div>
            </div>
            
             <!-- Mobile Sub-Header -->
            <div v-if="viewMode === 'marketplace'" class="md:hidden flex border-t border-purple-500/30 bg-purple-900/50">
                <button v-for="tab in ['catalog', 'cart', 'transactions']" :key="tab" @click="currentTab = tab" :class="['flex-1 py-3 text-xs font-bold uppercase tracking-widest text-center', currentTab === tab ? 'text-pink-400 border-b-2 border-pink-500' : 'text-purple-300']">
                    {{ tab }}
                </button>
            </div>
        </header>

        <!-- Tokopedia-style Mega Menu Dropdown -->
        <div v-show="showCategoryModal" class="fixed inset-0 z-40 flex flex-col" style="top: 80px;"> <!-- Top 80px to sit below header -->
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCategoryModal = false"></div>
            
            <!-- Dropdown Content -->
            <div class="relative w-full bg-[#1e1b4b] border-b border-purple-500/30 shadow-2xl flex max-h-[70vh] animate-slide-down origin-top">
                <!-- Left: Sidebar (Main Categories) -->
                <div class="w-64 bg-black/20 border-r border-white/10 overflow-y-auto custom-scrollbar flex flex-col py-2">
                    <button 
                        @click="selectedCategory = ''; showCategoryModal = false"
                        class="text-left px-6 py-3 font-bold text-white hover:bg-white/10 transition-colors border-l-4"
                        :class="!activeMegaMenuCategory && !selectedCategory ? 'border-pink-500 bg-white/5' : 'border-transparent'"
                        @mouseenter="activeMegaMenuCategory = null"
                    >
                        Semua Kategori
                    </button>
                    <button 
                        v-for="cat in mainCategories" 
                        :key="cat.id"
                        @mouseenter="activeMegaMenuCategory = cat.id"
                        class="text-left px-6 py-3 font-medium text-purple-200 hover:text-white hover:bg-white/10 transition-colors border-l-4 flex justify-between items-center group"
                        :class="activeMegaMenuCategory === cat.id ? 'border-pink-500 bg-white/5 text-white' : 'border-transparent'"
                    >
                        {{ cat.name }}
                        <svg v-if="activeMegaMenuCategory === cat.id" class="w-4 h-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                <!-- Right: Content (Subcategories) -->
                <div class="flex-1 bg-[#1e1b4b] p-8 overflow-y-auto custom-scrollbar">
                    <!-- All Categories View -->
                    <div v-if="!activeMegaMenuCategory" class="flex flex-col items-center justify-center h-full opacity-50">
                        <span class="text-6xl mb-4">🛍️</span>
                        <p class="text-xl font-bold text-white">Jelajahi Semua Kategori</p>
                        <p class="text-purple-300">Pilih kategori di sebelah kiri untuk melihat sub-kategori.</p>
                    </div>

                    <!-- Specific Category View -->
                    <div v-else class="animate-fade-in">
                        <div class="flex items-center gap-4 mb-6 pb-4 border-b border-white/10">
                            <h3 class="text-2xl font-bold text-white">{{ mainCategories.find(c => c.id === activeMegaMenuCategory)?.name }}</h3>
                            <button 
                                @click="selectedCategory = activeMegaMenuCategory; showCategoryModal = false"
                                class="px-4 py-1.5 rounded-lg bg-pink-600/20 text-pink-300 text-sm font-bold hover:bg-pink-600 hover:text-white transition-colors border border-pink-500/30"
                            >
                                Lihat Semua Produk
                            </button>
                        </div>
                        
                        <div class="columns-2 md:columns-3 xl:columns-4 gap-8 space-y-8">
                            <!-- Group Subcategories -->
                            <div class="break-inside-avoid">
                                <ul class="space-y-3">
                                    <li v-for="sub in getCategoryChildren(activeMegaMenuCategory)" :key="sub.id">
                                        <button 
                                            @click="selectedCategory = sub.id; showCategoryModal = false"
                                            class="text-purple-300 hover:text-pink-400 hover:translate-x-1 transition-all text-sm flex items-center gap-2"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            {{ sub.name }}
                                        </button>
                                    </li>
                                    <li v-if="getCategoryChildren(activeMegaMenuCategory).length === 0" class="text-white/30 italic text-sm">
                                        Tidak ada sub-kategori
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BODY CONTENT -->
        <main class="flex-1 flex flex-col relative z-10 w-full max-w-[1600px] mx-auto pt-6">
            
            <!-- VIEW MODE: MARKETPLACE -->
            <div v-if="viewMode === 'marketplace'" class="flex-1 flex flex-col w-full px-4 lg:px-6">
                <!-- Desktop Tabs -->
                <div class="hidden md:flex border-b border-purple-500/30 mb-6">
                    <button @click="currentTab = 'catalog'" :class="['py-4 px-8 text-sm font-bold border-b-2 transition-colors relative', currentTab === 'catalog' ? 'border-pink-500 text-pink-400' : 'border-transparent text-purple-300 hover:text-white']">
                        Belanja
                        <span v-if="currentTab === 'catalog'" class="absolute bottom-0 left-0 w-full h-full bg-pink-500/5 -z-10 rounded-t-lg"></span>
                    </button>
                    <button @click="currentTab = 'cart'" :class="['py-4 px-8 text-sm font-bold border-b-2 transition-colors relative', currentTab === 'cart' ? 'border-pink-500 text-pink-400' : 'border-transparent text-purple-300 hover:text-white']">
                        Keranjang <span v-if="cartItemCount > 0" class="ml-2 bg-pink-500/20 text-pink-300 px-2 py-0.5 rounded-full text-xs">{{ cartItemCount }}</span>
                         <span v-if="currentTab === 'cart'" class="absolute bottom-0 left-0 w-full h-full bg-pink-500/5 -z-10 rounded-t-lg"></span>
                    </button>
                    <button @click="currentTab = 'transactions'" :class="['py-4 px-8 text-sm font-bold border-b-2 transition-colors relative', currentTab === 'transactions' ? 'border-pink-500 text-pink-400' : 'border-transparent text-purple-300 hover:text-white']">
                        Status Transaksi
                         <span v-if="currentTab === 'transactions'" class="absolute bottom-0 left-0 w-full h-full bg-pink-500/5 -z-10 rounded-t-lg"></span>
                    </button>
                </div>

                <!-- Content Area -->
                <div class="flex-1 pb-12">
                    <!-- CATALOG -->
                    <div v-if="currentTab === 'catalog'">
                        <!-- Categories with Navigation -->
                        <div class="relative group px-10 mb-6">
                            <!-- Left Arrow -->
                            <button @click="scrollCategories('left')" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/40 hover:bg-pink-600 text-white flex items-center justify-center backdrop-blur-sm border border-white/10 transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>

                            <!-- Container -->
                            <div ref="categoryScrollContainer" class="flex gap-3 overflow-x-auto pb-4 no-scrollbar scroll-smooth pl-1 pr-1">
                                 <button @click="selectedCategory = ''" :class="['px-5 py-2.5 rounded-xl border text-sm font-bold whitespace-nowrap transition-all shadow-lg hover:shadow-purple-500/20 flex-shrink-0', !selectedCategory ? 'bg-gradient-to-r from-purple-600 to-pink-600 border-purple-500 text-white transform scale-105' : 'bg-white/5 border-purple-500/30 text-purple-200 hover:bg-purple-800/50 hover:text-white']">Semua</button>
                                 <button v-for="cat in mainCategories" :key="cat.id" @click="selectedCategory = cat.id" :class="['px-5 py-2.5 rounded-xl border text-sm font-bold whitespace-nowrap transition-all shadow-lg hover:shadow-purple-500/20 flex-shrink-0', selectedCategory == cat.id ? 'bg-gradient-to-r from-purple-600 to-pink-600 border-purple-500 text-white transform scale-105' : 'bg-white/5 border-purple-500/30 text-purple-200 hover:bg-purple-800/50 hover:text-white']">{{ cat.name }}</button>
                            </div>

                            <!-- Right Arrow -->
                            <button @click="scrollCategories('right')" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/40 hover:bg-pink-600 text-white flex items-center justify-center backdrop-blur-sm border border-white/10 transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                        
                        <!-- Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                             <div v-for="product in paginatedProducts" :key="product.id" class="group bg-white/5 backdrop-blur-sm border border-purple-500/20 rounded-2xl overflow-hidden hover:border-pink-500/50 transition-all hover:shadow-2xl hover:shadow-purple-500/20 flex flex-col hover:-translate-y-1">
                                <div class="aspect-square bg-black/40 relative overflow-hidden">
                                    <img v-if="product.image_path" :src="`/storage/${product.image_path}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-5xl opacity-50">📦</div>
                                    <!-- Stock Badge -->
                                    <div class="absolute top-2 right-2 px-2 py-1 rounded-md text-[10px] font-bold backdrop-blur-md border border-white/10" :class="product.stock > 10 ? 'bg-emerald-500/80 text-white' : 'bg-rose-500/80 text-white'">
                                        {{ product.stock }} Unit
                                    </div>
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="text-sm font-bold text-white line-clamp-2 mb-1 h-10 leading-tight group-hover:text-pink-300 transition-colors">{{ product.name }}</h3>
                                    <p class="text-[10px] text-purple-300 mb-3 tracking-wide uppercase">{{ product.category ? product.category.name : 'Umum' }}</p>
                                    <div class="mt-auto flex items-center justify-between">
                                        <p class="text-pink-400 font-extrabold text-lg whitespace-nowrap">{{ formatCurrency(product.harga_jual) }}</p>
                                        <!-- Actions -->
                                        <div v-if="getProductQuantityInCart(product.id) > 0" class="flex items-center bg-purple-900 rounded-lg p-1 border border-purple-500/50 shadow-inner">
                                            <button @click="decreaseQuantity(product)" class="w-7 h-7 flex items-center justify-center bg-purple-800 hover:bg-red-500/80 rounded-md text-sm transition-colors">-</button>
                                            <span class="w-8 text-center text-sm font-bold">{{ getProductQuantityInCart(product.id) }}</span>
                                            <button @click="addToCart(product)" class="w-7 h-7 flex items-center justify-center bg-pink-600 hover:bg-pink-500 rounded-md text-sm text-white transition-colors">+</button>
                                        </div>
                                        <button v-else @click="addToCart(product)" class="w-10 h-10 rounded-xl bg-pink-600/10 text-pink-400 hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 hover:text-white flex items-center justify-center transition-all border border-pink-500/20 hover:border-transparent hover:shadow-lg hover:shadow-pink-500/40">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        </button>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- CART -->
                    <div v-if="currentTab === 'cart'" class="max-w-5xl mx-auto">
                         <!-- (Cart Layout matches previous purple logic but wider) -->
                         <div class="flex flex-col lg:flex-row gap-8">
                             <!-- Items -->
                            <div class="flex-1 bg-white/5 backdrop-blur-xl rounded-3xl border border-purple-500/30 p-6 shadow-2xl">
                                <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                                    <span>🛒</span> Keranjang Belanja <span class="text-sm font-normal text-purple-300 ml-auto">{{ cartItemCount }} Items</span>
                                </h2>
                                <div v-if="cart.length === 0" class="text-center py-20 text-purple-300 border-2 border-dashed border-purple-500/20 rounded-2xl">
                                    <div class="text-6xl mb-4 opacity-50">🛍️</div>
                                    <p class="mb-4 text-lg">Keranjang masih kosong</p>
                                    <button @click="currentTab = 'catalog'" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl text-white font-bold shadow-lg hover:shadow-pink-500/30 transition-all transform hover:scale-105">Mulai Belanja</button>
                                </div>
                                <div v-else class="space-y-6">
                                    <div v-for="(item, index) in cart" :key="index" class="flex gap-6 items-center pb-6 border-b border-purple-500/20 last:border-0 group">
                                        <div class="w-24 h-24 bg-white rounded-xl overflow-hidden flex-shrink-0 border-2 border-transparent group-hover:border-pink-500/50 transition-colors">
                                             <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-lg text-white group-hover:text-pink-300 transition-colors">{{ item.name }}</h4>
                                            <p class="text-sm text-purple-300">{{ item.code }}</p>
                                            <p class="text-pink-400 font-bold mt-1 text-lg">{{ formatCurrency(item.price) }}</p>
                                        </div>
                                        <div class="flex items-center gap-3 bg-black/20 rounded-xl p-1.5 border border-white/5">
                                            <button @click="decreaseQuantity(item)" class="w-9 h-9 flex items-center justify-center bg-purple-900 hover:bg-purple-800 rounded-lg text-white transition-colors">-</button>
                                            <span class="w-8 text-center font-bold text-lg">{{ item.quantity }}</span>
                                            <button @click="addToCart(item)" class="w-9 h-9 flex items-center justify-center bg-pink-600 hover:bg-pink-500 rounded-lg text-white transition-colors">+</button>
                                        </div>
                                        <button @click="removeFromCart(index)" class="p-2 text-rose-400 hover:text-rose-200 hover:bg-rose-500/20 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Summary -->
                            <div class="lg:w-96 space-y-4">
                                <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-purple-500/30 p-6 sticky top-24 shadow-2xl">
                                    <h3 class="font-bold text-xl mb-6 text-white pb-4 border-b border-purple-500/20">Ringkasan Pesanan</h3>
                                    
                                    <div class="space-y-3 mb-6">
                                        <div class="flex justify-between text-purple-200"><span>Subtotal Produk</span><span>{{ formatCurrency(cartTotal) }}</span></div>
                                        <div class="flex justify-between text-purple-200"><span>Pajak (0%)</span><span>Rp 0</span></div>
                                        <div class="flex justify-between text-purple-200"><span>Diskon</span><span>-Rp 0</span></div>
                                    </div>
                                    
                                    <div class="bg-black/20 rounded-xl p-4 mb-6 border border-purple-500/10">
                                         <div class="flex justify-between items-end">
                                             <span class="text-sm text-purple-300 mb-1">Total Tagihan</span>
                                             <span class="text-2xl font-bold text-white">{{ formatCurrency(cartTotal) }}</span>
                                         </div>
                                    </div>
                                    
                                    <!-- Payment Mode -->
                                    <div class="space-y-2 mb-6">
                                        <p class="text-xs font-bold uppercase tracking-wider text-purple-400 ml-1">Metode Pembayaran</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button @click="paymentMethod = 'cash'" :class="['py-3 rounded-xl font-bold text-sm border-2 transition-all flex flex-col items-center gap-1', paymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400' : 'border-purple-500/20 bg-purple-900/20 text-purple-400 hover:bg-purple-900/40']">
                                                <span>💵</span> Tunai
                                            </button>
                                            <button @click="paymentMethod = 'balance'" :class="['py-3 rounded-xl font-bold text-sm border-2 transition-all flex flex-col items-center gap-1', paymentMethod === 'balance' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-purple-500/20 bg-purple-900/20 text-purple-400 hover:bg-purple-900/40']">
                                                <span>💳</span> Member
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Inputs -->
                                    <div v-if="paymentMethod === 'cash'" class="mb-6 animate-fade-in-up">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-purple-400">Rp</span>
                                            <input v-model="cashAmount" type="number" class="w-full bg-black/30 border-purple-500/30 rounded-xl pl-10 pr-4 py-3 text-white font-bold focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none">
                                        </div>
                                        <div class="flex justify-between text-sm mt-3 px-1">
                                            <span class="text-purple-300">Kembalian</span>
                                            <span class="font-bold text-lg" :class="changeAmount >= 0 ? 'text-emerald-400' : 'text-rose-400'">{{ formatCurrency(changeAmount) }}</span>
                                        </div>
                                    </div>

                                    <div v-if="paymentMethod === 'balance'" class="mb-6 animate-fade-in-up">
                                        <div v-if="selectedStudent" class="bg-blue-900/20 p-3 rounded-xl border border-blue-500/30 flex justify-between items-center group cursor-pointer hover:bg-blue-900/30 transition-colors" @click="showStudentModal=true">
                                            <div>
                                                <p class="font-bold text-blue-200">{{ selectedStudent.user.name }}</p>
                                                <p class="text-xs text-blue-400">Saldo: {{ formatCurrency(selectedStudent.balance) }}</p>
                                            </div>
                                            <span class="text-blue-500">Change ></span>
                                        </div>
                                        <div v-else class="flex gap-2">
                                            <button @click="showStudentModal = true" class="flex-1 py-3 bg-blue-600/10 text-blue-300 border border-blue-500/30 rounded-xl font-bold text-sm hover:bg-blue-600/20 border-dashed transition-all">
                                                + Pilih Member
                                            </button>
                                            <button @click="scannerMode = 'student'; showScanner = true" class="w-12 flex items-center justify-center bg-blue-600/20 text-blue-400 border border-blue-500/50 rounded-xl hover:bg-blue-600 hover:text-white transition-all transform hover:scale-105" title="Scan QR Kartu Member">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <button @click="processCheckout(false)" :disabled="cart.length === 0" class="w-full py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-purple-500/20 transform hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                        PROSES PEMBAYARAN
                                    </button>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- TRANSACTIONS -->
                     <div v-if="currentTab === 'transactions'" class="max-w-4xl mx-auto px-4">
                         <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-white flex items-center gap-2"><span>📜</span> Riwayat Transaksi</h2>
                            <div class="flex gap-2 bg-black/20 p-1 rounded-xl border border-purple-500/30">
                                <span class="px-4 py-2 bg-purple-600 text-white rounded-lg text-xs font-bold shadow">24 Jam Terakhir</span>
                                <Link :href="route('kasir.pos.transactions-history')" class="px-4 py-2 text-purple-300 hover:text-white rounded-lg text-xs font-bold transition-colors">
                                    Lihat Semua Riwayat &rarr;
                                </Link>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div v-if="recentSales.length === 0" class="text-center py-20 text-purple-300 bg-white/5 rounded-2xl border border-dashed border-purple-500/30">
                                Belum ada transaksi hari ini.
                            </div>
                            <div v-for="sale in recentSales" :key="sale.id" class="flex justify-between items-center bg-white/5 p-5 rounded-2xl border border-purple-500/20 hover:bg-white/10 transition-all hover:border-pink-500/30 hover:transform hover:translate-x-1">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-purple-900/50 flex items-center justify-center text-xl">
                                        {{ sale.payment_method === 'cash' ? '💵' : '💳' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-pink-300 text-lg">Invoice #{{ sale.id }}</p>
                                        <p class="text-xs text-purple-200">{{ new Date(sale.created_at).toLocaleString() }} • <span class="uppercase font-bold tracking-wide">{{ sale.payment_method }}</span></p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-6">
                                     <div class="flex flex-col items-end">
                                         <span class="font-bold text-white text-xl">{{ formatCurrency(sale.total) }}</span>
                                         <span class="text-xs text-purple-400">{{ sale.student ? sale.student.user.name : 'Guest Customer' }}</span>
                                     </div>
                                     <a :href="`/kasir/receipt/${sale.id}`" target="_blank" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-pink-600 hover:text-white flex items-center justify-center transition-all border border-white/5 hover:shadow-lg" title="Cetak Struk">
                                         <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                     </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW MODE: EXPRESS (Full Width) -->
            <div v-if="viewMode === 'express'" class="flex-1 flex overflow-hidden border-t border-purple-500/30 bg-black/40 backdrop-blur-md">
                 <!-- Left: Table -->
                <div class="w-3/4 border-r border-purple-500/30 flex flex-col p-6">
                     <div class="flex justify-between mb-4 font-mono text-pink-400 items-end border-b border-purple-500/30 pb-4">
                         <div>
                             <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">EXPRESS TERMINAL</h1>
                             <span class="text-xs text-purple-400 tracking-[0.3em]">HIGH VELOCITY MODE</span>
                         </div>
                         <div class="text-right">
                             <span class="block text-2xl font-bold text-white">{{ currentTime.toLocaleTimeString() }}</span>
                             <span class="text-xs text-emerald-500 font-bold">● SYSTEM ONLINE</span>
                         </div>
                     </div>
                     <!-- Table Header -->
                     <div class="flex bg-purple-900/40 text-purple-200 font-bold text-sm py-3 px-6 uppercase tracking-wider rounded-lg mb-2">
                         <div class="w-16">No</div><div class="flex-1">Item Description</div><div class="w-32 text-right">Qty</div><div class="w-48 text-right">Total</div><div class="w-12"></div>
                     </div>
                     <!-- Rows -->
                     <div class="flex-1 overflow-y-auto font-mono bg-black/20 rounded-lg border border-purple-500/10">
                         <div v-for="(item, index) in cart" :key="index" class="flex items-center py-3 px-6 border-b border-purple-500/10 hover:bg-white/5 text-lg transition-colors group">
                             <div class="w-16 text-purple-500 font-bold">{{ index+1 }}</div>
                             <div class="flex-1 text-white truncate px-2 font-bold group-hover:text-pink-300">{{ item.name }}</div>
                             <div class="w-32 text-right text-emerald-400 font-bold bg-emerald-900/10 rounded px-2">{{ item.quantity }}</div>
                             <div class="w-48 text-right text-pink-400 font-bold">{{ formatCurrency(item.price * item.quantity) }}</div>
                             <button @click="removeFromCart(index)" class="w-12 text-rose-500 hover:text-white flex justify-end opacity-50 group-hover:opacity-100 transition-opacity">✕</button>
                         </div>
                     </div>
                </div>
                <!-- Right: Panel -->
                <div class="w-1/4 bg-gradient-to-b from-purple-900/80 to-indigo-900/80 flex flex-col p-6 shadow-2xl z-20 backdrop-blur-xl">
                    <div class="bg-black/60 rounded-2xl p-6 mb-6 text-right border border-purple-500/30 shadow-inner">
                        <span class="block text-purple-400 text-sm font-bold uppercase mb-2">Total Amount</span>
                        <h2 class="text-5xl font-mono text-white font-bold tracking-tighter shadow-purple-500/50 drop-shadow-lg">{{ formatCurrency(cartTotal) }}</h2>
                    </div>
                    <div class="flex-1 flex flex-col items-center justify-center bg-purple-800/20 rounded-2xl border-2 border-dashed border-purple-500/30 relative overflow-hidden group mb-6">
                         <div class="absolute inset-0 bg-pink-500/5 group-hover:bg-pink-500/10 transition-colors"></div>
                         <div class="relative z-10 flex flex-col items-center">
                             <div class="w-24 h-24 rounded-full bg-purple-500/20 flex items-center justify-center mb-4 animate-pulse">
                                 <span class="text-6xl">💳</span>
                             </div>
                             <p class="font-bold text-lg text-white">SCAN CARD</p>
                             <p class="font-mono text-xs text-center text-purple-300 uppercase tracking-widest mt-1">RFID Auto-Payment Ready</p>
                         </div>
                         <!-- Scanner visualizer line -->
                         <div class="absolute top-0 left-0 w-full h-1 bg-pink-500 shadow-[0_0_15px_rgba(236,72,153,0.8)] animate-scan opacity-50"></div>
                    </div>
                    <div class="mt-auto grid gap-3">
                         <button @click="clearCart" class="py-4 bg-red-900/40 text-red-300 hover:bg-red-600 hover:text-white rounded-xl font-mono font-bold transition-colors border border-red-500/20">CLEAR TERMINAL</button>
                         <button @click="processCheckout(false)" class="py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-mono font-bold transition-colors shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                             <span>💵</span> PROCESS CASH (F2)
                         </button>
                    </div>
                </div>
            </div>

        </main>

        <!-- FOOTER (Tokopedia Style) -->
        <footer v-if="viewMode === 'marketplace'" class="border-t border-purple-500/20 bg-purple-900/80 backdrop-blur-md py-8 mt-auto relative z-20">
            <div class="max-w-[1600px] mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-purple-300">
                <div class="flex items-center gap-2">
                     <span class="font-bold text-white">GalaxyPOS</span> &copy; 2026 Koperasi Lemdiklat. 
                </div>
                <div class="flex gap-8 font-medium">
                    <Link :href="route('dashboard')" class="hover:text-pink-400 transition-colors">Dashboard</Link>
                    <a href="#" class="hover:text-pink-400 transition-colors">Pusat Bantuan</a>
                    <a href="#" class="hover:text-pink-400 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </footer>

        <!-- MODALS -->
        <div v-if="showStudentModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
             <div class="bg-gradient-to-br from-purple-900 to-indigo-900 border border-purple-400/30 rounded-2xl w-full max-w-lg shadow-2xl p-6">
                 <div class="flex justify-between items-center mb-6"><h3 class="font-bold text-xl text-white">Cari Member / Siswa</h3><button @click="showStudentModal=false" class="text-purple-300 hover:text-white text-xl">✕</button></div>
                 <input v-model="studentSearchQuery" @input="searchStudents" type="text" placeholder="Ketik Nama atau NIS..." class="w-full bg-purple-900/50 border-purple-500/30 rounded-xl text-white mb-4 px-5 py-3 focus:ring-2 focus:ring-pink-500 outline-none text-lg">
                 <div class="max-h-60 overflow-y-auto space-y-2 custom-scrollbar pr-2">
                     <div v-if="searchingStudent" class="text-purple-300 text-center py-6 animate-pulse">Mencari...</div>
                     <button v-for="s in studentSearchResults" :key="s.id" @click="selectStudent(s)" class="w-full text-left p-4 hover:bg-purple-800/50 rounded-xl border border-purple-500/10 hover:border-pink-500/50 flex justify-between items-center transition-all group">
                         <div>
                             <p class="text-white font-bold group-hover:text-pink-200">{{ s.user.name }}</p>
                             <p class="text-xs text-purple-400">{{ s.nis }}</p>
                         </div>
                         <span class="font-mono text-emerald-400 font-bold bg-emerald-900/20 px-2 py-1 rounded">{{ formatCurrency(s.balance) }}</span>
                     </button>
                 </div>
             </div>
        </div>

    </div>

    <!-- Camera Scanner Component -->
    <CameraScanner 
        :show="showScanner" 
        :title="scannerMode === 'product' ? 'Scan Barcode Produk' : 'Scan QR Kartu Member'" 
        @close="showScanner = false" 
        @scan="handleCameraScan" 
    />
</template>

<style scoped>
/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(168, 85, 247, 0.4);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(236, 72, 153, 0.6);
}

@keyframes slide-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down {
    animation: slide-down 0.2s ease-out forwards;
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-fade-in {
    animation: fade-in 0.2s ease-out forwards;
}

.no-scrollbar::-webkit-scrollbar { display: none; }

/* Galaxy Search Bar (Consistent Dark/Glass Theme) */
/* Replaced with standard input classes in template */

@keyframes blob { 0% { transform: translate(0px, 0px) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-20px, 20px) scale(0.9); } 100% { transform: translate(0px, 0px) scale(1); } }
.animate-blob { animation: blob 10s infinite; }
.animation-delay-2000 { animation-delay: 2s; }
.animation-delay-4000 { animation-delay: 4s; }

@keyframes scan { 0% { top: 0; } 50% { top: 100%; } 100% { top: 0; } }
.animate-scan { animation: scan 3s linear infinite; }
</style>
