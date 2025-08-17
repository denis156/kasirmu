<x-card class="rounded-box border border-base-content/10 bg-base-100 shadow-sm shadow-primary">
    <!-- Header -->
    <x-header title="Daftar Produk" subtitle="Pilih produk untuk ditambahkan ke keranjang" icon="phosphor.package"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.showFilterDrawer = true" responsive icon="phosphor.funnel"
                class="btn-sm btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        @forelse($this->products as $product)
            <x-card class="rounded-box border border-base-content/10 bg-base-100 shadow-sm shadow-success"
                wire:click="addToCart({{ $product->id }})">
                <div class="p-4 h-full flex flex-col">
                    <!-- Product Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-sm line-clamp-2 mb-1">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ $product->sku }}</p>
                        </div>
                        @if($product->category)
                            <x-badge value="{{ Str::words($product->category->name, 1, '...') }}"
                                class="badge-xs badge-soft badge-info ml-2 shrink-0" />
                        @endif
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <span class="text-lg font-bold text-primary">{{ $product->price_formatted }}</span>
                    </div>

                    <!-- Stock Info -->
                    <div class="flex justify-between items-center mb-4 flex-grow">
                        <div class="flex items-center text-xs">
                            <x-icon name="phosphor.stack" class="w-3 h-3 mr-1 shrink-0" />
                            <span>Stok: {{ $product->available_stock }}</span>
                        </div>
                        @if($product->available_stock <= $product->min_stock)
                            <x-badge value="Rendah" class="badge-xs badge-error shrink-0" />
                        @else
                            <x-badge value="Tersedia" class="badge-xs badge-success shrink-0" />
                        @endif
                    </div>

                    <!-- Add to Cart Button -->
                    <div class="mt-auto">
                        <x-button icon="phosphor.basket" class="btn-sm btn-success w-full"
                            label="Tambahkan" wire:click.stop="addToCart({{ $product->id }})"
                            :disabled="$product->available_stock <= 0" responsive/>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card class="rounded-box border border-base-content/10 bg-base-100 shadow-sm shadow-base-content">
                    <div class="text-center py-12 text-gray-500">
                        <x-icon name="phosphor.package" class="w-16 h-16 mx-auto mb-4 opacity-50" />
                        <h3 class="font-medium mb-2">Tidak ada produk ditemukan</h3>
                        <p class="text-sm">Coba ubah kata kunci pencarian atau filter kategori</p>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>

    <!-- Load More/Less Buttons -->
    @if($this->products->count() >= $productsLimit)
        @if($productsLimit > 4)
            <div class="grid grid-cols-2 gap-2 mt-4">
                <x-button label="Lebih Banyak" icon="phosphor.plus"
                    class="btn-sm btn-primary btn-block" wire:click="loadMoreProducts" />
                <x-button label="Lebih Sedikit" icon="phosphor.minus"
                    class="btn-sm btn-outline btn-block" wire:click="loadLessProducts" />
            </div>
        @else
            <div class="mt-4">
                <x-button label="Lebih Banyak" icon="phosphor.plus"
                    class="btn-sm btn-primary btn-block" wire:click="loadMoreProducts" />
            </div>
        @endif
    @endif

    <!-- Filter Drawer -->
    <x-drawer wire:model="showFilterDrawer" title="Filter Produk" right separator with-close-button class="lg:w-1/3">
        <!-- Search Input -->
        <div>
            <x-input label="Cari Produk" wire:model.live.debounce="search"
                placeholder="Nama, SKU, atau Barcode..." clearable
                icon="phosphor.magnifying-glass" />
        </div>

        <!-- Category Filter -->
        <div class="mt-4">
            <x-select label="Kategori" wire:model.live="selectedCategory" :options="$this->categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])" icon="phosphor.tag"
                option-value="id" option-label="name" placeholder="Semua kategori" />
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="phosphor.x" wire:click="resetFilters" spinner />
            <x-button label="Tutup" icon="phosphor.check" class="btn-primary" @click="$wire.showFilterDrawer = false" />
        </x-slot:actions>
    </x-drawer>
</x-card>
