<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        /* Thermal Printer Style - 58mm width */
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
            body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 58mm;
            padding: 5mm;
            background: white;
        }

        .receipt {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-info {
            font-size: 9px;
            line-height: 1.3;
        }

        .receipt-info {
            margin-bottom: 8px;
            font-size: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .receipt-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .items {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .item {
            margin-bottom: 4px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .summary {
            margin-bottom: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .summary-row.total {
            font-size: 13px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 4px;
        }

        .payment-info {
            margin-bottom: 8px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            z-index: 1000;
        }

        .print-button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Struk</button>

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">KOPERASI LEMDIKLAT</div>
            <div class="store-name" style="font-size: 10px; margin-top: 2px;">TARUNA NUSANTARA INDONESIA</div>
            <div class="store-info" style="margin-top: 4px;">
                {{ config('school.subtitle') }}<br>
                {{ config('school.contact.address') }}<br>
                {{ config('school.contact.phone') }}
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="receipt-info-row">
                <span>No. Transaksi</span>
                <span>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="receipt-info-row">
                <span>Tanggal</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Admin/Cashier Information - HIGHLIGHTED -->
        <div style="background: #f0f0f0; padding: 6px; margin-bottom: 8px; border: 1px solid #999; border-radius: 3px;">
            <div style="text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 4px; text-transform: uppercase;">
                👤 DILAYANI OLEH
            </div>
            <div class="receipt-info-row" style="font-weight: bold;">
                <span>Kasir/Admin:</span>
                <span>{{ $sale->user->name }}</span>
            </div>
            <div class="receipt-info-row">
                <span>Role:</span>
                <span style="text-transform: uppercase;">{{ $sale->user->role }}</span>
            </div>
        </div>

        <!-- Student Information -->
        @if($sale->student)
        <div style="background: #e3f2fd; padding: 6px; margin-bottom: 8px; border: 1px solid #90caf9; border-radius: 3px;">
            <div style="text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 4px;">
                👨‍🎓 INFO SISWA
            </div>
            <div class="receipt-info-row">
                <span>Nama:</span>
                <span>{{ $sale->student->user->name }}</span>
            </div>
            <div class="receipt-info-row">
                <span>NIS:</span>
                <span>{{ $sale->student->nis }}</span>
            </div>
            <div class="receipt-info-row">
                <span>Kelas:</span>
                <span>{{ $sale->student->kelas }}</span>
            </div>
        </div>
        @else
        <div class="receipt-info" style="margin-bottom: 8px;">
            <div class="receipt-info-row">
                <span>Pelanggan:</span>
                <span>UMUM (Tunai)</span>
            </div>
        </div>
        @endif

        <!-- Items -->
        <div class="items">
            @foreach($sale->saleItems as $item)
            <div class="item">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-detail">
                    <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>

            @if($sale->payment_method === 'cash')
            <div class="summary-row">
                <span>Tunai</span>
                <span>Rp {{ number_format($sale->cash_amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Kembalian</span>
                <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <div class="receipt-info-row">
                <span>Metode Bayar</span>
                <span>{{ $sale->payment_method === 'cash' ? 'TUNAI' : 'SALDO' }}</span>
            </div>
            @if($sale->payment_method === 'balance' && $sale->student)
            <div class="receipt-info-row">
                <span>Saldo Akhir</span>
                <span>Rp {{ number_format($sale->student->balance, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            Terima kasih atas kunjungan Anda<br>
            Barang yang sudah dibeli tidak dapat dikembalikan<br>
            ================================<br>
            {{ config('school.website') }}<br>
            Powered by Claude Code
        </div>
    </div>

    <script>
        // Auto print on load (opsional - uncomment untuk auto print)
        // window.onload = function() {
        //     setTimeout(function(){
        //         window.print();
        //         // Auto close after print (opsional)
        //         // setTimeout(function(){ window.close(); }, 1000);
        //     }, 500);
        // };
    </script>
</body>
</html>
