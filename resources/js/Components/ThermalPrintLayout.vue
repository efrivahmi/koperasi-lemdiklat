<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    user: {
        type: Object,
        default: null,
    }
});
</script>

<template>
    <div class="thermal-print-container">
        <!-- Header -->
        <div class="header">
            <div class="store-name">KOPERASI LEMDIKLAT</div>
            <div class="store-name-sub">TARUNA NUSANTARA INDONESIA</div>
            <div class="store-info">
                JL. Raya Purwakarta Kp. Warga Saluyu, RT 1 RW 15,<br>
                Cisomang Barat, Kec. Cikalongwetan,<br>
                Kabupaten Bandung Barat, Jawa Barat 40556
            </div>
        </div>

        <!-- Title -->
        <div class="report-title">
            {{ title }}
            <div v-if="subtitle" class="report-subtitle">{{ subtitle }}</div>
        </div>

        <!-- Info (Date/User) -->
        <div class="report-info">
            <div class="info-row">
                <span>Tanggal:</span>
                <span>{{ new Date().toLocaleString('id-ID') }}</span>
            </div>
            <div v-if="user" class="info-row">
                <span>Oleh:</span>
                <span>{{ user.name }}</span>
            </div>
        </div>

        <!-- Content Slot -->
        <div class="content">
            <slot />
        </div>

        <!-- Footer -->
        <div class="footer">
            Thank you :)<br>
            ================================
        </div>
    </div>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .thermal-print-container, .thermal-print-container * {
        visibility: visible;
    }
    .thermal-print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 58mm; /* Standard thermal paper width */
        padding: 0 2mm; /* Small padding */
        font-family: Arial, Helvetica, sans-serif; /* Bold sans-serif for thermal readability */
        font-size: 12px;
        font-weight: bold;
        line-height: 1.3;
        color: black;
        background: white;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Ensure tables inside take full width */
    .thermal-print-container table {
        width: 100%;
        border-collapse: collapse;
    }
    
    /* Hide URL/Page numbers if possible (browser dependent) */
    @page {
        size: 58mm auto;
        margin: 0;
    }
}

/* Hide on screen */
@media screen {
    .thermal-print-container {
        display: none;
    }
}

/* Sizing and Typography */
.header {
    text-align: center;
    margin-bottom: 8px;
    border-bottom: 1px dashed #000;
    padding-bottom: 5px;
}

.store-name {
    font-size: 12px;
    font-weight: bold;
}

.store-name-sub {
    font-size: 9px;
    font-weight: bold;
    margin-bottom: 2px;
}

.store-info {
    font-size: 8px;
}

.report-title {
    text-align: center;
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.report-subtitle {
    font-size: 9px;
    font-weight: normal;
}

.report-info {
    margin-bottom: 8px;
    border-bottom: 1px dashed #000;
    padding-bottom: 5px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
}

.content {
    margin-bottom: 10px;
}

.footer {
    text-align: center;
    font-size: 8px;
    margin-top: 10px;
    border-top: 1px dashed #000;
    padding-top: 5px;
}
</style>
