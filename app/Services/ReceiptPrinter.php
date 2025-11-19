<?php

namespace App\Services;

use App\Models\Sale;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;

class ReceiptPrinter
{
    /**
     * Generate receipt HTML for browser printing or thermal printer preview
     */
    public static function generateReceiptHtml(Sale $sale): string
    {
        $sale->load(['student.user', 'saleItems.product']);

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk Pembelian</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt {
                width: 80mm;
                font-family: "Courier New", monospace;
                font-size: 12px;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
        body {
            font-family: "Courier New", monospace;
            font-size: 12px;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .receipt {
            width: 100%;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .right {
            text-align: right;
        }
        .header {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header center">
            <div class="bold" style="font-size: 14px;">KOPERASI LEMDIKLAT</div>
            <div class="bold" style="font-size: 13px;">TARUNA NUSANTARA INDONESIA</div>
            <div style="font-size: 11px;">SMA Taruna Nusantara Indonesia</div>
            <div style="font-size: 11px;">SMK Taruna Nusantara Jaya</div>
        </div>

        <div class="line"></div>

        <div style="margin: 10px 0;">
            <div>Tanggal: ' . $sale->created_at->format('d/m/Y H:i:s') . '</div>
            <div>Kasir: ' . $sale->student->user->name . '</div>
            <div>NIS: ' . $sale->student->nis . '</div>
            <div>Kelas: ' . $sale->student->class . '</div>
        </div>

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th style="text-align: left;">Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Harga</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($sale->saleItems as $item) {
            $html .= '
                <tr>
                    <td>' . $item->product->name . '</td>
                    <td style="text-align: center;">' . $item->quantity . '</td>
                    <td style="text-align: right;">' . number_format($item->price, 0, ',', '.') . '</td>
                    <td style="text-align: right;">' . number_format($item->subtotal, 0, ',', '.') . '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td class="bold">TOTAL:</td>
                <td class="right bold">Rp ' . number_format($sale->total, 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td>Metode Bayar:</td>
                <td class="right">' . ($sale->payment_method === 'cash' ? 'TUNAI' : 'SALDO') . '</td>
            </tr>';

        if ($sale->payment_method === 'balance') {
            // Get ending balance from transaction
            $transaction = \App\Models\Transaction::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->where('student_id', $sale->student_id)
                ->first();

            if ($transaction) {
                $html .= '
                <tr>
                    <td>Saldo Akhir:</td>
                    <td class="right">Rp ' . number_format($transaction->ending_balance, 0, ',', '.') . '</td>
                </tr>';
            }
        }

        $html .= '
        </table>

        <div class="line"></div>

        <div class="footer center">
            <div style="margin: 10px 0;">Terima kasih atas pembelian Anda!</div>
            <div style="font-size: 10px;">Barang yang sudah dibeli</div>
            <div style="font-size: 10px;">tidak dapat dikembalikan</div>
        </div>

        <div class="center" style="margin-top: 15px; font-size: 10px;">
            <div>Powered by Koperasi System</div>
            <div>ID Transaksi: #' . str_pad($sale->id, 6, '0', STR_PAD_LEFT) . '</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Auto print when page loads
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>';

        return $html;
    }

    /**
     * Generate plain text receipt for thermal printer (ESC/POS)
     * This can be used with JavaScript libraries like escpos or sent directly to printer
     */
    public static function generateReceiptText(Sale $sale): string
    {
        $sale->load(['student.user', 'saleItems.product']);

        $width = 42; // Characters per line for 80mm paper

        $text = '';

        // Header
        $text .= self::centerText('KOPERASI LEMDIKLAT', $width) . "\n";
        $text .= self::centerText('TARUNA NUSANTARA INDONESIA', $width) . "\n";
        $text .= self::centerText('SMA Taruna Nusantara Indonesia', $width) . "\n";
        $text .= self::centerText('SMK Taruna Nusantara Jaya', $width) . "\n";
        $text .= str_repeat('-', $width) . "\n";

        // Transaction info
        $text .= "Tanggal: " . $sale->created_at->format('d/m/Y H:i:s') . "\n";
        $text .= "Kasir: " . $sale->student->user->name . "\n";
        $text .= "NIS: " . $sale->student->nis . "\n";
        $text .= "Kelas: " . $sale->student->class . "\n";
        $text .= str_repeat('-', $width) . "\n";

        // Items
        foreach ($sale->saleItems as $item) {
            $text .= $item->product->name . "\n";
            $line = sprintf(
                "  %dx @ %s",
                $item->quantity,
                number_format($item->price, 0, ',', '.')
            );
            $line .= str_repeat(' ', $width - strlen($line) - strlen(number_format($item->subtotal, 0, ',', '.')));
            $line .= number_format($item->subtotal, 0, ',', '.');
            $text .= $line . "\n";
        }

        $text .= str_repeat('-', $width) . "\n";

        // Total
        $totalLine = "TOTAL:";
        $totalLine .= str_repeat(' ', $width - strlen($totalLine) - strlen('Rp ' . number_format($sale->total, 0, ',', '.')));
        $totalLine .= 'Rp ' . number_format($sale->total, 0, ',', '.');
        $text .= $totalLine . "\n";

        $text .= "Metode: " . ($sale->payment_method === 'cash' ? 'TUNAI' : 'SALDO') . "\n";

        if ($sale->payment_method === 'balance') {
            $transaction = \App\Models\Transaction::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->where('student_id', $sale->student_id)
                ->first();

            if ($transaction) {
                $text .= "Saldo Akhir: Rp " . number_format($transaction->ending_balance, 0, ',', '.') . "\n";
            }
        }

        $text .= str_repeat('-', $width) . "\n";

        // Footer
        $text .= "\n";
        $text .= self::centerText('Terima kasih!', $width) . "\n";
        $text .= self::centerText('Barang yang sudah dibeli', $width) . "\n";
        $text .= self::centerText('tidak dapat dikembalikan', $width) . "\n";
        $text .= "\n";
        $text .= self::centerText('Powered by Koperasi System', $width) . "\n";
        $text .= self::centerText('ID: #' . str_pad($sale->id, 6, '0', STR_PAD_LEFT), $width) . "\n";
        $text .= "\n\n\n";

        return $text;
    }

    /**
     * Center text for thermal printer
     */
    private static function centerText(string $text, int $width): string
    {
        $len = strlen($text);
        if ($len >= $width) {
            return $text;
        }

        $spaces = ($width - $len) / 2;
        return str_repeat(' ', (int)$spaces) . $text;
    }

    /**
     * Get receipt data as JSON for frontend printing
     */
    public static function getReceiptData(Sale $sale): array
    {
        $sale->load(['student.user', 'saleItems.product']);

        $transaction = \App\Models\Transaction::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('student_id', $sale->student_id)
            ->first();

        return [
            'id' => $sale->id,
            'date' => $sale->created_at->format('d/m/Y H:i:s'),
            'student' => [
                'name' => $sale->student->user->name,
                'nis' => $sale->student->nis,
                'class' => $sale->student->class,
            ],
            'items' => $sale->saleItems->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'total' => $sale->total,
            'payment_method' => $sale->payment_method,
            'ending_balance' => $transaction ? $transaction->ending_balance : null,
        ];
    }
}
