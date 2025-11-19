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

class StudentTransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dateFrom;
    protected $dateTo;
    protected $class;
    protected $studentId;
    protected $search;

    public function __construct($dateFrom, $dateTo, $class = null, $studentId = null, $search = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->class = $class;
        $this->studentId = $studentId;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Sale::with(['student.user', 'saleItems.product'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->class) {
            $query->whereHas('student', function($q) {
                $q->where('class', $this->class);
            });
        }

        if ($this->studentId) {
            $query->where('student_id', $this->studentId);
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
            'Nama Barang',
            'Total Barang (QTY)',
            'Total Transaksi',
            'Metode Pembayaran',
            'Saldo Akhir Siswa',
        ];
    }

    public function map($sale): array
    {
        // Get ending balance from transaction
        $transaction = \App\Models\Transaction::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('student_id', $sale->student_id)
            ->first();

        // Combine all products into readable string
        $products = $sale->saleItems->map(function($item) {
            return $item->product->name . ' (' . $item->quantity . 'x)';
        })->join(', ');

        $totalQty = $sale->saleItems->sum('quantity');

        return [
            $sale->created_at->format('d/m/Y H:i:s'),
            $sale->student->user->name,
            $sale->student->nis,
            $sale->student->class,
            $products,
            $totalQty,
            $sale->total,
            $sale->payment_method === 'cash' ? 'Tunai' : 'Saldo',
            $transaction ? $transaction->ending_balance : $sale->student->balance,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6']
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Transaksi Siswa';
    }
}
