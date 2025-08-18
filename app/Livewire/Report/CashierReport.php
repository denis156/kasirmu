<?php

declare(strict_types=1);

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Laporan Kasir')]
class CashierReport extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $period = 'today'; // today, week, month, custom
    public string $selectedCashier = '';
    public array $performanceChart = [];
    public array $transactionChart = [];

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

    public function updatedSelectedCashier()
    {
        $this->loadCharts();
    }

    // Get cashier summary statistics
    public function summaryStats()
    {
        $totalCashiers = DB::table('users')->count();

        $activeCashiers = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->distinct('users.id')
            ->count();

        $totalTransactions = DB::table('transactions')
            ->where('status', 'selesai')
            ->whereDate('transaction_date', '>=', $this->dateFrom)
            ->whereDate('transaction_date', '<=', $this->dateTo)
            ->count();

        $totalSales = DB::table('transactions')
            ->where('status', 'selesai')
            ->whereDate('transaction_date', '>=', $this->dateFrom)
            ->whereDate('transaction_date', '<=', $this->dateTo)
            ->sum('total_amount');

        return [
            'total_cashiers' => $totalCashiers,
            'active_cashiers' => $activeCashiers,
            'total_transactions' => $totalTransactions,
            'total_sales' => $totalSales,
        ];
    }

    // Get all cashiers for filter
    public function cashiers()
    {
        return DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    // Get cashier performance ranking
    public function cashierPerformance()
    {
        $query = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_sales'),
                DB::raw('AVG(transactions.total_amount) as avg_transaction'),
                DB::raw('SUM(transactions.total_amount) / COUNT(transactions.id) as performance_score')
            )
            ->groupBy('users.id', 'users.name');

        // Apply cashier filter if selected
        if ($this->selectedCashier) {
            $query->where('users.id', $this->selectedCashier);
        }

        return $query->orderBy('total_sales', 'desc')->get();
    }

    // Get daily performance for selected cashier or all
    public function dailyPerformance()
    {
        $query = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                DB::raw('DATE(transactions.transaction_date) as date'),
                'users.name as cashier_name',
                DB::raw('COUNT(transactions.id) as transaction_count'),
                DB::raw('SUM(transactions.total_amount) as daily_sales')
            );

        // Apply cashier filter if selected
        if ($this->selectedCashier) {
            $query->where('users.id', $this->selectedCashier);
        }

        return $query->groupBy('date', 'users.id', 'users.name')
            ->orderBy('date')
            ->orderBy('daily_sales', 'desc')
            ->get();
    }

    // Get top performing cashiers
    public function topCashiers()
    {
        return DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                'users.name',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total_amount) as total_sales'),
                DB::raw('AVG(transactions.total_amount) as avg_transaction')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_sales', 'desc')
            ->limit(5)
            ->get();
    }

    // Get cashier transaction details
    public function cashierTransactions()
    {
        return DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', $this->dateFrom)
            ->whereDate('transactions.transaction_date', '<=', $this->dateTo)
            ->select(
                'transactions.*',
                'users.name as cashier_name'
            )
            ->when($this->selectedCashier, function ($query) {
                return $query->where('users.id', $this->selectedCashier);
            })
            ->orderBy('transactions.transaction_date', 'desc')
            ->limit(20)
            ->get();
    }

    // Load chart data
    public function loadCharts()
    {
        $this->loadPerformanceChart();
        $this->loadTransactionChart();
    }

    public function loadPerformanceChart()
    {
        $performanceData = $this->cashierPerformance();

        $labels = $performanceData->pluck('name')->toArray();
        $salesData = $performanceData->pluck('total_sales')->map(fn($amount) => (float) $amount)->toArray();
        $transactionData = $performanceData->pluck('total_transactions')->map(fn($count) => (int) $count)->toArray();

        $this->performanceChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Penjualan (Rp)',
                        'data' => $salesData,
                        'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
                        'borderColor' => 'rgba(139, 92, 246, 1)',
                        'borderWidth' => 1,
                        'yAxisID' => 'y'
                    ],
                    [
                        'label' => 'Jumlah Transaksi',
                        'data' => $transactionData,
                        'backgroundColor' => 'rgba(244, 114, 182, 0.8)',
                        'borderColor' => 'rgba(244, 114, 182, 1)',
                        'borderWidth' => 1,
                        'yAxisID' => 'y1'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'beginAtZero' => true
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'beginAtZero' => true,
                        'grid' => [
                            'drawOnChartArea' => false
                        ]
                    ]
                ]
            ]
        ];
    }

    public function loadTransactionChart()
    {
        $dailyData = $this->dailyPerformance();

        // Group by date
        $groupedData = $dailyData->groupBy('date');

        $labels = $groupedData->keys()->map(fn($date) => Carbon::parse($date)->format('M j'))->toArray();
        $totalSales = $groupedData->map(fn($group) => $group->sum('daily_sales'))->values()->toArray();
        $totalTransactions = $groupedData->map(fn($group) => $group->sum('transaction_count'))->values()->toArray();

        $this->transactionChart = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Penjualan Harian (Rp)',
                        'data' => $totalSales,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'borderColor' => 'rgba(16, 185, 129, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.4
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

    public function render()
    {
        return view('livewire.report.cashier-report');
    }
}
