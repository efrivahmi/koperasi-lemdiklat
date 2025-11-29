<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
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
    <Head title="Login" />

    <div class="min-h-screen galaxy-bg flex items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Planets -->
        <div class="planet planet-1"></div>
        <div class="planet planet-2"></div>

        <!-- Back to Home -->
        <Link
            :href="route('welcome')"
            class="absolute top-8 left-8 px-4 py-2 rounded-lg glass hover:bg-white/20 transition-all duration-300 border border-white/20 text-white flex items-center space-x-2 z-10"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </Link>

        <!-- Login Container -->
        <div class="w-full max-w-md relative z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-8 float">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl gradient-purple-blue mb-4 shadow-lg glow">
                    <span class="text-4xl">🏪</span>
                </div>
                <h1 class="text-4xl font-bold mb-2">
                    <span class="galaxy-text">Selamat Datang</span>
                </h1>
                <p class="text-gray-300">Koperasi Lemdiklat Taruna Nusantara Indonesia</p>
                <p class="text-sm text-gray-400 mt-1">SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya</p>
                <p class="text-sm text-gray-400">Kab. Bandung Barat</p>
            </div>

            <!-- Login Card -->
            <div class="galaxy-card p-8 animated-border">
                <!-- Status Message -->
                <div v-if="status" class="mb-6 px-4 py-3 rounded-lg bg-green-500/20 border border-green-500/50 text-green-300 text-sm">
                    {{ status }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="galaxy-input w-full"
                            placeholder="email@example.com"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-400">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="galaxy-input w-full pr-12"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors duration-200"
                                tabindex="-1"
                            >
                                <!-- Eye Icon (Show) -->
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye Slash Icon (Hide) -->
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-400">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded bg-white/10 border-white/20 text-purple-600 focus:ring-purple-500 focus:ring-offset-0"
                            />
                            <span class="ml-2 text-sm text-gray-300">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm galaxy-text hover:underline"
                        >
                            Lupa password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full galaxy-btn py-3 text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Login</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </form>

            </div>

            <!-- Features Preview -->
            <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">⚡</div>
                    <div class="text-xs text-gray-300">Fast</div>
                </div>
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">🔒</div>
                    <div class="text-xs text-gray-300">Secure</div>
                </div>
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">💎</div>
                    <div class="text-xs text-gray-300">Modern</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure smooth animations */
.float {
    animation: float 6s ease-in-out infinite;
}
</style>
