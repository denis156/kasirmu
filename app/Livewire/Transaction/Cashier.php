<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use Mary\Traits\Toast;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Transaction;
use Livewire\Attributes\Title;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

#[Title('Kasir')]
class Cashier extends Component
{
    use Toast;

    public array $cart = [];
    public string $search = '';
    public string $selectedCategory = '';
    public float $total = 0;
    public float $paidAmount = 0;
    public float $changeAmount = 0;
    public string $paymentMethod = 'tunai';
    public string $notes = '';
    public $taxRate = 0.0;
    public $discountAmount = 0.0;
    public int $productsLimit = 4;

    public bool $paymentModal = false;
    public bool $showFilterDrawer = false;


    protected $listeners = [
        'addToCart' => 'addToCart'
    ];

    public function mount(): void
    {
        $this->taxRate = 0.0;
        $this->discountAmount = 0.0;
        $this->total = 0.0;
    }

    public function addToCart($productId): void
    {
        $product = Product::with('category')->find($productId);

        if (!$product || !$product->is_active || $product->stock <= 0) {
            $this->error('Produk tidak tersedia atau stok habis.');
            return;
        }

        $existingItemIndex = collect($this->cart)->search(function ($item) use ($productId) {
            return $item['product_id'] == $productId;
        });

        if ($existingItemIndex !== false) {
            if ($this->cart[$existingItemIndex]['quantity'] >= $product->stock) {
                $this->error('Stok tidak mencukupi.');
                return;
            }
            $this->cart[$existingItemIndex]['quantity']++;
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
            ];
        }

        $this->calculateTotal();
        $this->success("'{$product->name}' ditambahkan ke keranjang.");
    }

    public function updateQuantity($index, $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);
            return;
        }

        if ($quantity > $this->cart[$index]['stock']) {
            $this->error('Jumlah melebihi stok yang tersedia.');
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->calculateTotal();
    }

    public function removeFromCart($index): void
    {
        $productName = $this->cart[$index]['name'];
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
        $this->success("'{$productName}' dihapus dari keranjang.");
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->calculateTotal();
        $this->success('Keranjang dikosongkan.');
    }

    public function calculateTotal(): void
    {
        $subtotal = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $taxAmount = $subtotal * ((float) $this->taxRate / 100);
        $this->total = max(0, $subtotal + $taxAmount - (float) $this->discountAmount);
    }

    public function showPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->error('Keranjang masih kosong.');
            return;
        }

        $this->paidAmount = $this->total;
        $this->calculateChange();
        $this->paymentModal = true;
    }

    public function calculateChange(): void
    {
        $this->changeAmount = max(0, $this->paidAmount - $this->total);
    }

    public function updatedPaidAmount(): void
    {
        $this->calculateChange();
    }

    public function updatedTaxRate(): void
    {
        $this->calculateTotal();
    }

    public function updatedDiscountAmount(): void
    {
        $this->calculateTotal();
    }

    public function processPayment(): void
    {
        if ($this->paidAmount < $this->total) {
            $this->error('Jumlah bayar tidak mencukupi.');
            return;
        }

        if (!Auth::check()) {
            $this->error('Anda harus login terlebih dahulu.');
            return;
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'transaction_code' => Transaction::generateTransactionCode(),
                'user_id' => Auth::id(),
                'tax_rate' => $this->taxRate,
                'discount_amount' => $this->discountAmount,
                'total_amount' => $this->total,
                'paid_amount' => $this->paidAmount,
                'change_amount' => $this->changeAmount,
                'payment_method' => $this->paymentMethod,
                'status' => 'selesai',
                'notes' => $this->notes,
                'transaction_date' => now(),
            ]);

            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                $product = Product::find($item['product_id']);
                $product->stock -= $item['quantity'];
                $product->terjual += $item['quantity'];
                $product->save();
            }

            DB::commit();

            $this->success('Transaksi berhasil diproses.');
            $this->resetTransaction();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error: ' . $e->getMessage());
            $this->error('Terjadi kesalahan saat memproses transaksi: ' . $e->getMessage());
        }
    }

    public function resetTransaction(): void
    {
        $this->cart = [];
        $this->total = 0;
        $this->paidAmount = 0;
        $this->changeAmount = 0;
        $this->paymentMethod = 'tunai';
        $this->notes = '';
        $this->taxRate = 0;
        $this->discountAmount = 0;
        $this->paymentModal = false;
    }


    // Products List Methods
    public function getProductsProperty()
    {
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.barcode',
                'products.price',
                'products.stock',
                'products.min_stock',
                'products.is_active',
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

                        // Calculate remaining stock after cart items
                        $cartQuantity = collect($this->cart)->where('product_id', $product->id)->sum('quantity');
                        $product->available_stock = $product->stock - $cartQuantity;

                        return $product;
                    });
    }

    public function getCategoriesProperty()
    {
        return DB::table('categories')
                  ->select('id', 'name')
                  ->where('is_active', true)
                  ->orderBy('name')
                  ->get();
    }

    public function getTotalProductsCountProperty()
    {
        $query = DB::table('products')
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

        return $query->count();
    }


    public function loadMoreProducts(): void
    {
        $this->productsLimit += 4;
    }

    public function loadLessProducts(): void
    {
        $this->productsLimit = max(4, $this->productsLimit - 4);
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->showFilterDrawer = false;
    }

    public function getPaymentMethodOptions(): array
    {
        return [
            ['id' => 'tunai', 'name' => 'Tunai'],
            ['id' => 'kartu', 'name' => 'Kartu'],
            ['id' => 'transfer', 'name' => 'Transfer'],
            ['id' => 'qris', 'name' => 'QRIS'],
        ];
    }

    public function render()
    {
        return view('livewire.transaction.cashier');
    }
}
