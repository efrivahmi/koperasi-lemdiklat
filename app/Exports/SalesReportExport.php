<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dateFrom;
    protected $dateTo;
    protected $paymentMethod;
    protected $search;

    public function __construct($dateFrom, $dateTo, $paymentMethod = null, $search = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->paymentMethod = $paymentMethod;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Sale::with(['student.user', 'saleItems.product'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if ($this->search) {
            $query->whereHas('student.user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })->orWhereHas('student', function($q) {
                $q->where('nis', 'like', '%' . $this->search . '%');
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal & Waktu',
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Barang',
            'Total QTY',
            'Metode Pembayaran',
            'Total Transaksi',
        ];
    }

    public function map($sale): array
    {
        // Combine all products into one string
        $products = $sale->saleItems->map(function($item) {
            return $item->product->name . ' (x' . $item->quantity . ')';
        })->join(', ');

        $totalQty = $sale->saleItems->sum('quantity');

        return [
            $sale->created_at->format('d/m/Y H:i'),
            $sale->student->user->name,
            $sale->student->nis,
            $sale->student->class,
            $products,
            $totalQty,
            $sale->payment_method === 'cash' ? 'Tunai' : 'Saldo',
            $sale->total,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
