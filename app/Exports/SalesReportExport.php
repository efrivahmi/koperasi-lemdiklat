<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
            'Tanggal',
            'Waktu',
            'Invoice',
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Metode Pembayaran',
            'Metode Transaksi',
            'Total',
            'Jumlah Tunai',
            'Kembalian',
            'Status',
            'Produk',
        ];
    }

    public function map($sale): array
    {
        $products = $sale->saleItems->map(function($item) {
            return $item->product->name . ' (' . $item->quantity . 'x)';
        })->join(', ');

        return [
            Carbon::parse($sale->created_at)->format('d/m/Y'),
            Carbon::parse($sale->created_at)->format('H:i:s'),
            '#' . $sale->id,
            $sale->student->user->name ?? 'Cash Sale',
            $sale->student->nis ?? '-',
            $sale->student->kelas ?? '-',
            $sale->payment_method === 'cash' ? 'Tunai' : 'Saldo',
            $this->getTransactionMethodText($sale->transaction_method ?? 'manual'),
            number_format($sale->total, 0, ',', '.'),
            number_format($sale->cash_amount ?? 0, 0, ',', '.'),
            number_format($sale->change_amount ?? 0, 0, ',', '.'),
            $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan',
            $products,
        ];
    }

    protected function getTransactionMethodText($method)
    {
        $texts = [
            'manual' => 'Manual',
            'rfid' => 'RFID Scanner',
            'barcode' => 'Barcode Scanner',
            'mixed' => 'Campuran',
        ];
        return $texts[$method] ?? $method;
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
                    'startColor' => ['rgb' => '14B8A6'],
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
            'C' => 10,  // Invoice
            'D' => 25,  // Nama Siswa
            'E' => 12,  // NIS
            'F' => 10,  // Kelas
            'G' => 18,  // Metode Pembayaran
            'H' => 18,  // Metode Transaksi
            'I' => 15,  // Total
            'J' => 15,  // Jumlah Tunai
            'K' => 15,  // Kembalian
            'L' => 12,  // Status
            'M' => 40,  // Produk
        ];
    }
}
