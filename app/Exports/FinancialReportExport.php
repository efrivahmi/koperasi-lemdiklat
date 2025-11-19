<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialReportExport implements WithMultipleSheets
{
    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function sheets(): array
    {
        return [
            new FinancialSummarySheet($this->dateFrom, $this->dateTo),
            new ExpensesSheet($this->dateFrom, $this->dateTo),
        ];
    }
}

class FinancialSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        $sales = Sale::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])->get();
        $totalRevenue = $sales->sum('total');

        $totalCOGS = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->sum(DB::raw('sale_items.quantity * products.harga_beli'));

        $totalExpenses = Expense::whereBetween('expense_date', [$this->dateFrom, $this->dateTo])->sum('amount');

        $grossProfit = $totalRevenue - $totalCOGS;
        $netProfit = $grossProfit - $totalExpenses;

        return collect([
            ['Deskripsi', 'Nilai'],
            ['Total Pendapatan (Revenue)', $totalRevenue],
            ['Harga Pokok Penjualan (COGS)', $totalCOGS],
            ['Laba Kotor', $grossProfit],
            ['Total Pengeluaran Operasional', $totalExpenses],
            ['Laba Bersih', $netProfit],
            ['', ''],
            ['Periode', $this->dateFrom . ' s/d ' . $this->dateTo],
            ['Total Transaksi', $sales->count()],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB3B']]],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Keuangan';
    }
}

class ExpensesSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        return Expense::with('user')
            ->whereBetween('expense_date', [$this->dateFrom, $this->dateTo])
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Deskripsi',
            'Kategori',
            'Jumlah',
            'Dicatat Oleh',
            'Catatan',
        ];
    }

    public function map($expense): array
    {
        return [
            date('d/m/Y', strtotime($expense->expense_date)),
            $expense->description,
            $expense->category,
            $expense->amount,
            $expense->user->name,
            $expense->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EF4444']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Detail Pengeluaran';
    }
}
