<div>
    <!-- HEADER -->
    <x-header title="Laporan Penjualan"
        subtitle="Analisis dan statistik penjualan toko • Beta Version - Sedang dikembangkan" icon="phosphor.chart-line"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Export PDF" icon="phosphor.file-pdf" class="btn-error btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
            <x-button label="Export Excel" icon="phosphor.file-xls" class="btn-success btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY STATISTICS -->
    <x-card title="Ringkasan Penjualan" subtitle="Statistik periode yang dipilih" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat title="Total Penjualan" description="Pendapatan periode ini"
                value="Rp {{ number_format($this->summaryStats()['total_sales'], 2, ',', '.') }}"
                icon="phosphor.currency-circle-dollar"
                class="bg-success/10 text-success border border-success/20 shadow-sm shadow-success" />

            <x-stat title="Jumlah Transaksi" description="Total transaksi selesai"
                value="{{ number_format($this->summaryStats()['total_transactions']) }}" icon="phosphor.receipt"
                class="bg-info/10 text-info border border-info/20 shadow-sm shadow-info" />

            <x-stat title="Rata-rata Transaksi" description="Nilai rata-rata per transaksi"
                value="Rp {{ number_format($this->summaryStats()['avg_transaction'], 2, ',', '.') }}"
                icon="phosphor.chart-line"
                class="bg-primary/10 text-primary border border-primary/20 shadow-sm shadow-primary" />

            <x-stat title="Total Item Terjual" description="Jumlah produk terjual"
                value="{{ number_format($this->summaryStats()['total_items']) }}" icon="phosphor.package"
                class="bg-warning/10 text-warning border border-warning/20 shadow-sm shadow-warning" />
        </div>
    </x-card>

    <!-- FILTERS -->
    <x-card title="Filter Periode" subtitle="Pilih rentang tanggal untuk laporan" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-select label="Periode" wire:model.live="period" :options="[
                    ['id' => 'today', 'name' => 'Hari Ini'],
                    ['id' => 'week', 'name' => 'Minggu Ini'],
                    ['id' => 'month', 'name' => 'Bulan Ini'],
                    ['id' => 'custom', 'name' => 'Custom'],
                ]" option-value="id"
                    option-label="name" icon="phosphor.calendar" />
            </div>
            <div>
                <x-input label="Dari Tanggal" wire:model.live="dateFrom" type="date" icon="phosphor.calendar" />
            </div>
            <div>
                <x-input label="Sampai Tanggal" wire:model.live="dateTo" type="date" icon="phosphor.calendar" />
            </div>
            <div class="flex items-end">
                <x-button label="Refresh" icon="phosphor.arrow-clockwise" class="btn-primary w-full"
                    wire:click="loadCharts" />
            </div>
        </div>
    </x-card>

    <!-- CHARTS SECTION -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
        <!-- SALES CHART - Takes 2/3 width on XL screens -->
        <div class="xl:col-span-2">
            <x-card title="Grafik Penjualan Harian" subtitle="Tren penjualan per hari" shadow
                class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
                <x-chart wire:model="salesChart" />
            </x-card>
        </div>

        <!-- CATEGORY CHART - Takes 1/3 width on XL screens -->
        <div class="xl:col-span-1">
            <x-card title="Penjualan per Kategori" subtitle="Distribusi berdasarkan kategori" shadow
                class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
                <x-chart wire:model="categoryChart" />
            </x-card>
        </div>
    </div>

    <!-- TABLES SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- TOP PRODUCTS TABLE -->
        <x-card title="Produk Terlaris" subtitle="10 produk dengan penjualan tertinggi" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->topProducts()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Qty Terjual</th>
                                <th class="text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->topProducts() as $index => $product)
                                <tr>
                                    <td class="text-center">
                                        <div
                                            class="w-8 h-8 bg-primary text-primary-content rounded-full flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $product->name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <x-badge value="{{ number_format($product->total_qty) }}" class="badge-info" />
                                    </td>
                                    <td class="text-right font-medium">
                                        Rp {{ number_format($product->total_amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <x-icon name="phosphor.chart-bar" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada data penjualan</h3>
                    <p class="text-base-content/60">Data akan muncul setelah ada transaksi di periode ini</p>
                </div>
            @endif
        </x-card>

        <!-- DAILY SALES TABLE -->
        <x-card title="Penjualan Harian" subtitle="Detail penjualan per hari dalam periode" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->dailySales()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-center">Jumlah Transaksi</th>
                                <th class="text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->dailySales() as $sale)
                                <tr>
                                    <td class="font-medium">
                                        {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <x-badge value="{{ $sale->count }}" class="badge-info" />
                                    </td>
                                    <td class="text-right font-medium">
                                        Rp {{ number_format($sale->total, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-base-200 font-bold">
                                <td>TOTAL</td>
                                <td class="text-center">{{ $this->summaryStats()['total_transactions'] }}</td>
                                <td class="text-right">Rp
                                    {{ number_format($this->summaryStats()['total_sales'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <x-icon name="phosphor.calendar-x" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada penjualan</h3>
                    <p class="text-base-content/60">Belum ada transaksi pada periode yang dipilih</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
