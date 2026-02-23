<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    status: String,
});

const form = useForm({
    email: props.email,
    otp: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('password.verify.store'), {
        onFinish: () => form.reset('password', 'password_confirmation', 'otp'),
    });
};

// Shooting stars animation
const createShootingStar = () => {
    const star = document.createElement('div');
    star.className = 'shooting-star';
    star.style.top = Math.random() * 50 + '%';
    star.style.right = '0';
    star.style.animationDelay = Math.random() * 3 + 's';
    document.querySelector('.galaxy-bg')?.appendChild(star);

    setTimeout(() => star.remove(), 3000);
};

onMounted(() => {
    setInterval(createShootingStar, 2000);
});
</script>

<template>
    <Head title="Verifikasi OTP & Reset Password" />

    <div class="min-h-screen galaxy-bg flex items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Planets -->
        <div class="planet planet-1"></div>
        <div class="planet planet-2"></div>

        <!-- Back to Login -->
        <Link
            :href="route('login')"
            class="absolute top-8 left-8 px-4 py-2 rounded-lg glass hover:bg-white/20 transition-all duration-300 border border-white/20 text-white flex items-center space-x-2 z-10"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Batal & Kembali ke Login</span>
        </Link>

        <!-- Container -->
        <div class="w-full max-w-md relative z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-8 float">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl gradient-purple-blue mb-4 shadow-lg glow">
                    <span class="text-4xl">🔑</span>
                </div>
                <h1 class="text-4xl font-bold mb-2">
                    <span class="galaxy-text">Verifikasi OTP</span>
                </h1>
                <p class="text-gray-300 text-sm max-w-md mx-auto">
                    Masukkan kode 6-digit yang telah dikirim ke email Anda beserta password baru Anda.
                </p>
            </div>

            <!-- Card -->
            <div class="galaxy-card p-8 animated-border">
                <!-- Status Message -->
                <div v-if="status" class="mb-6 px-4 py-3 rounded-lg bg-green-500/20 border border-green-500/50 text-green-300 text-sm text-center font-medium">
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <!-- Email (Hidden or Read-only) -->
                    <div class="hidden">
                        <input type="email" v-model="form.email" />
                    </div>
                    
                    <div class="bg-white/5 border border-white/10 rounded-lg p-3 text-center mb-6">
                        <p class="text-xs text-slate-400">Kode OTP dikirim ke:</p>
                        <p class="text-sm font-semibold text-white">{{ form.email || 'Email tidak ditemukan' }}</p>
                    </div>

                    <!-- OTP Input -->
                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-300 mb-2">
                            Kode OTP (6-Digit)
                        </label>
                        <input
                            id="otp"
                            type="text"
                            maxlength="6"
                            v-model="form.otp"
                            required
                            autofocus
                            class="galaxy-input w-full text-center tracking-[1em] font-mono text-xl"
                            placeholder="••••••"
                        />
                        <div v-if="form.errors.otp" class="mt-2 text-sm text-red-400 text-center">
                            {{ form.errors.otp }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                class="galaxy-input w-full pr-12"
                                placeholder="Minimal 8 karakter"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-400">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                            Konfirmasi Password Baru
                        </label>
                        <input
                            id="password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            required
                            class="galaxy-input w-full"
                            placeholder="Ulangi password baru"
                        />
                        <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-400">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full galaxy-btn py-3 text-lg font-semibold disabled:opacity-50 mt-4"
                    >
                        <span v-if="!form.processing">Reset Password</span>
                        <span v-else class="flex items-center justify-center">
                            Memproses...
                        </span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-400">
                        Tidak menerima email?
                        <Link :href="route('password.request')" class="galaxy-text hover:underline ml-1">Kirim ulang kode OTP</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.float {
    animation: float 6s ease-in-out infinite;
}
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}
</style>
