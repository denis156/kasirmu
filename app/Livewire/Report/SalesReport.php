<?php

declare(strict_types=1);

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Laporan Penjualan')]
class SalesReport extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $period = 'today'; // today, week, month, custom
    public array $salesChart = [];
    public array $categoryChart = [];

    public function mount()
    {
        $this->initializeDates();
        $this->loadCharts();
    }

    public function initializeDates()
    {
        switch ($this->period) {
            case 'today':
                $this->dateFrom = now()->format('Y-m-d');
                $this->dateTo = now()->format('Y-m-d');
                break;
            case 'week':
                $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            default:
                if (!$this->dateFrom) $this->dateFrom = now()->subDays(30)->format('Y-m-d');
                if (!$this->dateTo) $this->dateTo = now()->format('Y-m-d');
                break;
        }
    }

    public function updatedPeriod()
    {
        $this->initializeDates();
        $this->loadCharts();
    }

    public function updatedDateFrom()
    {
        $this->period = 'custom';
        $this->loadCharts();
    }

    public function updatedDateTo()
    {
        $this->period = 'custom';
        $this->loadCharts();
    }

    // Get summary statistics
    public function summaryStats()
    {
        $query = DB::table('transactions')
            ->where('status', 'selesai')
            ->whereDate('transaction_date', '>=', $this->dateFrom)
            ->whereDate('transaction_date', '<=', $this->dateTo);

        $totalSales = $query->sum('total_amount');
        $totalTransactions = $query->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Get total items sold
        $totalItems = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->sum('transaction_items.quantity');

        return [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'avg_transaction' => $avgTransaction,
            'total_items' => $totalItems,
        ];
    }

    // Get top selling products
    public function topProducts()
    {
        return DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_qty'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_amount')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();
    }

    // Get daily sales data
    public function dailySales()
    {
        return DB::table('transactions')
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->where('status', 'selesai')
            ->whereDate('transaction_date', '>=', $this->dateFrom)
            ->whereDate('transaction_date', '<=', $this->dateTo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Load chart data
    public function loadCharts()
    {
        $this->loadSalesChart();
        $this->loadCategoryChart();
    }

    public function loadSalesChart()
    {
        $salesData = $this->dailySales();

        $labels = [];
        $data = [];

        foreach ($salesData as $sale) {
            $labels[] = Carbon::parse($sale->date)->format('M j');
            $data[] = (float) $sale->total;
        }

        $this->salesChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Penjualan (Rp)',
                        'data' => $data,
                        'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
                        'borderColor' => 'rgba(139, 92, 246, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];
    }

    public function loadCategoryChart()
    {
        $categoryData = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as name'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.unit_price) as total_amount')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        $labels = $categoryData->pluck('name')->toArray();
        $data = $categoryData->pluck('total_amount')->map(fn($amount) => (float) $amount)->toArray();

        $colors = [
            'rgba(139, 92, 246, 0.8)',
            'rgba(244, 114, 182, 0.8)',
            'rgba(6, 182, 212, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
        ];

        $this->categoryChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.report.sales-report');
    }
}
