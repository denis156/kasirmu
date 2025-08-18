<?php

declare(strict_types=1);

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Laporan Stok')]
class StockReport extends Component
{
    public string $filterStatus = ''; // all, low_stock, out_of_stock, overstock
    public string $filterCategory = '';
    public string $search = '';
    public array $stockChart = [];

    public function mount()
    {
        $this->loadStockChart();
    }

    public function updatedFilterStatus()
    {
        $this->loadStockChart();
    }

    public function updatedFilterCategory()
    {
        $this->loadStockChart();
    }

    public function updatedSearch()
    {
        $this->loadStockChart();
    }

    // Get stock summary statistics
    public function stockSummary()
    {
        $totalProducts = DB::table('products')->where('is_active', true)->count();

        $lowStockCount = DB::table('products')
            ->where('is_active', true)
            ->whereRaw('stock <= min_stock')
            ->count();

        $outOfStockCount = DB::table('products')
            ->where('is_active', true)
            ->where('stock', 0)
            ->count();

        $totalStockValue = DB::table('products')
            ->where('is_active', true)
            ->selectRaw('SUM(stock * price) as total_value')
            ->value('total_value') ?? 0;

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_stock_value' => $totalStockValue,
        ];
    }

    // Get categories for filter
    public function categories()
    {
        return DB::table('categories')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    // Get stock status filter options
    public function getStatusOptions()
    {
        return [
            ['id' => '', 'name' => 'Semua Status'],
            ['id' => 'low_stock', 'name' => 'Stok Menipis'],
            ['id' => 'out_of_stock', 'name' => 'Stok Habis'],
            ['id' => 'overstock', 'name' => 'Stok Berlebih'],
        ];
    }

    // Get products based on filters
    public function products()
    {
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('(products.stock * products.price) as stock_value'),
                DB::raw('CASE
                    WHEN products.stock = 0 THEN "out_of_stock"
                    WHEN products.stock <= products.min_stock THEN "low_stock"
                    WHEN products.stock > (products.min_stock * 3) THEN "overstock"
                    ELSE "normal"
                END as stock_status')
            )
            ->where('products.is_active', true);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                    ->orWhere('products.sku', 'like', '%' . $this->search . '%');
            });
        }

        // Apply category filter
        if ($this->filterCategory) {
            $query->where('products.category_id', $this->filterCategory);
        }

        // Apply status filter
        if ($this->filterStatus) {
            switch ($this->filterStatus) {
                case 'low_stock':
                    $query->whereRaw('products.stock <= products.min_stock')
                        ->where('products.stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('products.stock', 0);
                    break;
                case 'overstock':
                    $query->whereRaw('products.stock > (products.min_stock * 3)');
                    break;
            }
        }

        return $query->orderBy('products.name')->get();
    }

    // Get low stock products (urgent)
    public function lowStockProducts()
    {
        return DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name')
            )
            ->where('products.is_active', true)
            ->whereRaw('products.stock <= products.min_stock')
            ->orderBy('products.stock', 'asc')
            ->limit(10)
            ->get();
    }

    // Get stock movement (products sold recently)
    public function stockMovement()
    {
        return DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.name',
                'products.stock',
                'products.min_stock',
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('SUM(transaction_items.quantity) as total_sold')
            )
            ->where('transactions.status', 'selesai')
            ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.name', 'products.stock', 'products.min_stock', 'categories.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();
    }

    // Load stock status chart
    public function loadStockChart()
    {
        $stockData = DB::table('products')
            ->selectRaw('
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock > 0 AND stock <= min_stock THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN stock > min_stock AND stock <= (min_stock * 3) THEN 1 ELSE 0 END) as normal_stock,
                SUM(CASE WHEN stock > (min_stock * 3) THEN 1 ELSE 0 END) as overstock
            ')
            ->where('is_active', true)
            ->first();

        $this->stockChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Stok Habis', 'Stok Menipis', 'Stok Normal', 'Stok Berlebih'],
                'datasets' => [
                    [
                        'data' => [
                            (int) $stockData->out_of_stock,
                            (int) $stockData->low_stock,
                            (int) $stockData->normal_stock,
                            (int) $stockData->overstock
                        ],
                        'backgroundColor' => [
                            'rgba(239, 68, 68, 0.8)',   // Red - Out of stock
                            'rgba(245, 158, 11, 0.8)',  // Orange - Low stock
                            'rgba(16, 185, 129, 0.8)',  // Green - Normal
                            'rgba(6, 182, 212, 0.8)'    // Blue - Overstock
                        ],
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
        return view('livewire.report.stock-report');
    }
}
