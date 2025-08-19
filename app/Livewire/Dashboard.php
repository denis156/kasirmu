<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Beranda')]
class Dashboard extends Component
{
    public array $salesChart = [];

    public function mount()
    {
        $this->loadSalesChart();
    }


    // Get today's statistics
    public function todayStats()
    {
        $today = now()->format('Y-m-d');

        // Use single query with subqueries for better performance
        $stats = DB::table('transactions')
            ->selectRaw('
                COALESCE(SUM(CASE WHEN status = "selesai" THEN total_amount END), 0) as sales,
                COUNT(CASE WHEN status = "selesai" THEN 1 END) as transactions
            ')
            ->whereDate('transaction_date', $today)
            ->first();

        $products_sold = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereDate('transactions.transaction_date', $today)
            ->where('transactions.status', 'selesai')
            ->sum('transaction_items.quantity');

        return [
            'sales' => (float) $stats->sales,
            'transactions' => (int) $stats->transactions,
            'products_sold' => (int) $products_sold,
        ];
    }

    // Get monthly statistics
    public function monthlyStats()
    {
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        // Use single query with better date filtering for performance
        $stats = DB::table('transactions')
            ->selectRaw('
                COALESCE(SUM(CASE WHEN status = "selesai" THEN total_amount END), 0) as sales,
                COUNT(CASE WHEN status = "selesai" THEN 1 END) as transactions
            ')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->first();

        return [
            'sales' => (float) $stats->sales,
            'transactions' => (int) $stats->transactions,
        ];
    }

    // Get low stock products
    public function lowStockProducts()
    {
        return DB::table('products')
            ->whereRaw('stock <= min_stock')
            ->where('is_active', true)
            ->select('name', 'stock', 'min_stock')
            ->limit(5)
            ->get();
    }

    // Get top selling products
    public function topProducts()
    {
        return DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
            ->select('products.name', DB::raw('SUM(transaction_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
    }

    // Get recent transactions
    public function recentTransactions()
    {
        return DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', 'selesai')
            ->orderBy('transactions.transaction_date', 'desc')
            ->limit(5)
            ->select(
                'transactions.id', 
                'transactions.total_amount', 
                'transactions.transaction_date', 
                'users.name as cashier_name', 
                'transactions.transaction_code'
            )
            ->get();
    }

    // Load sales chart data
    public function loadSalesChart()
    {
        $salesData = DB::table('transactions')
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
            ->where('status', 'selesai')
            ->whereDate('transaction_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');

            $sale = $salesData->firstWhere('date', $date);
            $data[] = $sale ? (float) $sale->total : 0;
        }

        $this->salesChart = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Penjualan (Rp)',
                        'data' => $data,
                        'borderColor' => 'rgb(139, 92, 246)',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
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
        return view('livewire.dashboard');
    }
}
