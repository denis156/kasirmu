<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProductsList extends Component
{
    public string $search = '';
    public string $selectedCategory = '';

    protected $listeners = [];

    public function getProductsProperty()
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return $query->orderBy('name')
                    ->limit(50)
                    ->get()
                    ->map(function ($product) {
                        $product->price_formatted = 'Rp ' . number_format((float) $product->price, 0, ',', '.');
                        return $product;
                    });
    }

    public function getCategoriesProperty()
    {
        return Category::where('is_active', true)
                      ->orderBy('name')
                      ->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.transaction.products-list', [
            'products' => $this->products,
            'categories' => $this->categories,
        ]);
    }
}
