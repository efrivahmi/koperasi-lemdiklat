<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const isMounted = ref(false);

const submit = () => {
    form.post(route('password.email'));
};

// Shooting stars animation
let starInterval;
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
    setTimeout(() => {
        isMounted.value = true;
    }, 100);
    starInterval = setInterval(createShootingStar, 2000);
});

onUnmounted(() => {
    clearInterval(starInterval);
});
</script>

<template>
    <Head title="Lupa Password" />

    <div class="min-h-screen galaxy-bg flex items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden text-white perspective-1000">
        <!-- Planets & Abstract Background -->
        <div class="planet planet-1 opacity-50 filter blur-sm"></div>
        <div class="planet planet-2 opacity-50 filter blur-sm"></div>
        
        <!-- Animated Blobs -->
        <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] bg-indigo-600/30 rounded-full mix-blend-screen filter blur-[100px] animate-blob transition-opacity duration-1000" :class="isMounted ? 'opacity-100' : 'opacity-0'"></div>
        <div class="absolute bottom-1/3 right-1/4 w-[400px] h-[400px] bg-pink-600/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-4000 transition-opacity duration-1000 delay-500" :class="isMounted ? 'opacity-100' : 'opacity-0'"></div>

        <!-- Back to Login -->
        <Link
            :href="route('login')"
            class="absolute top-6 left-4 sm:top-8 sm:left-8 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 backdrop-blur-md transition-all duration-500 border border-white/10 text-white flex items-center space-x-2 z-20 group transform"
            :class="isMounted ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'"
        >
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-medium text-sm">Kembali Login</span>
        </Link>

        <!-- Main Forgot Password Container (Glass Plate) -->
        <div 
            class="w-full max-w-md mt-16 sm:mt-0 relative z-10 transition-all duration-1000 cubic-bezier-entrance delay-200 py-10"
            :class="isMounted ? 'translate-y-0 opacity-100 scale-100 rotate-x-0' : 'translate-y-20 opacity-0 scale-95 rotate-x-12'"
        >
            <!-- Logo & Title -->
            <div class="text-center mb-8 float">
                <div 
                    class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-tr from-pink-500/80 to-indigo-600/80 mb-6 shadow-[0_0_40px_rgba(236,72,153,0.4)] border border-white/20 transition-all duration-700 delay-400"
                    :class="isMounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"
                >
                    <span class="text-5xl drop-shadow-lg">🔐</span>
                </div>
                <h1 
                    class="text-4xl font-extrabold mb-3 tracking-tight transition-all duration-700 delay-500"
                    :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-pink-200">Lupa Password?</span>
                </h1>
                <p 
                    class="text-pink-100/70 font-medium transition-all duration-700 delay-700 max-w-sm mx-auto leading-relaxed"
                    :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    Tidak masalah! Masukkan email Anda dan sistem akan mengirimkan kode verifikasi 6-digit.
                </p>
            </div>

            <!-- Heavy Glassmorphism Card -->
            <div class="bg-white/5 backdrop-blur-[40px] border border-white/10 p-8 sm:p-10 rounded-[32px] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] relative overflow-hidden group">
                <!-- Inner glow -->
                <div class="absolute inset-0 bg-gradient-to-t from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>

                <!-- Status Message -->
                <transition
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="transform -translate-y-4 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-4 opacity-0"
                >
                    <div v-if="status" class="mb-6 px-4 py-3 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-300 text-sm font-medium flex items-center gap-3 backdrop-blur-md">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ status }}
                    </div>
                </transition>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div 
                        class="transition-all duration-700 delay-[900ms]"
                        :class="isMounted ? 'translate-x-0 opacity-100' : 'translate-x-8 opacity-0'"
                    >
                        <label for="email" class="block text-sm font-semibold text-pink-100/80 mb-2 ml-1">
                            Email Recovery
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:border-pink-400 focus:ring-1 focus:ring-pink-400 focus:bg-white/10 transition-all duration-300 backdrop-blur-md"
                                placeholder="name@domain.com"
                            />
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-white/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div v-if="form.errors.email" class="mt-2 text-xs text-rose-400 ml-1 font-medium">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div 
                        class="pt-4 transition-all duration-700 delay-[1200ms]"
                        :class="isMounted ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-10 opacity-0 scale-95'"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full relative py-4 rounded-2xl font-bold text-lg text-white overflow-hidden transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed group shadow-[0_10px_20px_rgba(0,0,0,0.2)] hover:shadow-[0_10px_30px_rgba(219,39,119,0.4)] hover:-translate-y-0.5"
                        >
                            <!-- Animated Background -->
                            <div class="absolute inset-x-0 bottom-0 h-full bg-gradient-to-r from-pink-600 via-purple-600 to-pink-600 bg-[length:200%_auto] animate-gradient"></div>
                            
                            <span v-if="!form.processing" class="relative z-10 flex items-center justify-center">
                                Kirim Kode OTP
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                            <span v-else class="relative z-10 flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cubic-bezier-entrance {
    transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
.float {
    animation: float 8s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 15s infinite alternate;
}
.animation-delay-4000 { animation-delay: 4s; }

@keyframes gradient-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.animate-gradient {
    animation: gradient-flow 3s ease infinite;
}

input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
    -webkit-text-fill-color: white !important;
    transition: background-color 5000s ease-in-out 0s;
}

.perspective-1000 { perspective: 1000px; }
.rotate-x-12 { transform: rotateX(12deg); }
.rotate-x-0 { transform: rotateX(0deg); }
</style>
