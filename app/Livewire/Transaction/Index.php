<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

#[Title('Daftar Transaksi')]
class Index extends Component
{
    use Toast, WithPagination;

    protected $listeners = [
        'midtransSuccess' => 'handleMidtransSuccess',
        'midtransPending' => 'handleMidtransPending',
        'midtransError' => 'handleMidtransError',
        'midtransClose' => 'handleMidtransClose'
    ];

    public string $search = '';
    public array $sortBy = ['column' => 'transaction_date', 'direction' => 'desc'];
    public int $perPage = 10;
    public bool $deleteModal = false;
    public ?object $transactionToDelete = null;

    // Filter properties
    public bool $drawer = false;
    public string $filterPaymentMethod = '';
    public string $filterStatus = '';

    // Show delete modal
    public function showDeleteModal($id): void
    {
        $this->transactionToDelete = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name')
            ->where('transactions.id', $id)
            ->first();
        $this->deleteModal = true;
    }

    // Confirm delete
    public function confirmDelete(): void
    {
        if ($this->transactionToDelete) {
            // Delete transaction items first
            DB::table('transaction_items')->where('transaction_id', $this->transactionToDelete->id)->delete();
            // Delete transaction
            DB::table('transactions')->where('id', $this->transactionToDelete->id)->delete();
            $this->success("Transaksi '{$this->transactionToDelete->transaction_code}' berhasil dihapus.");
            $this->transactionToDelete = null;
        }
        $this->deleteModal = false;
    }

    // Cancel delete
    public function cancelDelete(): void
    {
        $this->transactionToDelete = null;
        $this->deleteModal = false;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'no', 'label' => 'No.', 'sortable' => false, 'disableLink' => true],
            ['key' => 'transaction_code', 'label' => 'Kode Transaksi'],
            ['key' => 'user_name', 'label' => 'Kasir', 'disableLink' => true],
            ['key' => 'items', 'label' => 'Produk', 'disableLink' => true],
            ['key' => 'total_amount', 'label' => 'Total', 'disableLink' => true],
            ['key' => 'change_amount', 'label' => 'Kembalian', 'disableLink' => true],
            ['key' => 'payment_method', 'label' => 'Metode Bayar', 'disableLink' => true],
            ['key' => 'status', 'label' => 'Status', 'disableLink' => true],
            ['key' => 'transaction_date', 'label' => 'Tanggal'],
        ];
    }

    public function transactions()
    {
        $query = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.*',
                'users.name as user_name'
            );

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('transactions.transaction_code', 'like', '%' . $this->search . '%')
                  ->orWhere('users.name', 'like', '%' . $this->search . '%');
            });
        }

        // Apply payment method filter
        if ($this->filterPaymentMethod) {
            $query->where('transactions.payment_method', $this->filterPaymentMethod);
        }

        // Apply status filter
        if ($this->filterStatus) {
            $query->where('transactions.status', $this->filterStatus);
        }

        // Apply sorting
        $sortColumn = $this->sortBy['column'];
        if ($sortColumn === 'user_name') {
            $query->orderBy('users.name', $this->sortBy['direction']);
        } else {
            $query->orderBy('transactions.' . $sortColumn, $this->sortBy['direction']);
        }

        // Get total count
        $total = $query->count();

        // Get current page
        $currentPage = Paginator::resolveCurrentPage();

        // Get items for current page
        $items = $query->offset(($currentPage - 1) * $this->perPage)
                      ->limit($this->perPage)
                      ->get();

        // Format data
        $items = $items->map(function ($item) {
            $item->total_amount_formatted = 'Rp ' . number_format((float) $item->total_amount, 2, ',', '.');
            $item->change_amount_formatted = 'Rp ' . number_format((float) $item->change_amount, 2, ',', '.');
            $item->transaction_date_formatted = \Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y H:i');

            // Get transaction items
            $transactionItems = DB::table('transaction_items')
                ->where('transaction_id', $item->id)
                ->select('product_name', 'quantity')
                ->get();

            $item->items_formatted = $transactionItems->map(function ($txItem, $index) {
                return ($index + 1) . '. ' . $txItem->product_name . ' (' . $txItem->quantity . ' item)';
            })->join('<br>');

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
        $this->reset(['filterPaymentMethod', 'filterStatus']);
        $this->success('Filter berhasil direset.');
    }

    public function getPaymentMethodFilterOptions(): array
    {
        $options = [];
        $paymentMethods = \App\Models\Transaction::getPaymentMethods();
        
        foreach ($paymentMethods as $id => $name) {
            $options[] = ['id' => $id, 'name' => $name];
        }
        
        return $options;
    }

    public function getStatusFilterOptions(): array
    {
        $options = [];
        $statuses = \App\Models\Transaction::getStatuses();
        
        foreach ($statuses as $id => $name) {
            $options[] = ['id' => $id, 'name' => $name];
        }
        
        return $options;
    }

    public function continuePayment($transactionId): void
    {
        try {
            // Cek apakah payment gateway aktif
            $paymentGatewayEnabled = \App\Models\Setting::get('payment_gateway_enabled', false);
            if (!$paymentGatewayEnabled) {
                $this->error('Payment gateway tidak aktif. Hubungi administrator.');
                return;
            }

            // Optimisasi: gunakan Eloquent dengan eager loading
            $transaction = \App\Models\Transaction::with(['items', 'user'])
                ->where('id', $transactionId)
                ->where('status', 'menunggu')
                ->first();

            if (!$transaction) {
                $this->error('Transaksi tidak ditemukan atau sudah selesai.');
                return;
            }

            // Hitung subtotal dan tax dari relationship yang sudah di-load
            $subtotal = $transaction->items->sum(function ($item) {
                return $item->unit_price * $item->quantity;
            });
            $taxAmount = $subtotal * ($transaction->tax_rate / 100);

            // Prepare transaction data untuk modal dengan data yang sudah optimal
            $transactionData = [
                'items' => $transaction->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product_name,
                        'sku' => $item->product_sku,
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity
                    ];
                })->toArray(),
                'total' => $transaction->total_amount,
                'subtotal' => $subtotal,
                'tax_rate' => $transaction->tax_rate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $transaction->discount_amount,
                'payment_method' => $transaction->payment_method,
                'notes' => $transaction->notes ?? '',
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code
            ];

            // Open Midtrans modal
            $this->dispatch('openMidtransModal', $transactionData);

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function handleMidtransSuccess($result): void
    {
        $message = $result['message'] ?? 'Pembayaran berhasil! Transaksi telah dikonfirmasi.';
        $this->success($message);
    }

    public function handleMidtransPending($result): void
    {
        $message = $result['message'] ?? 'Pembayaran sedang diproses. Status akan diperbarui otomatis.';
        $this->info($message);
    }

    public function handleMidtransError($errorMessage): void
    {
        $this->error($errorMessage);
    }

    public function handleMidtransClose($message): void
    {
        $this->info($message ?? 'Pembayaran dibatalkan.');
    }

    public function render()
    {
        return view('livewire.transaction.index');
    }
}
