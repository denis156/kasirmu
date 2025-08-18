<div>
    <!-- HEADER -->
    <x-header title="Daftar Produk" subtitle="Kelola produk di halaman ini" icon="phosphor.package"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="phosphor.funnel"
                class="btn-sm btn-primary" />
            <x-button icon="phosphor.plus" class="btn-sm btn-success btn-outline" link="{{ route('products.create') }}"
                label="Tambah Produk" responsive />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card
        class="overflow-x-auto rounded-box border border-base-content/10 p-2 mt-4 bg-base-100 shadow-sm shadow-primary">
        <x-table :headers="$this->headers()" :rows="$this->products()" with-pagination striped>
            <x-slot:empty>
                <div class="text-center py-16">
                    <x-icon name="phosphor.package" class="w-16 h-16 mx-auto mb-4 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada produk</h3>
                    <p class="text-base-content/60 mb-6">Mulai dengan menambahkan produk pertama ke toko Anda</p>
                    <x-button label="Tambah Produk" icon="phosphor.plus" class="btn-success btn-sm"
                        link="{{ route('products.create') }}" />
                </div>
            </x-slot:empty>
            @scope('cell_no', $product)
                {{ ($this->products()->currentPage() - 1) * $this->products()->perPage() + $loop->iteration }}
            @endscope
            @scope('cell_category_name', $product)
                @if ($product->category_name)
                    <x-badge value="{{ Str::words($product->category_name, 1, '...') }}"
                        class="badge-sm badge-soft badge-info" />
                @else
                    <x-badge value="Belum diatur" class="badge-sm badge-soft badge-error" />
                @endif
            @endscope
            @scope('cell_price', $product)
                <span class="font-medium">{{ $product->price_formatted }}</span>
            @endscope
            @scope('cell_stock', $product)
                @if ($product->stock <= $product->min_stock)
                    <x-badge value="{{ $product->stock }}" class="badge-sm badge-soft badge-error" />
                @else
                    <x-badge value="{{ $product->stock }}" class="badge-sm badge-soft badge-success" />
                @endif
            @endscope
            @scope('cell_terjual', $product)
                <x-badge value="{{ $product->terjual }}" class="badge-sm badge-soft badge-info" />
            @endscope
            @scope('cell_is_active', $product)
                @if ($product->is_active)
                    <x-badge value="Aktif" class="badge-sm badge-soft badge-success" />
                @else
                    <x-badge value="Nonaktif" class="badge-sm badge-soft badge-error" />
                @endif
            @endscope
            @scope('actions', $product)
                <div class="flex gap-2">
                    <x-button icon="phosphor.pencil" link="{{ route('products.edit', $product->id) }}"
                        class="btn-sm btn-warning" label="Edit" responsive />
                    <x-button icon="phosphor.trash" wire:click="showDeleteModal({{ $product->id }})" spinner
                        class="btn-sm btn-outline btn-error" label="Hapus" responsive />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" class="modal-bottom sm:modal-middle backdrop-blur" title="Konfirmasi Hapus"
        persistent>
        @if ($productToDelete)
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus produk
                <strong>{{ $productToDelete->name }}</strong>?
            </p>
            <p class="text-sm text-gray-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
        @endif

        <x-slot:actions>
            <x-button icon="phosphor.x" label="Batal" class="btn-soft" wire:click="cancelDelete" responsive />
            <x-button icon="phosphor.check" label="Hapus" class="btn-error" wire:click="confirmDelete" responsive />
        </x-slot:actions>
    </x-modal>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filter Produk" right separator with-close-button class="lg:w-1/3">
        <!-- Search Input -->
        <div>
            <x-input label="Cari Produk" wire:model.live.debounce="search"
                placeholder="Nama, SKU, atau Barcode..." clearable
                icon="phosphor.magnifying-glass" />
        </div>

        <!-- Filter Kategori -->
        <div class="mt-4">
            <x-select label="Kategori" wire:model.live="filterCategory" :options="$this->getCategoryFilterOptions()" icon="phosphor.tag"
                option-value="id" option-label="name" placeholder="Semua kategori" />
        </div>

        <!-- Filter Status -->
        <div class="mt-4">
            <x-select label="Status" wire:model.live="filterStatus" :options="$this->getStatusFilterOptions()" icon="phosphor.check-circle"
                option-value="id" option-label="name" placeholder="Semua status" />
        </div>

        <!-- Filter Stok -->
        <div class="mt-4">
            <x-select label="Stok" wire:model.live="filterStock" :options="$this->getStockFilterOptions()" icon="phosphor.stack"
                option-value="id" option-label="name" placeholder="Semua stok" />
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="phosphor.x" wire:click="clearFilters" spinner />
            <x-button label="Tutup" icon="phosphor.check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
