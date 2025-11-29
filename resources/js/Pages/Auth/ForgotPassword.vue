<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
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
    <Head title="Lupa Password" />

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
            <span>Kembali ke Login</span>
        </Link>

        <!-- Forgot Password Container -->
        <div class="w-full max-w-md relative z-10">
            <!-- Logo & Title -->
            <div class="text-center mb-8 float">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl gradient-purple-blue mb-4 shadow-lg glow">
                    <span class="text-4xl">🔐</span>
                </div>
                <h1 class="text-4xl font-bold mb-2">
                    <span class="galaxy-text">Lupa Password?</span>
                </h1>
                <p class="text-gray-300 text-sm max-w-md mx-auto">
                    Tidak masalah! Masukkan email Anda dan kami akan mengirimkan link reset password.
                </p>
            </div>

            <!-- Forgot Password Card -->
            <div class="galaxy-card p-8 animated-border">
                <!-- Status Message -->
                <div v-if="status" class="mb-6 px-4 py-3 rounded-lg bg-green-500/20 border border-green-500/50 text-green-300 text-sm">
                    {{ status }}
                </div>

                <!-- Forgot Password Form -->
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

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full galaxy-btn py-3 text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Kirim Link Reset Password</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    Ingat password Anda?
                    <Link
                        :href="route('login')"
                        class="galaxy-text font-semibold hover:underline ml-1"
                    >
                        Login sekarang
                    </Link>
                </p>
            </div>

            <!-- Features Preview -->
            <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">📧</div>
                    <div class="text-xs text-gray-300">Email</div>
                </div>
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">🔒</div>
                    <div class="text-xs text-gray-300">Secure</div>
                </div>
                <div class="glass p-4 rounded-lg border border-white/10">
                    <div class="text-2xl mb-2">⚡</div>
                    <div class="text-xs text-gray-300">Fast</div>
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
