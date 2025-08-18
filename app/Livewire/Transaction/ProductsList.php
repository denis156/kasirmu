<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProductsList extends Component
{
    public string $search = '';
    public string $selectedCategory = '';
    public int $productsLimit = 4;
    public bool $showFilterDrawer = false;

    protected $listeners = [];

    public function getProductsProperty()
    {
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                'categories.name as category_name'
            )
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                  ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                  ->orWhere('products.barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('products.category_id', $this->selectedCategory);
        }

        return $query->orderBy('products.name')
                    ->limit($this->productsLimit)
                    ->get()
                    ->map(function ($product) {
                        $product->price_formatted = 'Rp ' . number_format((float) $product->price, 2, ',', '.');
                        return $product;
                    });
    }

    public function getCategoriesProperty()
    {
        return DB::table('categories')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
    }

    public function getTotalProductsCountProperty()
    {
        $query = DB::table('products')
            ->where('products.is_active', true)
            ->where('products.stock', '>', 0);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                  ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                  ->orWhere('products.barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('products.category_id', $this->selectedCategory);
        }

        return $query->count();
    }

    public function loadMoreProducts()
    {
        $this->productsLimit += 4;
    }

    public function loadLessProducts()
    {
        $this->productsLimit = max(4, $this->productsLimit - 4);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedCategory']);
    }

    public function render()
    {
        return view('livewire.transaction.products-list');
    }
}
