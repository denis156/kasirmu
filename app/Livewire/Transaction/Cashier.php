<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use Mary\Traits\Toast;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Transaction;
use Livewire\Attributes\Title;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
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
    public string $paymentMethod = ''; // Kosongkan default
    public string $notes = '';
    public float $taxRate = 0.0;
    public float $discountAmount = 0.0;
    public int $productsLimit = 4;

    public bool $paymentModal = false;
    public bool $showFilterDrawer = false;
    public ?string $snapToken = null;

    protected $listeners = [
        'addToCart' => 'addToCart',
        'midtransSuccess' => 'handleMidtransSuccess',
        'midtransPending' => 'handleMidtransPending', 
        'midtransError' => 'handleMidtransError',
        'midtransClose' => 'handleMidtransClose'
    ];

    public function mount(): void
    {
        // Ambil tax rate dari settings
        $this->taxRate = (float) Setting::get('tax_rate', 11);
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

        // Handle money input yang mungkin berupa string dengan format ribuan
        $discountValue = $this->discountAmount;
        if (is_string($discountValue)) {
            // Hapus format ribuan dan konversi ke float
            $discountValue = (float) str_replace(',', '', $discountValue);
        } else {
            $discountValue = (float) $discountValue;
        }

        // Total = Subtotal + Pajak - Diskon (diskon mengurangi total)
        // Biarkan bisa minus jika diskon lebih besar dari subtotal+pajak
        $this->total = $subtotal + $taxAmount - $discountValue;
    }

    public function showPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->error('Keranjang masih kosong.');
            return;
        }

        // Reset payment method dan paid amount
        $this->paymentMethod = '';
        $this->paidAmount = 0;
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

    public function updatedDiscountAmount(): void
    {
        $this->calculateTotal();
    }

    public function updatedPaymentMethod(): void
    {
        // Set default paid amount berdasarkan payment method
        if ($this->paymentMethod === 'tunai') {
            $this->paidAmount = $this->total;
        } else {
            $this->paidAmount = 0;
        }

        $this->calculateChange();
    }

    public function getTaxRateFromSettings(): float
    {
        return (float) Setting::get('tax_rate', 11);
    }

    public function processPayment(): void
    {
        // Validasi metode pembayaran dipilih
        if (empty($this->paymentMethod)) {
            $this->error('Silakan pilih metode pembayaran.');
            return;
        }

        // Untuk metode tunai, cek jumlah bayar
        if ($this->paymentMethod === 'tunai' && $this->paidAmount < $this->total) {
            $this->error('Jumlah bayar tidak mencukupi.');
            return;
        }

        // Untuk metode non-tunai, gunakan ModalPaymentMidtrans
        if ($this->paymentMethod !== 'tunai') {
            // Prepare transaction data untuk modal
            $transactionData = $this->prepareTransactionData();
            
            // Tutup modal payment dulu
            $this->paymentModal = false;

            // Dispatch event untuk membuka ModalPaymentMidtrans
            $this->dispatch('openMidtransModal', $transactionData);
            return;
        }

        // Untuk tunai, langsung proses dengan status selesai
        $this->createTransaction('selesai');
    }

    public function resetTransaction(): void
    {
        $this->cart = [];
        $this->total = 0;
        $this->paidAmount = 0;
        $this->changeAmount = 0;
        $this->paymentMethod = ''; // Reset ke kosong
        $this->notes = '';
        // Ambil tax rate dari settings, jangan direset ke 0
        $this->taxRate = (float) Setting::get('tax_rate', 11);
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
            $query->where(function ($q) {
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
            $query->where(function ($q) {
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
        $methods = [
            ['id' => 'tunai', 'name' => 'Tunai']
        ];

        // Cek apakah payment gateway diaktifkan
        $paymentGatewayEnabled = Setting::get('payment_gateway_enabled', false);

        if ($paymentGatewayEnabled) {
            // Menggunakan data dari model Transaction
            $paymentMethods = Transaction::getPaymentMethods();
            
            // Skip tunai karena sudah ditambahkan di atas
            foreach ($paymentMethods as $id => $name) {
                if ($id !== 'tunai') {
                    $methods[] = ['id' => $id, 'name' => $name];
                }
            }
        }

        return $methods;
    }

    private function prepareTransactionData(): array
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $taxAmount = $subtotal * ($this->taxRate / 100);
        
        $discountValue = is_string($this->discountAmount) 
            ? (float) str_replace(',', '', $this->discountAmount)
            : (float) $this->discountAmount;

        return [
            'items' => $this->cart,
            'total' => $this->total,
            'subtotal' => $subtotal,
            'tax_rate' => $this->taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountValue,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes
        ];
    }


    public function handleMidtransSuccess($result): void
    {
        try {
            if (isset($result['create_transaction']) && $result['create_transaction']) {
                // Set payment details from Midtrans response
                $this->paidAmount = $this->total;
                $this->changeAmount = 0;

                // Create transaction dengan status yang ditentukan modal
                $status = $result['status'] ?? 'selesai';
                $midtransResult = $result['midtrans_result'] ?? [];
                $this->createTransaction($status, $midtransResult);
            } else {
                // Hanya tampilkan pesan jika tidak perlu create transaction
                $message = $result['message'] ?? 'Pembayaran berhasil!';
                $this->success($message);
            }
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat memproses transaksi.');
        }
    }

    public function handleMidtransPending($result): void
    {
        try {
            if (isset($result['create_transaction']) && $result['create_transaction']) {
                // Set payment details
                $this->paidAmount = $this->total;
                $this->changeAmount = 0;

                // Create transaction dengan status pending
                $status = $result['status'] ?? 'menunggu';
                $midtransResult = $result['midtrans_result'] ?? [];
                $this->createTransaction($status, $midtransResult);
            } else {
                // Hanya tampilkan pesan
                $message = $result['message'] ?? 'Pembayaran sedang diproses.';
                $this->info($message);
            }
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat memproses transaksi.');
        }
    }

    public function handleMidtransError($errorMessage): void
    {
        $this->error($errorMessage);
        // Buka kembali modal payment untuk retry
        $this->paymentModal = true;
    }

    public function handleMidtransClose($message): void
    {
        $this->info($message);
        // Buka kembali modal payment jika user membatalkan
        $this->paymentModal = true;
    }

    private function createTransaction(string $status, array $midtransResult = []): void
    {
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
                'status' => $status,
                'notes' => $this->notes . (!empty($midtransResult) ? ' | Midtrans Order ID: ' . ($midtransResult['order_id'] ?? 'N/A') : ''),
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

                // Update stock hanya jika status selesai
                if ($status === 'selesai') {
                    $product = Product::find($item['product_id']);
                    $product->stock -= $item['quantity'];
                    $product->terjual += $item['quantity'];
                    $product->save();
                }
            }

            DB::commit();

            $this->success('Transaksi berhasil diproses dengan status: ' . $status);
            $this->resetTransaction();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transaction.cashier');
    }
}
