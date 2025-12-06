<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher - Koperasi Lemdiklat Taruna Nusantara Indonesia</title>
    <script src="{{ asset('js/jsbarcode.min.js') }}"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                max-width: 100%;
                padding: 5mm;
            }

            .print-controls {
                flex-direction: column;
                gap: 10px;
            }

            .print-controls h2 {
                font-size: 16px;
            }

            .print-controls .info {
                font-size: 12px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .vouchers-grid {
                grid-template-columns: 1fr;
                gap: 5mm;
            }

            .voucher-card {
                padding: 12px;
            }

            .voucher-title {
                font-size: 16px;
            }

            .voucher-amount {
                font-size: 24px;
            }

            .voucher-code {
                font-size: 12px;
                padding: 6px 10px;
            }

            .voucher-info {
                font-size: 9px;
            }

            .voucher-footer {
                font-size: 8px;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .voucher-card {
                break-inside: avoid;
            }

            /* Ensure proper sizing on print */
            .vouchers-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 5mm;
            }
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 10mm;
        }

        .print-controls {
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-controls h2 {
            color: white;
            margin: 0;
            font-size: 20px;
        }

        .print-controls .info {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: white;
            color: #667eea;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        .vouchers-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8mm;
        }

        .voucher-card {
            border: 2px dashed #667eea;
            border-radius: 12px;
            padding: 15px;
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }

        .voucher-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102,126,234,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .voucher-header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 2px solid #667eea;
            position: relative;
        }

        .voucher-logo {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .voucher-title {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .voucher-subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .voucher-amount-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin: 12px 0;
        }

        .voucher-amount-label {
            font-size: 10px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .voucher-amount {
            font-size: 28px;
            font-weight: bold;
            margin-top: 5px;
        }

        .voucher-code-section {
            text-align: center;
            margin: 12px 0;
        }

        .voucher-code-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .voucher-code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 8px 12px;
            border-radius: 6px;
            letter-spacing: 2px;
            display: inline-block;
            color: #333;
        }

        .barcode-container {
            text-align: center;
            margin: 12px 0;
            background: white;
            padding: 10px;
            border-radius: 6px;
        }

        .barcode-container svg {
            max-width: 100%;
            height: auto;
        }

        .voucher-info {
            font-size: 10px;
            color: #666;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
        }

        .voucher-info-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }

        .voucher-info-label {
            font-weight: 600;
        }

        .voucher-footer {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
            line-height: 1.4;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-available {
            background: #10b981;
            color: white;
        }

        .status-used {
            background: #ef4444;
            color: white;
        }

        .status-expired {
            background: #f59e0b;
            color: white;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            font-weight: bold;
            color: rgba(102, 126, 234, 0.03);
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Controls -->
        <div class="print-controls no-print">
            <div>
                <h2>🎫 Cetak Voucher</h2>
                <p class="info">Total: {{ $vouchers->count() }} voucher</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn btn-primary">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                        <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    </svg>
                    Cetak Voucher
                </button>
                <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>

        <!-- Vouchers Grid -->
        <div class="vouchers-grid">
            @foreach($vouchers as $voucher)
            <div class="voucher-card">
                <div class="watermark">KOPERASI</div>

                <div class="voucher-header">
                    <div class="voucher-logo">🎫</div>
                    <h1 class="voucher-title">Voucher Topup</h1>
                    <p class="voucher-subtitle">Koperasi Lemdiklat TNI</p>
                </div>

                <div class="voucher-amount-box">
                    <div class="voucher-amount-label">Nilai Voucher</div>
                    <div class="voucher-amount">Rp {{ number_format($voucher->nominal, 0, ',', '.') }}</div>
                </div>

                <div class="voucher-code-section">
                    <div class="voucher-code-label">Kode Penukaran</div>
                    <div class="voucher-code">{{ $voucher->code }}</div>
                </div>

                <div class="barcode-container">
                    <svg class="barcode"
                         data-code="{{ $voucher->code }}"
                         jsbarcode-format="CODE128"
                         jsbarcode-height="40"
                         jsbarcode-width="1.5"
                         jsbarcode-displayvalue="false"
                         jsbarcode-margin="0">
                    </svg>
                    <div style="font-size: 8px; color: #999; margin-top: 4px;">Scan barcode untuk redeem otomatis</div>
                </div>

                <div class="voucher-info">
                    <div class="voucher-info-row">
                        <span class="voucher-info-label">Status:</span>
                        <span class="status-badge status-{{ $voucher->status }}">
                            {{ ucfirst($voucher->status) }}
                        </span>
                    </div>
                    @if($voucher->expired_at)
                    <div class="voucher-info-row">
                        <span class="voucher-info-label">Berlaku s/d:</span>
                        <span>{{ \Carbon\Carbon::parse($voucher->expired_at)->format('d/m/Y') }}</span>
                    </div>
                    @else
                    <div class="voucher-info-row">
                        <span class="voucher-info-label">Masa Berlaku:</span>
                        <span>Tidak terbatas</span>
                    </div>
                    @endif
                    <div class="voucher-info-row">
                        <span class="voucher-info-label">Dibuat:</span>
                        <span>{{ \Carbon\Carbon::parse($voucher->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="voucher-footer">
                    <p><strong>Cara Penggunaan:</strong></p>
                    <p>1. Tunjukkan voucher ini kepada kasir</p>
                    <p>2. Scan barcode atau masukkan kode manual</p>
                    <p>3. Saldo akan otomatis masuk ke akun siswa</p>
                    <p style="margin-top: 8px; font-weight: bold;">⚠️ Voucher hanya bisa digunakan satu kali</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        // Barcode generation with multiple attempts and fallback
        let attemptCount = 0;
        const maxAttempts = 5;

        function initBarcodes() {
            attemptCount++;
            console.log('Barcode initialization attempt #' + attemptCount);

            if (typeof JsBarcode === 'undefined') {
                console.warn('JsBarcode library not loaded yet, attempt ' + attemptCount);

                if (attemptCount < maxAttempts) {
                    // Try again after a delay
                    setTimeout(initBarcodes, 200 * attemptCount);
                } else {
                    console.error('Failed to load JsBarcode after ' + maxAttempts + ' attempts');
                    showBarcodeFallback();
                }
                return;
            }

            const barcodes = document.querySelectorAll('.barcode');
            console.log('Found ' + barcodes.length + ' barcodes to generate');

            let successCount = 0;
            let errorCount = 0;

            barcodes.forEach(function(svg, index) {
                const code = svg.getAttribute('data-code');

                if (!code) {
                    console.error('No code found for barcode #' + index);
                    svg.parentElement.innerHTML = '<div style="color: #666; font-size: 10px; text-align: center; padding: 10px;">Kode: -</div>';
                    errorCount++;
                    return;
                }

                try {
                    JsBarcode(svg, code, {
                        format: "CODE128",
                        height: 40,
                        width: 1.5,
                        displayValue: false,
                        margin: 0,
                        valid: function(valid) {
                            if (!valid) {
                                throw new Error('Invalid barcode format');
                            }
                        }
                    });
                    console.log('Barcode #' + index + ' (' + code + ') generated successfully');
                    successCount++;
                } catch (e) {
                    console.error('Error generating barcode #' + index + ':', e);
                    // Fallback: show code as text
                    svg.parentElement.innerHTML = '<div style="text-align: center; padding: 10px;">' +
                        '<div style="font-size: 10px; color: #999; margin-bottom: 5px;">Scan atau ketik kode:</div>' +
                        '<div style="font-family: monospace; font-size: 12px; font-weight: bold; background: #f0f0f0; padding: 5px; border-radius: 4px;">' + code + '</div>' +
                        '</div>';
                    errorCount++;
                }
            });

            console.log('Barcode generation completed: ' + successCount + ' success, ' + errorCount + ' errors');
        }

        function showBarcodeFallback() {
            const barcodes = document.querySelectorAll('.barcode');
            barcodes.forEach(function(svg) {
                const code = svg.getAttribute('data-code');
                if (code) {
                    svg.parentElement.innerHTML = '<div style="text-align: center; padding: 10px;">' +
                        '<div style="font-size: 10px; color: #999; margin-bottom: 5px;">Ketik kode voucher:</div>' +
                        '<div style="font-family: monospace; font-size: 12px; font-weight: bold; background: #f0f0f0; padding: 5px; border-radius: 4px;">' + code + '</div>' +
                        '</div>';
                }
            });
        }

        // Start barcode generation
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBarcodes);
        } else {
            initBarcodes();
        }

        // Fallback: try again after window fully loaded
        window.addEventListener('load', function() {
            if (attemptCount === 0) {
                initBarcodes();
            }
        });
    </script>
</body>
</html>
