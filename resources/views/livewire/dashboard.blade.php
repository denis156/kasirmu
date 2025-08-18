<div>
    <!-- HEADER -->
    <x-header title="Beranda" subtitle="Ringkasan aktivitas toko Anda" icon="phosphor.gauge"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Ke Kasir" icon="phosphor.cash-register" class="btn-primary btn-sm"
                link="{{ route('cashier') }}" />
        </x-slot:actions>
    </x-header>

    <!-- TODAY'S STATISTICS -->
    <x-card title="Statistik Hari Ini" subtitle="Performa penjualan hari ini" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-stat
                    title="Total Penjualan"
                    description="Pendapatan hari ini"
                    value="Rp {{ number_format($this->todayStats()['sales'], 2, ',', '.') }}"
                    icon="phosphor.currency-circle-dollar"
                    class="bg-success/10 text-success border border-success/20 shadow-sm shadow-success" />
            </div>
            <div>
                <x-stat
                    title="Transaksi"
                    description="Jumlah transaksi hari ini"
                    value="{{ $this->todayStats()['transactions'] }}"
                    icon="phosphor.receipt"
                    class="bg-info/10 text-info border border-info/20 shadow-sm shadow-info" />
            </div>
            <div>
                <x-stat
                    title="Produk Terjual"
                    description="Total item terjual hari ini"
                    value="{{ $this->todayStats()['products_sold'] }}"
                    icon="phosphor.package"
                    class="bg-warning/10 text-warning border border-warning/20 shadow-sm shadow-warning" />
            </div>
        </div>
    </x-card>

    <!-- MONTHLY STATISTICS -->
    <x-card title="Statistik Bulan Ini" subtitle="Performa penjualan bulan ini" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-stat
                    title="Total Penjualan Bulan Ini"
                    description="Pendapatan {{ now()->format('F Y') }}"
                    value="Rp {{ number_format($this->monthlyStats()['sales'], 2, ',', '.') }}"
                    icon="phosphor.trend-up"
                    class="bg-primary/10 text-primary border border-primary/20 shadow-sm shadow-primary" />
            </div>
            <div>
                <x-stat
                    title="Total Transaksi Bulan Ini"
                    description="Jumlah transaksi {{ now()->format('F Y') }}"
                    value="{{ $this->monthlyStats()['transactions'] }}"
                    icon="phosphor.chart-line"
                    class="bg-secondary/10 text-secondary border border-secondary/20 shadow-sm shadow-secondary" />
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- SALES CHART -->
        <x-card title="Grafik Penjualan 7 Hari" subtitle="Tren penjualan minggu terakhir" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            <x-chart wire:model="salesChart" />
        </x-card>

        <!-- LOW STOCK ALERT -->
        <x-card title="Stok Menipis" subtitle="Produk yang perlu diisi ulang" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if($this->lowStockProducts()->count() > 0)
                <div class="space-y-3">
                    @foreach($this->lowStockProducts() as $product)
                        <div class="flex items-center justify-between p-3 bg-error/10 border border-error/20 rounded-lg">
                            <div>
                                <h4 class="font-medium text-base-content">{{ $product->name }}</h4>
                                <p class="text-sm text-base-content/60">Stok: {{ $product->stock }} / Min: {{ $product->min_stock }}</p>
                            </div>
                            <x-icon name="phosphor.warning" class="w-5 h-5 text-error" />
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-button label="Kelola Produk" icon="phosphor.package" class="btn-error btn-sm btn-outline w-full"
                        link="{{ route('products.index') }}" />
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.check-circle" class="w-12 h-12 mx-auto mb-3 text-success" />
                    <p class="text-base-content/60">Semua produk memiliki stok yang cukup</p>
                </div>
            @endif
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- TOP PRODUCTS -->
        <x-card title="Produk Terlaris" subtitle="Top 5 produk terlaris 30 hari terakhir" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if($this->topProducts()->count() > 0)
                <div class="space-y-3">
                    @foreach($this->topProducts() as $index => $product)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-primary text-primary-content rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="font-medium text-base-content">{{ $product->name }}</h4>
                                    <p class="text-sm text-base-content/60">Terjual {{ $product->total_sold }} unit</p>
                                </div>
                            </div>
                            <x-icon name="phosphor.crown" class="w-5 h-5 text-warning" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.chart-bar" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <p class="text-base-content/60">Belum ada data penjualan</p>
                </div>
            @endif
        </x-card>

        <!-- RECENT TRANSACTIONS -->
        <x-card title="Transaksi Terbaru" subtitle="5 transaksi terakhir yang selesai" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if($this->recentTransactions()->count() > 0)
                <div class="space-y-3">
                    @foreach($this->recentTransactions() as $transaction)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                            <div>
                                <h4 class="font-medium text-base-content">
                                    {{ $transaction->customer_name ?: 'Umum' }}
                                </h4>
                                <p class="text-sm text-base-content/60">
                                    {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-success">
                                    Rp {{ number_format($transaction->total_amount, 2, ',', '.') }}
                                </p>
                                <p class="text-xs text-base-content/60">#{{ $transaction->id }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-button label="Lihat Semua" icon="phosphor.list" class="btn-primary btn-sm btn-outline w-full"
                        link="{{ route('transactions.index') }}" />
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.receipt" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <p class="text-base-content/60">Belum ada transaksi hari ini</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
