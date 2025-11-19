<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
const paymentMethod = ref('cash');
const cashAmount = ref(0);
const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });

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

// Methods
const loadProducts = async () => {
    try {
        const response = await axios.get('/kasir/api/products');
        products.value = response.data.products;
    } catch (error) {
        showNotification('Gagal memuat produk', 'error');
    }
};

const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.product_id === product.id);

    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        } else {
            showNotification(`Stok ${product.name} tidak mencukupi`, 'error');
        }
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            price: product.harga_jual,
            quantity: 1,
            stock: product.stock,
            image_path: product.image_path,
        });
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateQuantity = (index, newQuantity) => {
    const item = cart.value[index];
    if (newQuantity > 0 && newQuantity <= item.stock) {
        item.quantity = newQuantity;
    } else if (newQuantity > item.stock) {
        showNotification('Jumlah melebihi stok tersedia', 'error');
    }
};

const clearCart = () => {
    if (confirm('Hapus semua item dari keranjang?')) {
        cart.value = [];
        selectedStudent.value = null;
        paymentMethod.value = 'cash';
        cashAmount.value = 0;
    }
};

const handleBarcodeInput = async () => {
    if (!barcodeInput.value) return;

    try {
        const response = await axios.get(`/kasir/api/products/barcode/${barcodeInput.value}`);
        if (response.data.success) {
            addToCart(response.data.product);
            showNotification(`${response.data.product.name} ditambahkan ke keranjang`, 'success');
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Produk tidak ditemukan', 'error');
    } finally {
        barcodeInput.value = '';
    }
};

const handleRfidInput = async () => {
    if (!rfidInput.value) return;

    try {
        const response = await axios.get(`/kasir/api/students/rfid/${rfidInput.value}`);
        if (response.data.success) {
            selectedStudent.value = response.data.student;
            paymentMethod.value = 'balance';
            showNotification(`Siswa: ${response.data.student.user.name} - Saldo: Rp ${formatCurrency(response.data.student.balance)}`, 'success');
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Kartu RFID tidak terdaftar', 'error');
    } finally {
        rfidInput.value = '';
    }
};

const openCheckout = () => {
    if (cart.value.length === 0) {
        showNotification('Keranjang masih kosong', 'error');
        return;
    }
    showCheckoutModal.value = true;
};

const processCheckout = async () => {
    if (paymentMethod.value === 'cash' && cashAmount.value < cartTotal.value) {
        showNotification('Jumlah uang tunai tidak mencukupi', 'error');
        return;
    }

    if (paymentMethod.value === 'balance' && !selectedStudent.value) {
        showNotification('Silakan scan kartu RFID siswa terlebih dahulu', 'error');
        return;
    }

    if (paymentMethod.value === 'balance' && selectedStudent.value.balance < cartTotal.value) {
        showNotification('Saldo siswa tidak mencukupi', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post('/kasir/api/checkout', {
            items: cart.value,
            payment_method: paymentMethod.value,
            student_id: paymentMethod.value === 'balance' ? selectedStudent.value.id : null,
            cash_amount: paymentMethod.value === 'cash' ? cashAmount.value : null,
        });

        if (response.data.success) {
            showNotification('Transaksi berhasil!', 'success');

            // Reset
            cart.value = [];
            selectedStudent.value = null;
            paymentMethod.value = 'cash';
            cashAmount.value = 0;
            showCheckoutModal.value = false;

            // Reload products to update stock
            loadProducts();
        }
    } catch (error) {
        showNotification(error.response?.data?.message || 'Transaksi gagal', 'error');
    } finally {
        loading.value = false;
    }
};

const showNotification = (message, type = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// Input Focus Handler (untuk barcode & RFID scanner)
const handleGlobalKeydown = (e) => {
    // Jika user sedang mengetik di input field lain, skip
    if (e.target.tagName === 'INPUT' && e.target.id !== 'hidden-scanner-input') {
        return;
    }

    // Jika bukan Enter key, focus ke hidden input
    if (e.key !== 'Enter') {
        const hiddenInput = document.getElementById('hidden-scanner-input');
        if (hiddenInput) {
            hiddenInput.focus();
        }
    }
};

onMounted(() => {
    loadProducts();
    window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown);
});
</script>

<template>
    <Head title="POS / Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">POS / Kasir</h2>
        </template>

        <div class="py-6">
            <!-- Notification -->
            <div v-if="notification.show" :class="[
                'fixed top-20 right-4 z-50 px-6 py-3 rounded-lg shadow-lg',
                notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            ]">
                {{ notification.message }}
            </div>

            <!-- Hidden Input for Scanner -->
            <input
                id="hidden-scanner-input"
                v-model="barcodeInput"
                @keydown.enter.prevent="handleBarcodeInput"
                type="text"
                class="absolute opacity-0 pointer-events-none"
                autofocus
            />

            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
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
                                class="bg-white rounded-lg shadow hover:shadow-lg cursor-pointer transition-shadow"
                            >
                                <div class="aspect-square bg-gray-200 rounded-t-lg overflow-hidden">
                                    <img
                                        v-if="product.image_path"
                                        :src="`/storage/${product.image_path}`"
                                        :alt="product.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        No Image
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-semibold text-sm mb-1 truncate">{{ product.name }}</h3>
                                    <p class="text-xs text-gray-500 mb-2">{{ product.category.name }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold text-blue-600">
                                            Rp {{ formatCurrency(product.harga_jual) }}
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

                            <!-- RFID Scanner Info -->
                            <div v-if="selectedStudent" class="p-4 bg-blue-50 border-b">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-semibold">{{ selectedStudent.user.name }}</p>
                                        <p class="text-xs text-gray-600">{{ selectedStudent.nis }} - {{ selectedStudent.kelas }}</p>
                                        <p class="text-sm font-bold text-blue-600 mt-1">
                                            Saldo: Rp {{ formatCurrency(selectedStudent.balance) }}
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
                                            <p class="text-xs text-gray-600">Rp {{ formatCurrency(item.price) }}</p>
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
                                        Rp {{ formatCurrency(cartTotal) }}
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
                                    Rp {{ formatCurrency(cartTotal) }}
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
                                        'p-4 border-2 rounded-lg font-semibold',
                                        paymentMethod === 'cash' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-300'
                                    ]"
                                >
                                    Tunai
                                </button>
                                <button
                                    @click="paymentMethod = 'balance'"
                                    :class="[
                                        'p-4 border-2 rounded-lg font-semibold',
                                        paymentMethod === 'balance' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-gray-300'
                                    ]"
                                    :disabled="!selectedStudent"
                                >
                                    Saldo Siswa
                                </button>
                            </div>
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
                                        Rp {{ formatCurrency(changeAmount) }}
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
                                <span class="font-bold">Rp {{ formatCurrency(selectedStudent.balance) }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span>Saldo Setelah:</span>
                                <span class="font-bold" :class="selectedStudent.balance - cartTotal < 0 ? 'text-red-600' : 'text-green-600'">
                                    Rp {{ formatCurrency(selectedStudent.balance - cartTotal) }}
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
        </div>
    </AuthenticatedLayout>
</template>
