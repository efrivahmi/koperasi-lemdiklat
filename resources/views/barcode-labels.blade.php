<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode Labels</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
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
                display: none;
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
            border: 1px dashed #ddd;
            padding: 8px;
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
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
            max-height: 28px;
            overflow: hidden;
            line-height: 14px;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .barcode-container {
            margin: 8px 0;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-container svg {
            max-width: 100%;
            height: auto;
        }

        .label-footer {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }

        .price {
            font-size: 12px;
            font-weight: bold;
            color: #059669;
            margin-top: 2px;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Controls -->
        <div class="print-controls no-print">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Labels
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ❌ Close
            </button>
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
                        <div class="label-header">
                            {{ $product->name }}
                        </div>
                        <div class="barcode-container">
                            <svg class="barcode"
                                 data-barcode="{{ $product->barcode }}"
                                 jsbarcode-format="CODE128"
                                 jsbarcode-height="50"
                                 jsbarcode-width="2"
                                 jsbarcode-displayvalue="true"
                                 jsbarcode-fontsize="12"
                                 jsbarcode-margin="0">
                            </svg>
                        </div>
                        <div class="label-footer">
                            {{ $product->category->name ?? 'General' }}
                        </div>
                        <div class="price">
                            Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                        </div>
                    </div>
                @endfor
            @endforeach
        </div>
    </div>

    <script>
        // Generate all barcodes after page load
        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>
