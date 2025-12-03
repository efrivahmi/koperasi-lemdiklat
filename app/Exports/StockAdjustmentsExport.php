<?php

namespace App\Exports;

use App\Models\StockAdjustment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StockAdjustmentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $dateFrom;
    protected $dateTo;
    protected $productId;
    protected $type;
    protected $adjustedBy;
    protected $search;

    public function __construct($dateFrom, $dateTo, $productId = null, $type = null, $adjustedBy = null, $search = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->productId = $productId;
        $this->type = $type;
        $this->adjustedBy = $adjustedBy;
        $this->search = $search;
    }

    public function collection()
    {
        $query = StockAdjustment::with(['product', 'adjustedBy'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        // Apply filters
        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->adjustedBy) {
            $query->where('adjusted_by', $this->adjustedBy);
        }

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Nama Produk',
            'Barcode',
            'Tipe Penyesuaian',
            'Tujuan Penyesuaian',
            'Stok Sebelum',
            'Jumlah Disesuaikan',
            'Stok Sesudah',
            'Harga Beli',
            'Nilai Stok (COGS)',
            'Pendapatan',
            'Laba/Rugi',
            'Disesuaikan Oleh',
            'Catatan',
        ];
    }

    public function map($adjustment): array
    {
        $hargaBeli = $adjustment->product->harga_beli ?? 0;
        $hargaJual = $adjustment->product->harga_jual ?? 0;
        $quantity = $adjustment->quantity_adjusted;
        $costImpact = $quantity * $hargaBeli;

        // Translate purpose to Indonesian
        $purposeLabels = [
            'sale' => 'Penjualan (Transaksi)',
            'internal_use' => 'Keperluan Internal/Kantor',
            'personal_use' => 'Keperluan Pribadi',
            'damage' => 'Kerusakan Barang',
            'expired' => 'Barang Kadaluarsa',
            'return_to_supplier' => 'Retur ke Supplier',
            'other' => 'Lainnya'
        ];
        $purposeLabel = $purposeLabels[$adjustment->purpose] ?? 'Tidak Diketahui';

        // Calculate revenue and profit/loss based on purpose
        $revenue = 0;
        $profitLossImpact = 0;

        if ($adjustment->purpose === 'sale') {
            // Sales: Revenue - COGS = Gross Profit
            $revenue = $quantity * $hargaJual;
            $profitLossImpact = $revenue - $costImpact;
        } else if (in_array($adjustment->purpose, ['internal_use', 'personal_use', 'damage', 'expired'])) {
            // Non-revenue: Pure loss
            $revenue = 0;
            $profitLossImpact = -$costImpact;
        } else if ($adjustment->purpose === 'return_to_supplier') {
            // Return: Refund at purchase price (break-even)
            $revenue = $costImpact;
            $profitLossImpact = 0;
        } else {
            // Other: Potential margin
            $profitMarginPerUnit = $hargaJual - $hargaBeli;
            $profitLossImpact = $quantity * $profitMarginPerUnit;
        }

        $sign = $adjustment->type === 'addition' ? '+' : '-';

        return [
            Carbon::parse($adjustment->created_at)->format('d/m/Y'),
            Carbon::parse($adjustment->created_at)->format('H:i:s'),
            $adjustment->product->name ?? '-',
            $adjustment->product->barcode ?? '-',
            $adjustment->type === 'addition' ? 'Penambahan' : 'Pengurangan',
            $purposeLabel,
            $adjustment->quantity_before,
            $adjustment->quantity_adjusted,
            $adjustment->quantity_after,
            number_format($hargaBeli, 0, ',', '.'),
            $sign . number_format($costImpact, 0, ',', '.'),
            number_format($revenue, 0, ',', '.'),
            ($profitLossImpact >= 0 ? '+' : '') . number_format($profitLossImpact, 0, ',', '.'),
            $adjustment->adjustedBy->name ?? '-',
            $adjustment->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Tanggal
            'B' => 10,  // Waktu
            'C' => 30,  // Nama Produk
            'D' => 15,  // Barcode
            'E' => 18,  // Tipe Penyesuaian
            'F' => 28,  // Tujuan Penyesuaian
            'G' => 12,  // Stok Sebelum
            'H' => 12,  // Jumlah Disesuaikan
            'I' => 12,  // Stok Sesudah
            'J' => 12,  // Harga Beli
            'K' => 18,  // Nilai Stok (COGS)
            'L' => 15,  // Pendapatan
            'M' => 15,  // Laba/Rugi
            'N' => 20,  // Disesuaikan Oleh
            'O' => 35,  // Catatan
        ];
    }
}
