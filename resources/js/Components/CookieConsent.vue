<script setup>
import { ref, onMounted } from 'vue';

const showCookieConsent = ref(false);

onMounted(() => {
    // Check if user has already accepted cookies
    const hasAcceptedCookies = localStorage.getItem('cookies_accepted');
    if (!hasAcceptedCookies) {
        // Add a small delay for better UX
        setTimeout(() => {
            showCookieConsent.value = true;
        }, 1000);
    }
});

const acceptCookies = () => {
    localStorage.setItem('cookies_accepted', 'true');
    showCookieConsent.value = false;
};
</script>

<template>
    <!-- Cookie Consent Popup -->
    <transition
        enter-active-class="transition ease-out duration-500"
        enter-from-class="transform translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-300"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-full opacity-0"
    >
        <div 
            v-if="showCookieConsent"
            class="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6 pointer-events-none"
        >
            <div class="max-w-4xl mx-auto pointer-events-auto">
                <div class="bg-slate-900/90 backdrop-blur-xl border border-white/10 shadow-2xl rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    
                    <!-- Content -->
                    <div class="flex items-start gap-4 flex-1">
                        <div class="hidden sm:flex shrink-0 w-12 h-12 rounded-full bg-indigo-500/20 items-center justify-center">
                            <span class="text-2xl">🍪</span>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-base sm:text-lg flex items-center gap-2">
                                <span class="sm:hidden">🍪</span> Pengaturan Privasi & Cookie
                            </h3>
                            <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                                Kami menggunakan cookie untuk memastikan Anda mendapatkan pengalaman terbaik dan teraman (termasuk Login via Google) di sistem Koperasi Lemdiklat. 
                                <a href="#" class="text-indigo-400 hover:text-indigo-300 underline underline-offset-2 transition-colors">Pelajari lebih lanjut</a>.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0 gap-3">
                        <button 
                            @click="showCookieConsent = false"
                            class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 font-medium hover:bg-white/5 transition-colors text-sm"
                        >
                            Tolak
                        </button>
                        <button 
                            @click="acceptCookies"
                            class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold shadow-lg shadow-indigo-500/25 transition-all transform hover:scale-105 active:scale-95 text-sm"
                        >
                            Mengerti & Setuju
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
/* Optional: Additional styling for the cookie popup can go here */
</style>
