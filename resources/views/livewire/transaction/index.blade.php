<div>
    <!-- HEADER -->
    <x-header title="Daftar Transaksi" subtitle="Kelola transaksi di halaman ini" icon="phosphor.receipt"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="phosphor.funnel"
                class="btn-sm btn-primary" />
            <x-button icon="phosphor.cash-register" class="btn-sm btn-success btn-outline" link="{{ route('products.create') }}"
                label="Kasir" responsive />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card
        class="overflow-x-auto rounded-box border border-base-content/10 p-2 mt-4 bg-base-100 shadow-sm shadow-primary">
        <x-table :headers="$this->headers()" :rows="$this->transactions()" with-pagination striped>
            <x-slot:empty>
                <div class="text-center py-16">
                    <x-icon name="phosphor.receipt" class="w-16 h-16 mx-auto mb-4 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada transaksi</h3>
                    <p class="text-base-content/60 mb-6">Transaksi akan muncul setelah ada pembayaran dari kasir</p>
                    <x-button label="Ke Kasir" icon="phosphor.cash-register" class="btn-primary btn-sm"
                        link="{{ route('cashier') }}" />
                </div>
            </x-slot:empty>
            @scope('cell_no', $transaction)
                {{ ($this->transactions()->currentPage() - 1) * $this->transactions()->perPage() + $loop->iteration }}
            @endscope
            @scope('cell_total_amount', $transaction)
                <span class="font-medium">{{ $transaction->total_amount_formatted }}</span>
            @endscope
            @scope('cell_payment_method', $transaction)
                @php
                    $methodClass = match ($transaction->payment_method) {
                        'tunai' => 'badge-success',
                        'kartu' => 'badge-info',
                        'transfer' => 'badge-warning',
                        'qris' => 'badge-primary',
                        default => 'badge-neutral',
                    };
                    $methodLabel = match ($transaction->payment_method) {
                        'tunai' => 'Tunai',
                        'kartu' => 'Kartu',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        default => $transaction->payment_method,
                    };
                @endphp
                <x-badge value="{{ $methodLabel }}" class="badge-sm badge-soft {{ $methodClass }}" />
            @endscope
            @scope('cell_status', $transaction)
                @php
                    $statusClass = match ($transaction->status) {
                        'selesai' => 'badge-success',
                        'menunggu' => 'badge-warning',
                        'dibatalkan' => 'badge-error',
                        default => 'badge-neutral',
                    };
                    $statusLabel = match ($transaction->status) {
                        'selesai' => 'Selesai',
                        'menunggu' => 'Menunggu',
                        'dibatalkan' => 'Dibatalkan',
                        default => $transaction->status,
                    };
                @endphp
                <x-badge value="{{ $statusLabel }}" class="badge-sm badge-soft {{ $statusClass }}" />
            @endscope
            @scope('cell_transaction_date', $transaction)
                <span class="text-sm">{{ $transaction->transaction_date_formatted }}</span>
            @endscope
            @scope('actions', $transaction)
                <div class="flex gap-2">
                    <x-button icon="phosphor.trash" wire:click="showDeleteModal({{ $transaction->id }})" spinner
                        class="btn-sm btn-outline btn-error" label="Hapus" responsive />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" class="modal-bottom sm:modal-middle backdrop-blur" title="Konfirmasi Hapus"
        persistent>
        @if ($transactionToDelete)
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus transaksi
                <strong>{{ $transactionToDelete->transaction_code }}</strong>?
            </p>
            <p class="text-sm text-gray-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
        @endif

        <x-slot:actions>
            <x-button icon="phosphor.x" label="Batal" class="btn-soft" wire:click="cancelDelete" responsive />
            <x-button icon="phosphor.check" label="Hapus" class="btn-error" wire:click="confirmDelete" responsive />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Transaksi" right separator with-close-button class="lg:w-1/3">
        <!-- Search Input -->
        <div>
            <x-input label="Cari Transaksi" wire:model.live.debounce="search" placeholder="Kode transaksi atau kasir..."
                clearable icon="phosphor.magnifying-glass" />
        </div>

        <!-- Filter Payment Method -->
        <div class="mt-4">
            <x-select label="Metode Pembayaran" wire:model.live="filterPaymentMethod" :options="$this->getPaymentMethodFilterOptions()"
                icon="phosphor.credit-card" option-value="id" option-label="name" placeholder="Semua metode" />
        </div>

        <!-- Filter Status -->
        <div class="mt-4">
            <x-select label="Status" wire:model.live="filterStatus" :options="$this->getStatusFilterOptions()" icon="phosphor.check-circle"
                option-value="id" option-label="name" placeholder="Semua status" />
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="phosphor.x" wire:click="clearFilters" spinner />
            <x-button label="Tutup" icon="phosphor.check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
