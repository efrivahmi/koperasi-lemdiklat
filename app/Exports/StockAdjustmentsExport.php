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
            'Stok Sebelum',
            'Jumlah Disesuaikan',
            'Stok Sesudah',
            'Disesuaikan Oleh',
            'Catatan',
        ];
    }

    public function map($adjustment): array
    {
        return [
            Carbon::parse($adjustment->created_at)->format('d/m/Y'),
            Carbon::parse($adjustment->created_at)->format('H:i:s'),
            $adjustment->product->name ?? '-',
            $adjustment->product->barcode ?? '-',
            $adjustment->type === 'addition' ? 'Penambahan' : 'Pengurangan',
            $adjustment->quantity_before,
            $adjustment->quantity_adjusted,
            $adjustment->quantity_after,
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
            'F' => 15,  // Stok Sebelum
            'G' => 20,  // Jumlah Disesuaikan
            'H' => 15,  // Stok Sesudah
            'I' => 20,  // Disesuaikan Oleh
            'J' => 35,  // Catatan
        ];
    }
}
