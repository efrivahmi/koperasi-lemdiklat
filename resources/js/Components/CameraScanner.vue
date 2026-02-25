<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { Html5QrcodeScanner, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Scan Barcode / QR Code',
    },
});

const emit = defineEmits(['close', 'scan']);

const scannerConfig = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    formatsToSupport: [
        Html5QrcodeSupportedFormats.QR_CODE,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.ITF
    ],
};

const scannerRef = ref(null);
let html5QrcodeScanner = null;
const isScanning = ref(false);

const startScanning = () => {
    isScanning.value = true;
    nextTick(() => {
        if (!html5QrcodeScanner) {
             html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                scannerConfig,
                /* verbose= */ false
            );
        }
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    });
};

const stopScanning = () => {
    if (html5QrcodeScanner) {
        // html5QrcodeScanner.clear() returns a promise Documented by Html5Qrcode
        html5QrcodeScanner.clear().catch(error => {
            console.error("Failed to clear html5QrcodeScanner. ", error);
        });
        html5QrcodeScanner = null;
    }
    isScanning.value = false;
};

const closeScanner = () => {
    stopScanning();
    emit('close');
};

const onScanSuccess = (decodedText, decodedResult) => {
    // Stop scanning immediately after a successful scan
    stopScanning();
    // Play a beep sound (optional, basic HTML5 audio fallback)
    try {
        const audio = new Audio('/audio/beep.mp3'); // if you have a beep file, otherwise ignore
        audio.play().catch(e => {}); 
    } catch(e) {}
    
    emit('scan', decodedText);
    emit('close');
};

const onScanFailure = (error) => {
    // handle scan failure, usually better to ignore and keep scanning
    // console.warn(`Code scan error = ${error}`);
};

// Start scanning when component is mounted ONLY IF 'show' is true
// But usually we control visibility via v-if in the parent
// Start scanning when component is mounted ONLY IF 'show' is true
// But usually we control visibility via v-if in the parent
onMounted(() => {
    if (props.show) {
        startScanning();
    }
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        startScanning();
    } else {
        stopScanning();
    }
});

onUnmounted(() => {
    stopScanning();
});

</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 border border-purple-500/30 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-slide-up relative flex flex-col">
            
            <!-- Header -->
            <div class="p-4 border-b border-purple-500/30 flex justify-between items-center bg-gray-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ title }}
                </h3>
                <button @click="closeScanner" class="text-gray-400 hover:text-white transition-colors bg-gray-700/50 p-1 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scanner Area -->
            <div class="p-4 relative bg-black/50 min-h-[300px] flex items-center justify-center">
                <div id="reader" width="100%" class="w-full h-full rounded-xl overflow-hidden [&>video]:rounded-xl [&>video]:w-full [&>video]:object-cover border-2 border-dashed border-gray-600"></div>
                
                <!-- Loading state / Instructions before camera starts -->
                <div v-if="!isScanning" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 pointer-events-none">
                    <svg class="w-12 h-12 mb-3 animate-spin text-purple-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium">Mengaktifkan Kamera...</p>
                    <p class="text-xs text-gray-500 mt-1 px-8 text-center">Harap izinkan akses kamera di browser Anda jika diminta.</p>
                </div>
            </div>
            
            <!-- Footer -->
             <div class="p-4 bg-gray-800 text-center border-t border-purple-500/20">
                <p class="text-xs text-gray-400">Arahkan kamera ke Barcode (Barang) atau QR Code (Kartu Siswa)</p>
            </div>
        </div>
    </div>
</template>

<style>
/* CSS to override inline styles of html5-qrcode library */
#reader__dashboard_section_csr span {
    color: white !important;
}
#reader button {
    background-color: #4f46e5; /* indigo-600 */
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: bold;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    margin-top: 10px;
    margin-bottom: 10px;
}
#reader button:hover {
    background-color: #4338ca; /* indigo-700 */
}
#reader select {
    background-color: #1f2937; /* gray-800 */
    color: white;
    padding: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid #4b5563; /* gray-600 */
    margin-bottom: 10px;
    width: 100%;
}
#reader__camera_permission_button {
     background-color: #ec4899 !important; /* pink-500 */
}
#reader__dashboard_section_swaplink {
    color: #e5e7eb !important; /* gray-200 */
    text-decoration: underline;
    margin-top: 10px;
    display: block;
}
</style>
