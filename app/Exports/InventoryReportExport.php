<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $categoryId;
    protected $stockStatus;
    protected $search;

    public function __construct($categoryId = null, $stockStatus = null, $search = null)
    {
        $this->categoryId = $categoryId;
        $this->stockStatus = $stockStatus;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Product::with('category');

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->stockStatus) {
            if ($this->stockStatus === 'out') {
                $query->where('stock', 0);
            } elseif ($this->stockStatus === 'low') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($this->stockStatus === 'available') {
                $query->where('stock', '>', 10);
            }
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('stock', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Kategori',
            'Barcode',
            'Stok',
            'Harga Beli',
            'Harga Jual',
            'Nilai Stok (Beli)',
            'Potensi Revenue',
            'Potensi Profit',
            'Status',
        ];
    }

    public function map($product): array
    {
        $stockValue = $product->stock * $product->harga_beli;
        $potentialRevenue = $product->stock * $product->harga_jual;
        $potentialProfit = $potentialRevenue - $stockValue;

        $status = $product->stock === 0 ? 'HABIS' :
                  ($product->stock <= 5 ? 'KRITIS' :
                  ($product->stock <= 10 ? 'RENDAH' : 'TERSEDIA'));

        return [
            $product->id,
            $product->name,
            $product->category->name ?? '-',
            $product->barcode ?? '-',
            $product->stock,
            number_format($product->harga_beli, 0, ',', '.'),
            number_format($product->harga_jual, 0, ',', '.'),
            number_format($stockValue, 0, ',', '.'),
            number_format($potentialRevenue, 0, ',', '.'),
            number_format($potentialProfit, 0, ',', '.'),
            $status,
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
                    'startColor' => ['rgb' => '3B82F6'],
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
            'A' => 12,  // Kode Produk
            'B' => 30,  // Nama Produk
            'C' => 20,  // Kategori
            'D' => 15,  // Barcode
            'E' => 10,  // Stok
            'F' => 15,  // Harga Beli
            'G' => 15,  // Harga Jual
            'H' => 18,  // Nilai Stok (Beli)
            'I' => 18,  // Potensi Revenue
            'J' => 18,  // Potensi Profit
            'K' => 12,  // Status
        ];
    }
}
