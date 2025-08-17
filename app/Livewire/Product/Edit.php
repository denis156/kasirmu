<?php

declare(strict_types=1);

namespace App\Livewire\Product;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Title('Edit Produk')]
class Edit extends Component
{
    use Toast;

    public int $productId;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('required|integer|min:0')]
    public string $stock = '';

    #[Validate('required|integer|min:0')]
    public string $min_stock = '';

    #[Validate('nullable|exists:categories,id')]
    public string $category_id = '';

    #[Validate('nullable|string|max:255')]
    public string $barcode = '';

    #[Validate('required|in:0,1')]
    public string $is_active = '1';

    // Category modal properties
    public bool $createCategoryModal = false;
    public string $newCategoryName = '';
    public string $newCategoryDescription = '';

    public function mount(int $id): void
    {
        $this->productId = $id;
        $this->loadProduct();
    }

    public function loadProduct(): void
    {
        $product = DB::table('products')->where('id', $this->productId)->first();

        if (!$product) {
            $this->error('Produk tidak ditemukan.', redirectTo: route('products.index'));
            return;
        }

        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->stock = (string) $product->stock;
        $this->min_stock = (string) $product->min_stock;
        $this->category_id = $product->category_id ? (string) $product->category_id : '';
        $this->barcode = $product->barcode ?? '';
        $this->is_active = (string) (int) $product->is_active;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
            
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'min_stock.integer' => 'Stok minimum harus berupa angka bulat.',
            'min_stock.min' => 'Stok minimum tidak boleh negatif.',
            
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            
            'barcode.string' => 'Barcode harus berupa teks.',
            'barcode.max' => 'Barcode maksimal 255 karakter.',
            
            'is_active.required' => 'Status produk wajib dipilih.',
            'is_active.in' => 'Status produk tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama produk',
            'description' => 'deskripsi',
            'price' => 'harga',
            'stock' => 'stok',
            'min_stock' => 'stok minimum',
            'category_id' => 'kategori',
            'barcode' => 'barcode',
            'is_active' => 'status produk',
        ];
    }

    public function simpan(): void
    {
        $this->validate();

        try {
            $productData = [
                'name' => $this->name,
                'description' => $this->description ?: null,
                'price' => (float) $this->price,
                'stock' => (int) $this->stock,
                'min_stock' => (int) $this->min_stock,
                'category_id' => $this->category_id ?: null,
                'barcode' => $this->barcode ?: null,
                'is_active' => (bool) $this->is_active,
                'updated_at' => now(),
            ];

            DB::table('products')->where('id', $this->productId)->update($productData);

            $this->success('Produk berhasil diupdate!', redirectTo: route('products.index'));
        } catch (Exception) {
            $this->error('Gagal mengupdate produk. Silakan coba lagi.');
        }
    }

    public function getCategoryOptions(): array
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $categories->map(function ($category) {
            return ['id' => (string) $category->id, 'name' => $category->name];
        })->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            ['id' => '1', 'name' => 'Aktif'],
            ['id' => '0', 'name' => 'Nonaktif'],
        ];
    }

    public function showCreateCategoryModal(): void
    {
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';
        $this->createCategoryModal = true;
    }

    public function saveCategory(): void
    {
        if (empty($this->newCategoryName)) {
            $this->error('Nama kategori wajib diisi.');
            return;
        }

        try {
            $categoryData = [
                'name' => $this->newCategoryName,
                'slug' => Str::slug($this->newCategoryName),
                'description' => $this->newCategoryDescription ?: null,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $categoryId = DB::table('categories')->insertGetId($categoryData);

            $this->category_id = (string) $categoryId;
            $this->createCategoryModal = false;
            $this->newCategoryName = '';
            $this->newCategoryDescription = '';

            $this->success('Kategori berhasil dibuat dan dipilih!');
        } catch (Exception) {
            $this->error('Gagal membuat kategori. Silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.product.edit');
    }
}