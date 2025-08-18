<div>
    <!-- HEADER -->
    <x-header title="Laporan Stok" subtitle="Analisis dan monitoring inventory toko • Beta Version - Sedang dikembangkan"
        icon="phosphor.package" icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Export PDF" icon="phosphor.file-pdf" class="btn-error btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
            <x-button label="Export Excel" icon="phosphor.file-xls" class="btn-success btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY STATISTICS -->
    <x-card title="Ringkasan Stok" subtitle="Status inventory dan nilai stok saat ini" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat title="Total Produk" description="Produk aktif dalam sistem"
                value="{{ number_format($this->stockSummary()['total_products']) }}" icon="phosphor.package"
                class="bg-info/10 text-info border border-info/20 shadow-sm shadow-info" />

            <x-stat title="Stok Menipis" description="Produk perlu restock"
                value="{{ number_format($this->stockSummary()['low_stock_count']) }}" icon="phosphor.warning"
                class="bg-warning/10 text-warning border border-warning/20 shadow-sm shadow-warning" />

            <x-stat title="Stok Habis" description="Produk kehabisan stok"
                value="{{ number_format($this->stockSummary()['out_of_stock_count']) }}" icon="phosphor.x-circle"
                class="bg-error/10 text-error border border-error/20 shadow-sm shadow-error" />

            <x-stat title="Nilai Stok" description="Total nilai inventory"
                value="Rp {{ number_format($this->stockSummary()['total_stock_value'], 2, ',', '.') }}"
                icon="phosphor.currency-circle-dollar"
                class="bg-success/10 text-success border border-success/20 shadow-sm shadow-success" />
        </div>
    </x-card>

    <!-- FILTERS -->
    <x-card title="Filter Produk" subtitle="Filter berdasarkan status stok dan kategori" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-input label="Pencarian" wire:model.live.debounce="search" placeholder="Nama produk atau SKU..."
                    icon="phosphor.magnifying-glass" />
            </div>
            <div>
                <x-select label="Status Stok" wire:model.live="filterStatus" :options="$this->getStatusOptions()" option-value="id"
                    option-label="name" icon="phosphor.warning" />
            </div>
            <div>
                <x-select label="Kategori" wire:model.live="filterCategory" :options="$this->categories()->toArray()" option-value="id"
                    option-label="name" placeholder="Semua kategori" icon="phosphor.tag" />
            </div>
            <div class="flex items-end">
                <x-button label="Reset Filter" icon="phosphor.x" class="btn-outline w-full"
                    wire:click="$set('filterStatus', ''); $set('filterCategory', ''); $set('search', '')" />
            </div>
        </div>
    </x-card>

    <!-- CHARTS AND URGENT ALERTS -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
        <!-- STOCK STATUS CHART -->
        <div class="xl:col-span-1">
            <x-card title="Status Stok" subtitle="Distribusi status inventory" shadow
                class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
                <x-chart wire:model="stockChart" />
            </x-card>
        </div>

        <!-- URGENT STOCK ALERTS -->
        <div class="xl:col-span-2">
            <x-card title="Alert Stok Menipis" subtitle="Produk yang perlu segera direstock" shadow
                class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
                @if ($this->lowStockProducts()->count() > 0)
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach ($this->lowStockProducts() as $product)
                            <div
                                class="flex items-center justify-between p-3 bg-warning/10 border border-warning/20 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-base-content">{{ $product->name }}</h4>
                                    <p class="text-sm text-base-content/60">SKU: {{ $product->sku }}</p>
                                    <p class="text-sm text-base-content/60">Kategori: {{ $product->category_name }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-warning">{{ $product->stock }}</div>
                                    <div class="text-xs text-base-content/60">Min: {{ $product->min_stock }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="phosphor.check-circle" class="w-12 h-12 mx-auto mb-3 text-success" />
                        <h3 class="text-lg font-semibold text-base-content mb-2">Semua stok aman</h3>
                        <p class="text-base-content/60">Tidak ada produk yang perlu direstock</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- TABLES SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- STOCK MOVEMENT TABLE -->
        <x-card title="Pergerakan Stok" subtitle="Produk terlaris 30 hari terakhir" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->stockMovement()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->stockMovement() as $product)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-base-content/60">{{ $product->category_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if ($product->stock <= $product->min_stock)
                                            <x-badge value="{{ $product->stock }}" class="badge-warning badge-sm" />
                                        @else
                                            <x-badge value="{{ $product->stock }}" class="badge-success badge-sm" />
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <x-badge value="{{ $product->total_sold }}" class="badge-info badge-sm" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.chart-bar" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada data penjualan</h3>
                    <p class="text-base-content/60">Data akan muncul setelah ada transaksi</p>
                </div>
            @endif
        </x-card>

        <!-- ALL PRODUCTS TABLE (FILTERED) -->
        <x-card title="Daftar Produk" subtitle="Produk berdasarkan filter yang dipilih" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->products()->count() > 0)
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="table table-zebra table-sm">
                        <thead class="sticky top-0 bg-base-200">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Stok</th>
                                <th class="text-right">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->products() as $product)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-base-content/60">{{ $product->sku }}</div>
                                        <div class="text-xs text-base-content/60">{{ $product->category_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if ($product->stock_status === 'out_of_stock')
                                            <x-badge value="0" class="badge-error badge-sm" />
                                        @elseif($product->stock_status === 'low_stock')
                                            <x-badge value="{{ $product->stock }}" class="badge-warning badge-sm" />
                                        @elseif($product->stock_status === 'overstock')
                                            <x-badge value="{{ $product->stock }}" class="badge-info badge-sm" />
                                        @else
                                            <x-badge value="{{ $product->stock }}" class="badge-success badge-sm" />
                                        @endif
                                    </td>
                                    <td class="text-right font-medium">
                                        Rp {{ number_format($product->stock_value, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.magnifying-glass" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Tidak ada produk</h3>
                    <p class="text-base-content/60">Coba ubah filter untuk melihat produk</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
