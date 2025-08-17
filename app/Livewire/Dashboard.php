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

        return [
            'sales' => DB::table('transactions')
                ->whereDate('transaction_date', $today)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'transactions' => DB::table('transactions')
                ->whereDate('transaction_date', $today)
                ->where('status', 'completed')
                ->count(),
            'products_sold' => DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.transaction_date', $today)
                ->where('transactions.status', 'completed')
                ->sum('transaction_items.quantity'),
        ];
    }

    // Get monthly statistics
    public function monthlyStats()
    {
        $thisMonth = now()->format('Y-m');

        return [
            'sales' => DB::table('transactions')
                ->where('transaction_date', 'like', "$thisMonth%")
                ->where('status', 'completed')
                ->sum('total_amount'),
            'transactions' => DB::table('transactions')
                ->where('transaction_date', 'like', "$thisMonth%")
                ->where('status', 'completed')
                ->count(),
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
            ->where('transactions.status', 'completed')
            ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
            ->select('products.name', DB::raw('SUM(transaction_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
    }

    // Load sales chart data
    public function loadSalesChart()
    {
        $salesData = DB::table('transactions')
            ->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
            ->where('status', 'completed')
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
