<?php

declare(strict_types=1);

namespace App\Livewire\Transaction;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

#[Title('Daftar Transaksi')]
class Index extends Component
{
    use Toast, WithPagination;

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
        return [
            ['id' => 'tunai', 'name' => 'Tunai'],
            ['id' => 'kartu', 'name' => 'Kartu'],
            ['id' => 'transfer', 'name' => 'Transfer'],
            ['id' => 'qris', 'name' => 'QRIS'],
        ];
    }

    public function getStatusFilterOptions(): array
    {
        return [
            ['id' => 'menunggu', 'name' => 'Menunggu'],
            ['id' => 'selesai', 'name' => 'Selesai'],
            ['id' => 'dibatalkan', 'name' => 'Dibatalkan'],
        ];
    }

    public function render()
    {
        return view('livewire.transaction.index');
    }
}
