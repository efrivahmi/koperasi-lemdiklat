<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
            $product->category->name,
            $product->barcode ?? '-',
            $product->stock,
            $product->harga_beli,
            $product->harga_jual,
            $stockValue,
            $potentialRevenue,
            $potentialProfit,
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981']
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Inventaris';
    }
}
