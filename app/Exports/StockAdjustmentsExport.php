<?php

namespace App\Exports;

use App\Models\StockAdjustment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class StockAdjustmentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $dateFrom;
    protected $dateTo;
    protected $productId;
    protected $type;
    protected $adjustedBy;
    protected $search;
    protected $clientName;

    public function __construct($dateFrom, $dateTo, $productId = null, $type = null, $adjustedBy = null, $search = null, $clientName = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->productId = $productId;
        $this->type = $type;
        $this->adjustedBy = $adjustedBy;
        $this->search = $search;
        $this->clientName = $clientName;
    }

    public function collection()
    {
        $query = StockAdjustment::with(['product', 'adjustedBy', 'creator', 'updater'])
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

        if ($this->clientName) {
            $query->where('client_name', 'like', '%' . $this->clientName . '%');
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
            'Harga Jual',
            'Pendapatan',
            'Laba/Rugi',
            'Disesuaikan Oleh',
            'Nama Pelanggan',
            'Catatan',
            'Dibuat Oleh',
            'Dibuat Pada',
            'Diubah Oleh',
            'Diubah Pada',
        ];
    }

    public function map($adjustment): array
    {
        $hargaBeli = $adjustment->product->harga_beli ?? 0;
        $hargaJual = $adjustment->product->harga_jual ?? 0;
        $qty = $adjustment->quantity_adjusted;
        
        // Signed Quantity
        $signedQty = ($adjustment->type === 'deduction') ? -$qty : $qty;

        // Translate purpose to Indonesian
        $purposeLabels = [
            'restock' => 'Restock (Beli)',
            'sale' => 'Penjualan (Transaksi)',
            'internal_use' => 'Keperluan Internal/Kantor',
            'personal_use' => 'Keperluan Pribadi',
            'damage' => 'Kerusakan Barang',
            'expired' => 'Barang Kadaluarsa',
            'loss' => 'Barang Hilang',
            'return_to_supplier' => 'Retur ke Supplier',
            'return' => 'Retur dari Pelanggan',
            'correction' => 'Koreksi Stok',
            'other' => 'Lainnya'
        ];
        $purposeLabel = $purposeLabels[$adjustment->purpose] ?? 'Lainnya';

        // Calculate Revenue and Profit/Loss
        $revenue = 0;
        $profit = 0;

        switch ($adjustment->purpose) {
            case 'sale': // Deduction
                $revenue = $qty * $hargaJual;
                $profit = ($qty * $hargaJual) - ($qty * $hargaBeli);
                break;

            case 'return_to_supplier': // Deduction
                $revenue = $qty * $hargaBeli; // Cash In
                $profit = 0;
                break;

            case 'damage':
            case 'expired':
            case 'loss': 
            case 'internal_use':
            case 'personal_use':
                // Deduction (Loss)
                $revenue = 0;
                $profit = -1 * ($qty * $hargaBeli);
                break;

            case 'restock': // Addition
                $revenue = 0;
                $profit = 0;
                break;

            case 'return': // Addition (Customer Return)
                // Reverse of sale
                $revenue = -1 * ($qty * $hargaJual);
                $profit = -1 * (($qty * $hargaJual) - ($qty * $hargaBeli));
                break;

            case 'correction':
                if ($adjustment->type === 'addition') {
                    // Found stock (Gain)
                    $revenue = 0;
                    $profit = $qty * $hargaBeli;
                } else {
                    // Lost stock (Loss)
                    $revenue = 0;
                    $profit = -1 * ($qty * $hargaBeli);
                }
                break;

            default: // other
                if ($adjustment->type === 'deduction') {
                    $revenue = 0;
                    $profit = -1 * ($qty * $hargaBeli);
                } else {
                    $revenue = 0;
                    $profit = 0;
                }
                break;
        }

        return [
            Carbon::parse($adjustment->created_at)->format('d/m/Y'),
            Carbon::parse($adjustment->created_at)->format('H:i:s'),
            $adjustment->product->name ?? '-',
            (string) ($adjustment->product->barcode ?? '-'), // Explicit string cast, FORMAT_TEXT handles display
            $adjustment->type === 'addition' ? 'Penambahan' : 'Pengurangan',
            $purposeLabel,
            $adjustment->quantity_before,
            $signedQty,
            $adjustment->quantity_after,
            $hargaBeli,        // Raw number — Excel formats via columnFormats
            $hargaJual,        // Raw number
            $revenue,          // Raw number
            $profit,           // Raw number (can be negative)
            $adjustment->adjustedBy->name ?? '-',
            $adjustment->client_name ?? '-',
            $adjustment->notes ?? '-',
            $adjustment->creator->name ?? '-',
            $adjustment->created_at ? Carbon::parse($adjustment->created_at)->format('d/m/Y H:i:s') : '-',
            $adjustment->updater->name ?? '-',
            $adjustment->updated_at ? Carbon::parse($adjustment->updated_at)->format('d/m/Y H:i:s') : '-',
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
            'D' => 18,  // Barcode
            'E' => 18,  // Tipe Penyesuaian
            'F' => 28,  // Tujuan Penyesuaian
            'G' => 12,  // Stok Sebelum
            'H' => 12,  // Jumlah Disesuaikan
            'I' => 12,  // Stok Sesudah
            'J' => 15,  // Harga Beli
            'K' => 15,  // Harga Jual
            'L' => 15,  // Pendapatan
            'M' => 15,  // Laba/Rugi
            'N' => 20,  // Disesuaikan Oleh
            'O' => 25,  // Nama Pelanggan
            'P' => 35,  // Catatan
            'Q' => 20,  // Dibuat Oleh
            'R' => 18,  // Dibuat Pada
            'S' => 20,  // Diubah Oleh
            'T' => 18,  // Diubah Pada
        ];
    }
    public function columnFormats(): array
    {
        return [
            'D' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Force column D (Barcode) to be stored as text string
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cell = $sheet->getCell('D' . $row);
                    $value = $cell->getValue();
                    $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                }
            },
        ];
    }
}
