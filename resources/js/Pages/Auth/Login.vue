<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

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
const isMounted = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
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
    // Trigger entrance animations
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
    <Head title="Login Portal" />

    <div class="min-h-screen galaxy-bg flex items-center justify-center px-4 sm:px-6 lg:px-8 relative overflow-hidden text-white perspective-1000">
        <!-- Planets & Abstract Background -->
        <div class="planet planet-1 opacity-50 filter blur-sm"></div>
        <div class="planet planet-2 opacity-50 filter blur-sm"></div>
        
        <!-- Animated Blobs -->
        <div class="absolute top-1/4 left-1/4 w-[400px] h-[400px] bg-purple-600/30 rounded-full mix-blend-screen filter blur-[100px] animate-blob transition-opacity duration-1000" :class="isMounted ? 'opacity-100' : 'opacity-0'"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-blue-600/30 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-4000 transition-opacity duration-1000 delay-500" :class="isMounted ? 'opacity-100' : 'opacity-0'"></div>

        <!-- Back to Home -->
        <Link
            :href="route('welcome')"
            class="absolute top-6 left-4 sm:top-8 sm:left-8 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 backdrop-blur-md transition-all duration-500 border border-white/10 text-white flex items-center space-x-2 z-20 group transform"
            :class="isMounted ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'"
        >
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-medium text-sm">Kembali</span>
        </Link>

        <!-- Main Login Container (The Glass Plate) -->
        <div 
            class="w-full max-w-md mt-16 sm:mt-0 relative z-10 transition-all duration-1000 cubic-bezier-entrance py-10"
            :class="isMounted ? 'translate-y-0 opacity-100 scale-100 rotate-x-0' : 'translate-y-20 opacity-0 scale-95 rotate-x-12'"
        >
            
            <!-- Logo & Title Section -->
            <div class="text-center mb-8 float">
                <div 
                    class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-tr from-indigo-500/80 to-purple-600/80 mb-6 shadow-[0_0_40px_rgba(99,102,241,0.5)] border border-white/20 transition-all duration-700 delay-300"
                    :class="isMounted ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"
                >
                    <span class="text-5xl drop-shadow-lg">🏪</span>
                </div>
                <h1 
                    class="text-4xl font-extrabold mb-3 tracking-tight transition-all duration-700 delay-500"
                    :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-200">Akses Portal</span>
                </h1>
                <p 
                    class="text-blue-200/60 font-medium transition-all duration-700 delay-700"
                    :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    Aplikasi Koperasi Lemdiklat
                </p>
            </div>

            <!-- Heavy Glassmorphism Card -->
            <div class="bg-white/5 backdrop-blur-[40px] border border-white/10 p-8 sm:p-10 rounded-[32px] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] relative overflow-hidden group">
                <!-- Inner card animated glow -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>

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

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Email Field -->
                    <div 
                        class="transition-all duration-700 delay-[800ms]"
                        :class="isMounted ? 'translate-x-0 opacity-100' : 'translate-x-8 opacity-0'"
                    >
                        <label for="email" class="block text-sm font-semibold text-blue-100/80 mb-2 ml-1">
                            Email Address
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 focus:bg-white/10 transition-all duration-300 backdrop-blur-md"
                                placeholder="name@domain.com"
                            />
                            <!-- Email Icon Overlay -->
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-white/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div v-if="form.errors.email" class="mt-2 text-xs text-rose-400 ml-1 font-medium">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div 
                        class="transition-all duration-700 delay-[1000ms]"
                        :class="isMounted ? 'translate-x-0 opacity-100' : '-translate-x-8 opacity-0'"
                    >
                        <label for="password" class="block text-sm font-semibold text-blue-100/80 mb-2 ml-1">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 focus:bg-white/10 transition-all duration-300 backdrop-blur-md pr-14"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-white/40 hover:text-white transition-colors duration-200 p-1"
                                tabindex="-1"
                            >
                                <!-- Eye Icon -->
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <!-- Eye Slash Icon -->
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 text-xs text-rose-400 ml-1 font-medium">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div 
                        class="flex items-center justify-between transition-all duration-700 delay-[1200ms]"
                        :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                    >
                        <label class="flex items-center group cursor-pointer">
                            <div class="relative flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="peer w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0 transition-all cursor-pointer"
                                />
                                <div class="absolute inset-0 rounded pointer-events-none peer-checked:shadow-[0_0_10px_rgba(99,102,241,0.5)] transition-shadow"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-blue-100/70 group-hover:text-white transition-colors">Ingat Saya</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium text-indigo-300 hover:text-white hover:underline underline-offset-4 transition-colors"
                        >
                            Lupa Password?
                        </Link>
                    </div>

                    <!-- Primary Submit Button -->
                    <div 
                        class="pt-2 transition-all duration-700 delay-[1400ms]"
                        :class="isMounted ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-10 opacity-0 scale-95'"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full relative py-4 rounded-2xl font-bold text-lg text-white overflow-hidden transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed group shadow-[0_10px_20px_rgba(0,0,0,0.2)] hover:shadow-[0_10px_30px_rgba(99,102,241,0.4)] hover:-translate-y-0.5"
                        >
                            <!-- Animated Background -->
                            <div class="absolute inset-x-0 bottom-0 h-full bg-gradient-to-r from-indigo-500 via-purple-600 to-indigo-500 bg-[length:200%_auto] animate-gradient"></div>
                            
                            <span v-if="!form.processing" class="relative z-10 flex items-center justify-center">
                                Masuk ke Sistem
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                            <span v-else class="relative z-10 flex items-center justify-center">
                                <!-- Loading Spinner -->
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memverifikasi...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div 
                    class="relative mt-8 mb-8 transition-all duration-700 delay-[1600ms]"
                    :class="isMounted ? 'opacity-100' : 'opacity-0'"
                >
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-[#0a0f1c] text-blue-100/50 rounded-full border border-white/5 font-medium backdrop-blur-3xl shadow-inner">Autentikasi Alternatif</span>
                    </div>
                </div>

                <!-- Google SSO Button -->
                <div 
                    class="transition-all duration-700 delay-[1800ms]"
                    :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                >
                    <a
                        :href="route('auth.google')"
                        class="w-full px-5 py-4 rounded-2xl flex items-center justify-center gap-3 transition-all duration-300 border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 text-white font-semibold shadow-lg hover:-translate-y-0.5"
                    >
                        <div class="bg-white p-1 rounded-full shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </div>
                        Lanjutkan dengan SSO Google
                    </a>
                </div>

            </div>

            <!-- Features Preview Indicator -->
            <div 
                class="mt-8 grid grid-cols-3 gap-4 text-center transition-all duration-1000 delay-[2000ms]"
                :class="isMounted ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'"
            >
                <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors">
                    <div class="text-2xl mb-1">⚡</div>
                    <div class="text-[10px] font-bold tracking-wider text-blue-200/50 uppercase">Super Cepat</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors">
                    <div class="text-2xl mb-1">🔒</div>
                    <div class="text-[10px] font-bold tracking-wider text-blue-200/50 uppercase">Keamanan Tinggi</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors">
                    <div class="text-2xl mb-1">💎</div>
                    <div class="text-[10px] font-bold tracking-wider text-blue-200/50 uppercase">Desain Premium</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Core Entrance Bezier */
.cubic-bezier-entrance {
    transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

/* Float Animations */
.float {
    animation: float 8s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

/* Background Blob Animations */
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

/* Gradient flow for primary button */
@keyframes gradient-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.animate-gradient {
    animation: gradient-flow 3s ease infinite;
}

/* Hide Default Eye Icons on Password Fields */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}
input[type="password"]::-webkit-credentials-auto-fill-button,
input[type="password"]::-webkit-contacts-auto-fill-button {
    visibility: hidden;
    pointer-events: none;
    position: absolute;
    right: 0;
}
input[type="password"]::-webkit-textfield-decoration-container {
    padding-right: 3.5rem;
}

/* Remove default Autofill backgrounds */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
    -webkit-text-fill-color: white !important;
    transition: background-color 5000s ease-in-out 0s;
}

/* 3D Transform utils */
.perspective-1000 { perspective: 1000px; }
.rotate-x-12 { transform: rotateX(12deg); }
.rotate-x-0 { transform: rotateX(0deg); }
</style>
