<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode Labels</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        /* CRITICAL: Hide controls when printing */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print,
            .print-controls,
            .info-banner,
            button,
            select {
                display: none !important;
            }
        }

        /* A4 Mode - untuk batch printing */
        @media print {
            .print-mode-a4 {
                @page {
                    size: A4;
                    margin: 10mm;
                }
            }
            .print-mode-a4 .container {
                max-width: 100%;
                padding: 0;
            }
            .print-mode-a4 .labels-grid {
                gap: 4mm;
            }
        }

        /* Thermal Mode - untuk label sticker thermal */
        @media print {
            .print-mode-thermal {
                @page {
                    size: 60mm 40mm;
                    margin: 1mm;
                }
            }
            .print-mode-thermal .labels-grid {
                display: block !important;
            }
            .print-mode-thermal .label {
                page-break-after: always;
                width: 58mm !important;
                height: 38mm !important;
                margin: 0 !important;
                padding: 3mm !important;
            }
            .print-mode-thermal .label-header {
                font-size: 8px !important;
                line-height: 1.2 !important;
                max-height: 18px !important;
                margin-bottom: 3px !important;
            }
            .print-mode-thermal .price {
                font-size: 18px !important;
            }
            .print-mode-thermal .price-label {
                font-size: 6px !important;
            }
            .print-mode-thermal .label-footer {
                font-size: 7px !important;
                margin-bottom: 2mm !important;
            }
        }

        /* Label Sticker Mode - untuk continuous label 70x35mm */
        @media print {
            .print-mode-label {
                @page {
                    size: 70mm 35mm;
                    margin: 1mm;
                }
            }
            .print-mode-label .labels-grid {
                display: block !important;
            }
            .print-mode-label .label {
                page-break-after: always;
                width: 68mm !important;
                height: 33mm !important;
                margin: 0 !important;
                padding: 3mm !important;
            }
            .print-mode-label .label-header {
                font-size: 9px !important;
                line-height: 1.3 !important;
                max-height: 20px !important;
                margin-bottom: 4px !important;
            }
            .print-mode-label .price {
                font-size: 22px !important;
            }
            .print-mode-label .price-label {
                font-size: 7px !important;
            }
            .print-mode-label .label-footer {
                font-size: 8px !important;
                margin-bottom: 2mm !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
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
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #4F46E5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338CA;
        }

        .btn-secondary {
            background: #6B7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4B5563;
        }

        .labels-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm;
            margin-top: 10mm;
        }

        .label {
            border: 2px solid #000;
            padding: 4mm;
            text-align: center;
            background: white;
            page-break-inside: avoid;
            height: 60mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .label-header {
            font-size: 10px;
            font-weight: 700;
            color: #000;
            line-height: 1.3;
            max-height: 26px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            width: 100%;
            margin-bottom: 6px;
        }

        .price-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 6px 0;
        }

        .price-label {
            font-size: 7px;
            color: #888;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .price {
            font-size: 26px;
            font-weight: 900;
            color: #000;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .barcode-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 6px 0;
        }

        .barcode-container svg {
            max-width: 100%;
            height: auto !important;
        }

        .label-footer {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 4px;
            margin-bottom: 2mm;
        }

        .info-banner {
            background: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .info-banner p {
            margin: 4px 0;
            font-size: 14px;
            color: #1E40AF;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-content {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4F46E5;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        /* Error Toast */
        .error-toast {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #EF4444;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            font-size: 14px;
            font-weight: 600;
            max-width: 400px;
        }

        .error-toast.active {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Controls -->
        <div class="print-controls no-print">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <label style="font-weight: 600; color: #374151;">Pilih Mode Cetak:</label>
                <select id="printMode" onchange="changePrintMode()" class="btn" style="padding: 8px 16px; border: 2px solid #4F46E5; background: white; color: #4F46E5; cursor: pointer;">
                    <option value="a4">📄 A4 (Print Masal)</option>
                    <option value="label">🏷️ Label Sticker 70x35mm</option>
                    <option value="thermal">🖨️ Thermal 60x40mm</option>
                </select>
                <button onclick="autoPrint()" class="btn btn-primary">
                    ⚡ Print Langsung
                </button>
                <button onclick="window.print()" class="btn btn-secondary">
                    👁️ Preview & Print
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    ❌ Close
                </button>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="info-banner no-print">
            <p><strong>📦 Total Produk:</strong> {{ $products->count() }} produk</p>
            <p><strong>🏷️ Total Label:</strong> {{ $products->count() * $quantity }} label ({{ $quantity }} label per produk)</p>
            <p><strong>💡 Tips:</strong> Gunakan sticker label ukuran 70mm x 35mm untuk hasil terbaik</p>
        </div>

        <!-- Barcode Labels Grid -->
        <div class="labels-grid">
            @foreach($products as $product)
                @for($i = 0; $i < $quantity; $i++)
                    <div class="label">
                        <!-- Nama Produk -->
                        <div class="label-header">
                            {{ strtoupper(Str::limit($product->name, 30)) }}
                        </div>

                        <!-- Harga (Alfamart Style) -->
                        <div class="price-section">
                            <div class="price-label">Harga</div>
                            <div class="price">
                                Rp{{ number_format($product->harga_jual, 0, ',', '.') }}
                            </div>
                        </div>

                        <!-- Barcode -->
                        <div class="barcode-container">
                            <svg class="barcode"
                                 data-barcode="{{ $product->barcode }}"
                                 jsbarcode-format="CODE128"
                                 jsbarcode-height="35"
                                 jsbarcode-width="1.5"
                                 jsbarcode-displayvalue="true"
                                 jsbarcode-fontsize="9"
                                 jsbarcode-margin="0">
                            </svg>
                        </div>

                        <!-- Kategori -->
                        <div class="label-footer">
                            {{ strtoupper($product->category->name ?? 'UMUM') }}
                        </div>
                    </div>
                @endfor
            @endforeach
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <div class="loading-text">Mengirim ke printer...</div>
            <div style="font-size: 12px; color: #6B7280; margin-top: 8px;">Mohon tunggu</div>
        </div>
    </div>

    <!-- Error Toast -->
    <div id="errorToast" class="error-toast"></div>

    <script>
        // Change print mode
        function changePrintMode() {
            const mode = document.getElementById('printMode').value;
            document.body.className = 'print-mode-' + mode;
        }

        // Set default mode to A4
        document.addEventListener('DOMContentLoaded', function() {
            document.body.className = 'print-mode-a4';

            // Generate all barcodes after page load
            const barcodes = document.querySelectorAll('.barcode');

            barcodes.forEach(function(barcode) {
                const code = barcode.getAttribute('data-barcode');
                if (code) {
                    try {
                        JsBarcode(barcode, code, {
                            format: "CODE128",
                            height: 50,
                            width: 2,
                            displayValue: true,
                            fontSize: 12,
                            margin: 0
                        });
                    } catch (e) {
                        console.error('Error generating barcode for:', code, e);
                        barcode.parentElement.innerHTML = '<div style="color: red; font-size: 10px;">Invalid Barcode</div>';
                    }
                }
            });
        });

        // Auto print when opening in new window
        if (window.location.search.includes('autoprint=true')) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            });
        }

        // Auto Print Function with Error Detection
        function autoPrint() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            const errorToast = document.getElementById('errorToast');

            try {
                // Show loading overlay
                loadingOverlay.classList.add('active');

                // Check if print is supported
                if (!window.print) {
                    throw new Error('Fungsi print tidak didukung oleh browser Anda');
                }

                // Small delay to ensure barcodes are fully rendered
                setTimeout(function() {
                    try {
                        // Trigger print dialog
                        window.print();

                        // Hide loading after print dialog opens
                        setTimeout(function() {
                            loadingOverlay.classList.remove('active');
                        }, 500);

                        // Log success
                        console.log('[Print] Print dialog dibuka:', new Date().toLocaleString());

                    } catch (printError) {
                        // Hide loading
                        loadingOverlay.classList.remove('active');

                        // Show error toast
                        showError('Gagal membuka dialog print: ' + printError.message);

                        // Log error
                        console.error('[Print Error]', printError);
                    }
                }, 300);

            } catch (error) {
                // Hide loading
                loadingOverlay.classList.remove('active');

                // Show error toast
                showError(error.message || 'Terjadi kesalahan saat mencetak');

                // Log error
                console.error('[Print Error]', error);
            }

            // Detect if printer is not connected (after print dialog)
            // This is a workaround since we can't directly detect printer connection
            window.onafterprint = function() {
                console.log('[Print] Print selesai atau dibatalkan');
                loadingOverlay.classList.remove('active');
            };

            window.onbeforeprint = function() {
                console.log('[Print] Memulai proses print...');
            };
        }

        // Show Error Toast
        function showError(message) {
            const errorToast = document.getElementById('errorToast');
            errorToast.textContent = '⚠️ ' + message;
            errorToast.classList.add('active');

            // Auto hide after 5 seconds
            setTimeout(function() {
                errorToast.classList.remove('active');
            }, 5000);
        }

        // Add error event listener for print errors
        window.addEventListener('error', function(event) {
            if (event.message.toLowerCase().includes('print')) {
                console.error('[Print Error Event]', event);
                showError('Error: ' + event.message);
            }
        });
    </script>
</body>
</html>
