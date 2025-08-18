<?php

declare(strict_types=1);

namespace App\Livewire\Product;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

#[Title('Daftar Produk')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public int $perPage = 10;

    public bool $deleteModal = false;

    public ?object $productToDelete = null;

    // Filter properties
    public bool $drawer = false;

    public string $filterCategory = '';

    public string $filterStatus = '';

    public string $filterStock = '';

    // Show delete modal
    public function showDeleteModal($id): void
    {
        $this->productToDelete = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->where('products.id', $id)
            ->first();
        $this->deleteModal = true;
    }

    // Confirm delete
    public function confirmDelete(): void
    {
        if ($this->productToDelete) {
            DB::table('products')->where('id', $this->productToDelete->id)->delete();
            $this->success("Produk '{$this->productToDelete->name}' berhasil dihapus.");
            $this->productToDelete = null;
        }
        $this->deleteModal = false;
    }

    // Cancel delete
    public function cancelDelete(): void
    {
        $this->productToDelete = null;
        $this->deleteModal = false;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'no', 'label' => 'No.', 'sortable' => false, 'disableLink' => true],
            ['key' => 'name', 'label' => 'Nama Produk'],
            ['key' => 'sku', 'label' => 'SKU', 'sortable' => false],
            ['key' => 'category_name', 'label' => 'Kategori', 'disableLink' => true],
            ['key' => 'price', 'label' => 'Harga', 'disableLink' => true],
            ['key' => 'stock', 'label' => 'Stok', 'disableLink' => true],
            ['key' => 'terjual', 'label' => 'Terjual', 'disableLink' => true],
            ['key' => 'is_active', 'label' => 'Status', 'disableLink' => true],
        ];
    }

    public function products()
    {
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.*',
                'categories.name as category_name'
            );

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                  ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                  ->orWhere('categories.name', 'like', '%' . $this->search . '%');
            });
        }

        // Apply category filter
        if ($this->filterCategory) {
            if ($this->filterCategory === 'null') {
                $query->whereNull('products.category_id');
            } else {
                $query->where('products.category_id', $this->filterCategory);
            }
        }

        // Apply status filter
        if ($this->filterStatus !== '') {
            $query->where('products.is_active', (bool) $this->filterStatus);
        }

        // Apply stock filter
        if ($this->filterStock) {
            switch ($this->filterStock) {
                case 'low':
                    $query->whereRaw('products.stock <= products.min_stock');
                    break;
                case 'normal':
                    $query->whereRaw('products.stock > products.min_stock');
                    break;
                case 'empty':
                    $query->where('products.stock', 0);
                    break;
            }
        }


        // Apply sorting
        $sortColumn = $this->sortBy['column'];
        if ($sortColumn === 'category_name') {
            $query->orderBy('categories.name', $this->sortBy['direction']);
        } else {
            $query->orderBy('products.' . $sortColumn, $this->sortBy['direction']);
        }

        // Get total count
        $total = $query->count();

        // Get current page
        $currentPage = Paginator::resolveCurrentPage();

        // Get items for current page
        $items = $query->offset(($currentPage - 1) * $this->perPage)
                      ->limit($this->perPage)
                      ->get();

        // Convert to objects with proper boolean casting
        $items = $items->map(function ($item) {
            $item->is_active = (bool) $item->is_active;
            $item->price_formatted = 'Rp ' . number_format($item->price, 2, ',', '.');
            return $item;
        });

        // Return paginator
        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    // Clear all filters
    public function clearFilters(): void
    {
        $this->reset(['filterCategory', 'filterStatus', 'filterStock']);
        $this->success('Filter berhasil direset.');
    }

    public function getCategoryFilterOptions(): array
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $options = $categories->map(function ($category) {
            return ['id' => (string) $category->id, 'name' => $category->name];
        })->toArray();

        // Tambahkan opsi untuk kategori yang belum diatur
        array_unshift($options, ['id' => 'null', 'name' => 'Belum diatur']);

        return $options;
    }

    public function getStatusFilterOptions(): array
    {
        return [
            ['id' => '1', 'name' => 'Aktif'],
            ['id' => '0', 'name' => 'Nonaktif'],
        ];
    }

    public function getStockFilterOptions(): array
    {
        return [
            ['id' => 'low', 'name' => 'Stok Rendah'],
            ['id' => 'normal', 'name' => 'Stok Normal'],
            ['id' => 'empty', 'name' => 'Stok Kosong'],
        ];
    }

    public function render()
    {
        return view('livewire.product.index');
    }
}
