<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LoadingOverlay from '@/Components/LoadingOverlay.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    categories: Array,
});

// State
const products = ref([]);
const cart = ref([]);
const searchQuery = ref('');
const selectedCategory = ref('');
const barcodeInput = ref('');
const rfidInput = ref('');
const selectedStudent = ref(null);
const showCheckoutModal = ref(false);
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
const showRecentSales = ref(false);

// POS Mode State
const posMode = ref('manual'); // 'manual', 'rfid', 'barcode'
const showInstructions = ref(true);

// Computed
const filteredProducts = computed(() => {
    return products.value.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = !selectedCategory.value || product.category_id == selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
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

// Methods
const loadProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('kasir.pos.api.products'));
        products.value = response.data.products;
    } catch (error) {
        showNotification('Gagal memuat produk. Silakan refresh halaman.', 'error');
        console.error('Load products error:', error);
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
            showNotification(`Stok tidak cukup for ${product.name}`, 'error');
        }
    } else {
        if (product.stock > 0) {
            cart.value.push({
                ...product,
                price: parseFloat(product.harga_jual),
                quantity: 1
            });
        } else {
            showNotification(`Stok habis for ${product.name}`, 'error');
        }
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateQuantity = (index, quantity) => {
    const item = cart.value[index];
    if (quantity < 1) return;
    if (quantity > item.stock) {
        showNotification(`Stok hanya tersedia ${item.stock}`, 'error');
        return;
    }
    item.quantity = quantity;
};

const clearCart = () => {
    cart.value = [];
    selectedStudent.value = null;
    paymentMethod.value = 'cash';
    cashAmount.value = 0;
};

const switchMode = (mode) => {
    posMode.value = mode;
    setTimeout(() => {
        const input = document.getElementById('hidden-scanner-input');
        if (input) input.focus();
    }, 100);
};

const getModeInstructions = () => {
    switch (posMode.value) {
        case 'rfid':
            return {
                title: 'RFID Scanner Mode',
                color: 'from-blue-500 to-indigo-600',
                steps: [
                    { icon: '💳', text: 'Pastikan kursor aktif di input scanner (otomatis).' },
                    { icon: '📡', text: 'Tap kartu RFID pada reader.' },
                    { icon: '✅', text: 'Siswa akan otomatis terdeteksi.' }
                ]
            };
        case 'barcode':
            return {
                title: 'Barcode Scanner Mode',
                color: 'from-purple-500 to-pink-600',
                steps: [
                    { icon: '🔫', text: 'Arahkan scanner ke barcode produk.' },
                    { icon: '📦', text: 'Produk akan otomatis masuk keranjang.' },
                    { icon: '⌨️', text: 'Gunakan keyboard untuk ubah jumlah.' }
                ]
            };
        default:
            return {
                title: 'Manual Entry Mode',
                color: 'from-green-500 to-emerald-600',
                steps: [
                    { icon: '🔍', text: 'Cari produk menggunakan kolom pencarian.' },
                    { icon: '👆', text: 'Klik produk untuk menambah ke keranjang.' },
                    { icon: '👤', text: 'Pilih siswa secara manual jika perlu.' }
                ]
            };
    }
};

const searchStudents = () => {
    if (searchDebounceTimer.value) clearTimeout(searchDebounceTimer.value);
    
    if (studentSearchQuery.value.length < 2) {
        studentSearchResults.value = [];
        return;
    }

    searchDebounceTimer.value = setTimeout(async () => {
        searchingStudent.value = true;
        try {
            const response = await axios.get(route('kasir.pos.api.search'), {
                params: { q: studentSearchQuery.value }
            });
            studentSearchResults.value = response.data.students || [];
        } catch (error) {
            showNotification('Gagal mencari siswa', 'error');
        } finally {
            searchingStudent.value = false;
        }
    }, 300);
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    showStudentModal.value = false;
    studentSearchQuery.value = '';
    studentSearchResults.value = [];
    showNotification(`Siswa dipilih: ${student.user.name}`, 'success');
};

const openStudentSearch = () => {
    showStudentModal.value = true;
    setTimeout(() => {
        const input = document.querySelector('input[placeholder*="Cari siswa"]');
        if (input) input.focus();
    }, 100);
};

const openCheckout = () => {
    showCheckoutModal.value = true;
};

const processCheckout = async () => {
    if (cart.value.length === 0) return;
    
    loading.value = true;
    try {
        const payload = {
            items: cart.value.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
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
            showNotification('Transaksi berhasil!', 'success');
            clearCart();
            showCheckoutModal.value = false;
            loadRecentSales();
            loadProducts(); // Refresh stock
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Gagal memproses transaksi', 'error');
    } finally {
        loading.value = false;
    }
};

const loadRecentSales = async () => {
    try {
        const response = await axios.get(route('kasir.pos.api.recent-sales'));
        recentSales.value = response.data.sales || [];
    } catch (error) {
        console.error('Gagal memuat transaksi terbaru:', error);
    }
};

const voidTransaction = async (sale) => {
    if (!confirm(`Batalkan transaksi #${sale.id}? Stok akan dikembalikan.`)) return;

    loading.value = true;
    try {
        const response = await axios.post(route('kasir.pos.api.void', sale.id));

        if (response.data.success) {
            showNotification('Transaksi berhasil dibatalkan', 'success');
            loadRecentSales(); 
            loadProducts(); 
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Gagal membatalkan transaksi', 'error');
    } finally {
        loading.value = false;
    }
};

const handleScannerInput = async () => {
    if (!barcodeInput.value) return;
    
    // Determine if it's likely a barcode or RFID based on length/format if possible, 
    // or just use current mode.
    // Here we use current mode.
    
    const input = barcodeInput.value;
    barcodeInput.value = ''; // Clear immediately

    if (posMode.value === 'rfid') {
        await handleRfidScan(input);
    } else {
        await handleBarcodeScan(input);
    }
    
    // Refocus
    document.getElementById('hidden-scanner-input')?.focus();
};

const handleBarcodeScan = async (code) => {
    try {
        const response = await axios.get(route('kasir.pos.api.barcode', code));
        if (response.data.success) {
            addToCart(response.data.product);
            showNotification(`${response.data.product.name} ditambahkan`, 'success');
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Produk tidak ditemukan', 'error');
    }
};

const handleRfidScan = async (uid) => {
    try {
        const response = await axios.get(route('kasir.pos.api.rfid', uid));
        if (response.data.success) {
            selectedStudent.value = response.data.student;
            paymentMethod.value = 'balance';
            showNotification(`Siswa: ${response.data.student.user.name}`, 'success');
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Kartu RFID tidak terdaftar', 'error');
    }
};

const showNotification = (message, type = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const toggleRecentSales = () => {
    showRecentSales.value = !showRecentSales.value;
    if (showRecentSales.value) {
        loadRecentSales();
    }
};

const handleGlobalKeydown = (e) => {
    // Shortcuts
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        openCheckout();
    }
    // Focus scanner
    if (posMode.value !== 'manual' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
        document.getElementById('hidden-scanner-input')?.focus();
    }
};

onMounted(() => {
    loadProducts();
    window.addEventListener('keydown', handleGlobalKeydown);
    // Auto focus scanner if mode is not manual
    if (posMode.value !== 'manual') {
        setTimeout(() => document.getElementById('hidden-scanner-input')?.focus(), 500);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>

<template>
    <Head title="POS / Kasir" />

    <AuthenticatedLayout>
        <template #mobileTitle>Kasir</template>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">POS / Kasir</h2>
                <div class="flex gap-2">
                    <Link
                        :href="route(route().current('pos.index') ? 'pos.transactions-history' : 'kasir.pos.transactions-history')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Lengkap
                    </Link>
                    <button
                        @click="toggleRecentSales"
                        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ showRecentSales ? 'Tutup' : '24 Jam Terakhir' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <!-- Loading Overlay -->
            <LoadingOverlay
                :show="loading"
                :message="paymentMethod === 'balance' ? 'Memproses transaksi...' : 'Memproses pembayaran tunai...'"
                fullscreen
            />

            <!-- Notification -->
            <Transition name="slide-fade">
                <div v-if="notification.show" :class="[
                    'fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 max-w-md',
                    notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                ]">
                    <svg v-if="notification.type === 'success'" class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="flex-1">{{ notification.message }}</span>
                </div>
            </Transition>

            <!-- Hidden Input for Scanner -->
            <input
                id="hidden-scanner-input"
                v-model="barcodeInput"
                @keydown.enter.prevent="handleScannerInput"
                type="text"
                class="absolute opacity-0 pointer-events-none"
                :autofocus="posMode !== 'manual'"
            />

            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Mode Switcher -->
                <div class="mb-6 bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Mode Transaksi</h3>
                        <button
                            @click="showInstructions = !showInstructions"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ showInstructions ? 'Sembunyikan' : 'Tampilkan' }} Petunjuk
                        </button>
                    </div>

                    <!-- Mode Buttons -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <button
                            @click="switchMode('manual')"
                            :class="[
                                'p-4 rounded-lg font-semibold transition-all border-2',
                                posMode === 'manual'
                                    ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white border-green-600 shadow-lg scale-105'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-green-500'
                            ]"
                        >
                            <div class="text-2xl mb-2">📝</div>
                            <div class="text-sm">Manual Entry</div>
                        </button>
                        <button
                            @click="switchMode('rfid')"
                            :class="[
                                'p-4 rounded-lg font-semibold transition-all border-2',
                                posMode === 'rfid'
                                    ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-blue-600 shadow-lg scale-105'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-blue-500'
                            ]"
                        >
                            <div class="text-2xl mb-2">📡</div>
                            <div class="text-sm">RFID Scanner</div>
                        </button>
                        <button
                            @click="switchMode('barcode')"
                            :class="[
                                'p-4 rounded-lg font-semibold transition-all border-2',
                                posMode === 'barcode'
                                    ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white border-purple-600 shadow-lg scale-105'
                                    : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500'
                            ]"
                        >
                            <div class="text-2xl mb-2">🔲</div>
                            <div class="text-sm">Barcode Scanner</div>
                        </button>
                    </div>

                    <!-- Instructions Panel -->
                    <Transition name="slide-down">
                        <div v-if="showInstructions" :class="[
                            'rounded-lg p-6 bg-gradient-to-r',
                            getModeInstructions().color
                        ]">
                            <h4 class="text-white font-bold text-xl mb-4">{{ getModeInstructions().title }}</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="(step, index) in getModeInstructions().steps"
                                    :key="index"
                                    class="flex items-start gap-3 bg-white bg-opacity-20 rounded-lg p-3 text-white"
                                >
                                    <span class="text-2xl flex-shrink-0">{{ step.icon }}</span>
                                    <div>
                                        <span class="font-semibold">{{ index + 1 }}.</span>
                                        <span class="ml-2">{{ step.text }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Scanner Status Indicator -->
                            <div v-if="posMode !== 'manual'" class="mt-4 bg-white bg-opacity-30 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <div class="animate-pulse">
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    </div>
                                    <span class="text-white font-semibold">
                                        {{ posMode === 'rfid' ? 'Menunggu kartu RFID...' : 'Menunggu scan barcode...' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left Side: Product Catalog -->
                    <div class="flex-1">
                        <!-- Search & Filter -->
                        <div class="bg-white rounded-lg shadow p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari produk..."
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    />
                                </div>
                                <div>
                                    <select
                                        v-model="selectedCategory"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Semua Kategori</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div
                                v-for="product in filteredProducts"
                                :key="product.id"
                                @click="addToCart(product)"
                                class="bg-white rounded-lg shadow hover:shadow-md cursor-pointer"
                            >
                                <div class="aspect-square bg-gray-200 rounded-t-lg overflow-hidden">
                                    <img
                                        v-if="product.image_path"
                                        :src="`/storage/${product.image_path}`"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                        @error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'"
                                    />
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100" :style="product.image_path ? 'display:none' : ''">
                                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-semibold text-sm mb-1 truncate">{{ product.name }}</h3>
                                    <p class="text-xs text-gray-500 mb-2">{{ product.category.name }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold text-blue-600">
                                            {{ formatCurrency(product.harga_jual) }}
                                        </span>
                                        <span :class="[
                                            'text-xs px-2 py-1 rounded',
                                            product.stock < 10 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'
                                        ]">
                                            Stok: {{ product.stock }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredProducts.length === 0" class="text-center py-12 text-gray-500">
                            Tidak ada produk ditemukan
                        </div>
                    </div>

                    <!-- Right Side: Shopping Cart -->
                    <div class="lg:w-96">
                        <div class="bg-white rounded-lg shadow sticky top-6">
                            <div class="p-4 border-b">
                                <h3 class="font-bold text-lg">Keranjang Belanja</h3>
                                <p class="text-sm text-gray-500">{{ cartItemCount }} item</p>
                            </div>

                            <!-- Button Pilih Siswa Manual -->
                            <div v-if="!selectedStudent" class="p-4 bg-gradient-to-r from-purple-50 to-blue-50 border-b">
                                <button
                                    @click="openStudentSearch"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg shadow flex items-center justify-center gap-2 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Pilih Siswa Manual
                                </button>
                                <p class="text-xs text-gray-500 text-center mt-2">
                                    Atau scan kartu RFID siswa
                                </p>
                            </div>

                            <!-- RFID Scanner Info -->
                            <div v-if="selectedStudent" class="p-4 bg-blue-50 border-b">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-semibold">{{ selectedStudent.user.name }}</p>
                                        <p class="text-xs text-gray-600">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</p>
                                        <p class="text-sm font-bold text-blue-600 mt-1">
                                            Saldo: {{ formatCurrency(selectedStudent.balance) }}
                                        </p>
                                    </div>
                                    <button @click="selectedStudent = null; paymentMethod = 'cash'" class="text-red-500 text-xs">
                                        Hapus
                                    </button>
                                </div>
                            </div>

                            <!-- Cart Items -->
                            <div class="p-4 max-h-96 overflow-y-auto">
                                <div v-if="cart.length === 0" class="text-center py-8 text-gray-400">
                                    Keranjang kosong
                                </div>
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="(item, index) in cart"
                                        :key="index"
                                        class="flex gap-3 pb-3 border-b"
                                    >
                                        <img
                                            v-if="item.image_path"
                                            :src="`/storage/${item.image_path}`"
                                            :alt="item.name"
                                            class="w-16 h-16 object-cover rounded"
                                        />
                                        <div v-else class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-400">
                                            No Img
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm">{{ item.name }}</h4>
                                            <p class="text-xs text-gray-600">{{ formatCurrency(item.price) }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <button
                                                    @click="updateQuantity(index, item.quantity - 1)"
                                                    class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300"
                                                >
                                                    -
                                                </button>
                                                <input
                                                    type="number"
                                                    :value="item.quantity"
                                                    @change="updateQuantity(index, parseInt($event.target.value))"
                                                    class="w-12 text-center border-gray-300 rounded text-sm"
                                                    min="1"
                                                    :max="item.stock"
                                                />
                                                <button
                                                    @click="updateQuantity(index, item.quantity + 1)"
                                                    class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300"
                                                >
                                                    +
                                                </button>
                                                <button
                                                    @click="removeFromCart(index)"
                                                    class="ml-auto text-red-500 text-sm"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total & Actions -->
                            <div class="p-4 border-t">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-bold text-lg">Total:</span>
                                    <span class="font-bold text-2xl text-blue-600">
                                        {{ formatCurrency(cartTotal) }}
                                    </span>
                                </div>
                                <button
                                    @click="openCheckout"
                                    :disabled="cart.length === 0"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed mb-2"
                                >
                                    Bayar
                                </button>
                                <button
                                    @click="clearCart"
                                    :disabled="cart.length === 0"
                                    class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Hapus Semua
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Pilih Siswa Manual -->
            <div v-if="showStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                                Pilih Siswa
                            </h3>
                            <button @click="showStudentModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Search Input -->
                        <div class="mb-4">
                            <div class="relative">
                                <input
                                    v-model="studentSearchQuery"
                                    @input="searchStudents"
                                    type="text"
                                    placeholder="Cari siswa (NISN/Nama)..."
                                    class="w-full pl-10 pr-4 py-3 border-2 border-purple-300 focus:border-purple-600 focus:ring-purple-600 rounded-lg shadow-sm"
                                    autofocus
                                />
                                <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Minimal 2 karakter untuk mencari
                            </p>
                        </div>

                        <!-- Loading -->
                        <div v-if="searchingStudent" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-purple-600 border-t-transparent"></div>
                            <p class="text-gray-500 mt-2">Mencari siswa...</p>
                        </div>

                        <!-- Results -->
                        <div v-else-if="studentSearchResults.length > 0" class="max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                <button
                                    v-for="student in studentSearchResults"
                                    :key="student.id"
                                    @click="selectStudent(student)"
                                    class="w-full text-left p-4 border-2 border-gray-200 hover:border-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-bold text-purple-600">{{ student.nis }}</span>
                                                <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full">
                                                    {{ student.kelas }}
                                                </span>
                                            </div>
                                            <p class="font-semibold text-gray-900">{{ student.user.name }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ student.jurusan }} - Tahun {{ student.tahun_masuk }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Saldo</p>
                                            <p class="font-bold" :class="student.balance > 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(student.balance) }}
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="studentSearchQuery.length >= 2" class="text-center py-12">
                            <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="text-gray-500">Tidak ada siswa ditemukan</p>
                            <p class="text-sm text-gray-400 mt-1">Coba kata kunci lain</p>
                        </div>

                        <!-- Initial State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-gray-500">Mulai ketik untuk mencari siswa</p>
                            <p class="text-sm text-gray-400 mt-1">Berdasarkan NISN atau Nama</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Modal -->
            <div v-if="showCheckoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-4">Pembayaran</h3>

                        <!-- Total -->
                        <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-lg">Total Belanja:</span>
                                <span class="text-2xl font-bold text-blue-600">
                                    {{ formatCurrency(cartTotal) }}
                                </span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    @click="paymentMethod = 'cash'"
                                    :class="[
                                        'p-4 border-2 rounded-lg font-semibold transition-colors',
                                        paymentMethod === 'cash' ? 'border-green-600 bg-green-50 text-green-700' : 'border-gray-300'
                                    ]"
                                >
                                    💵 Tunai (Utama)
                                </button>
                                <button
                                    @click="paymentMethod = 'balance'"
                                    :class="[
                                        'p-4 border-2 rounded-lg font-semibold transition-colors',
                                        paymentMethod === 'balance' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-300 text-gray-400'
                                    ]"
                                    :disabled="!selectedStudent"
                                >
                                    🎴 RFID / Saldo
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ !selectedStudent && paymentMethod === 'balance' ? 'Scan kartu RFID siswa atau pilih siswa manual untuk menggunakan saldo' : paymentMethod === 'cash' ? 'Metode pembayaran utama - input nominal tunai di bawah' : 'Pembayaran menggunakan saldo siswa' }}
                            </p>
                        </div>

                        <!-- Cash Payment -->
                        <div v-if="paymentMethod === 'cash'" class="mb-4">
                            <label class="block text-sm font-medium mb-2">Jumlah Uang Tunai</label>
                            <input
                                v-model.number="cashAmount"
                                type="number"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                :min="cartTotal"
                                step="1000"
                            />
                            <div v-if="cashAmount >= cartTotal" class="mt-2 p-3 bg-green-50 rounded">
                                <div class="flex justify-between">
                                    <span>Kembalian:</span>
                                    <span class="font-bold text-green-600">
                                        {{ formatCurrency(changeAmount) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Payment Info -->
                        <div v-if="paymentMethod === 'balance' && selectedStudent" class="mb-4 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm font-semibold mb-1">{{ selectedStudent.user.name }}</p>
                            <p class="text-xs text-gray-600 mb-2">{{ selectedStudent.kelas }}</p>
                            <div class="flex justify-between text-sm">
                                <span>Saldo Sekarang:</span>
                                <span class="font-bold">{{ formatCurrency(selectedStudent.balance) }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span>Saldo Setelah:</span>
                                <span class="font-bold" :class="selectedStudent.balance - cartTotal < 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ formatCurrency(selectedStudent.balance - cartTotal) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button
                                @click="showCheckoutModal = false"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 rounded-lg"
                                :disabled="loading"
                            >
                                Batal
                            </button>
                            <button
                                @click="processCheckout"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg disabled:opacity-50"
                                :disabled="loading || (paymentMethod === 'cash' && cashAmount < cartTotal)"
                            >
                                {{ loading ? 'Memproses...' : 'Konfirmasi' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sales Modal -->
            <div v-if="showRecentSales" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-gray-800">Transaksi Terbaru (24 Jam)</h3>
                            <button @click="showRecentSales = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sales List -->
                        <div class="overflow-y-auto max-h-[70vh]">
                            <div v-if="recentSales.length === 0" class="text-center py-12">
                                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-gray-500">Belum ada transaksi dalam 24 jam terakhir</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="sale in recentSales"
                                    :key="sale.id"
                                    class="border-2 border-gray-200 rounded-lg p-4 hover:border-orange-300 transition-all"
                                >
                                    <div class="flex justify-between items-start gap-4">
                                        <!-- Sale Info -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="text-sm font-semibold text-gray-500">#{{ sale.id }}</span>
                                                <span class="text-xs text-gray-400">{{ new Date(sale.created_at).toLocaleString('id-ID') }}</span>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 mb-3">
                                                <div>
                                                    <p class="text-xs text-gray-500">Kasir</p>
                                                    <p class="font-semibold">{{ sale.user.name }}</p>
                                                </div>
                                                <div v-if="sale.student">
                                                    <p class="text-xs text-gray-500">Siswa</p>
                                                    <p class="font-semibold">{{ sale.student.user.name }}</p>
                                                    <p class="text-xs text-gray-400">NISN: {{ sale.student.nis }}</p>
                                                </div>
                                                <div v-else>
                                                    <p class="text-xs text-gray-500">Pelanggan</p>
                                                    <p class="font-semibold text-gray-400">Umum (Cash)</p>
                                                </div>
                                            </div>

                                            <!-- Items -->
                                            <div class="bg-gray-50 rounded p-3 mb-3">
                                                <p class="text-xs font-semibold text-gray-600 mb-2">Item Pembelian:</p>
                                                <div class="space-y-1">
                                                    <div
                                                        v-for="item in sale.sale_items"
                                                        :key="item.id"
                                                        class="flex justify-between text-sm"
                                                    >
                                                        <span>{{ item.product.name }} × {{ item.quantity }}</span>
                                                        <span class="font-semibold">{{ formatCurrency(item.subtotal) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Total & Payment -->
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-xs text-gray-500">Pembayaran: </span>
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="sale.payment_method === 'cash' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
                                                    >
                                                        {{ sale.payment_method === 'cash' ? 'Tunai' : 'Saldo' }}
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs text-gray-500">Total</p>
                                                    <p class="text-xl font-bold text-orange-600">{{ formatCurrency(sale.total) }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex flex-col gap-2">
                                            <a
                                                :href="`/kasir/receipt/${sale.id}`"
                                                target="_blank"
                                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors text-center"
                                            >
                                                🖨️ Struk
                                            </a>
                                            <button
                                                @click="voidTransaction(sale)"
                                                :disabled="loading"
                                                class="px-4 py-2 bg-red-500 hover:bg-red-600 disabled:bg-gray-400 text-white text-sm font-semibold rounded-lg transition-colors"
                                            >
                                                ❌ Void
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Minimal animations for better performance */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.15s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}

/* Slide down animation for instructions */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    max-height: 500px;
}

/* Remove all hover scale transforms for better performance */
</style>
