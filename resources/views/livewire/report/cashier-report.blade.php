<div>
    <!-- HEADER -->
    <x-header title="Laporan Kasir"
        subtitle="Analisis performa dan produktivitas kasir • Beta Version - Sedang dikembangkan" icon="phosphor.user"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Export PDF" icon="phosphor.file-pdf" class="btn-error btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
            <x-button label="Export Excel" icon="phosphor.file-xls" class="btn-success btn-sm"
                tooltip="Sedang dalam pengembangan" responsive />
        </x-slot:actions>
    </x-header>

    <!-- SUMMARY STATISTICS -->
    <x-card title="Ringkasan Performa Kasir" subtitle="Statistik dan produktivitas kasir dalam periode" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-stat title="Total Kasir" description="Kasir aktif dalam sistem"
                value="{{ number_format($this->summaryStats()['total_cashiers']) }}" icon="phosphor.users"
                class="bg-info/10 text-info border border-info/20 shadow-sm shadow-info" />

            <x-stat title="Kasir Aktif" description="Kasir yang bertransaksi"
                value="{{ number_format($this->summaryStats()['active_cashiers']) }}" icon="phosphor.user-check"
                class="bg-success/10 text-success border border-success/20 shadow-sm shadow-success" />

            <x-stat title="Total Transaksi" description="Transaksi dalam periode"
                value="{{ number_format($this->summaryStats()['total_transactions']) }}" icon="phosphor.receipt"
                class="bg-primary/10 text-primary border border-primary/20 shadow-sm shadow-primary" />

            <x-stat title="Total Penjualan" description="Pendapatan periode ini"
                value="Rp {{ number_format($this->summaryStats()['total_sales'], 0, ',', '.') }}"
                icon="phosphor.currency-circle-dollar"
                class="bg-warning/10 text-warning border border-warning/20 shadow-sm shadow-warning" />
        </div>
    </x-card>

    <!-- FILTERS -->
    <x-card title="Filter Laporan" subtitle="Pilih periode dan kasir untuk analisis" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
            <div>
                <x-select label="Kasir" wire:model.live="selectedCashier" :options="$this->cashiers()->toArray()" option-value="id"
                    option-label="name" placeholder="Semua kasir" icon="phosphor.user" />
            </div>
            <div class="flex items-end">
                <x-button label="Refresh" icon="phosphor.arrow-clockwise" class="btn-primary w-full"
                    wire:click="loadCharts" />
            </div>
        </div>
    </x-card>

    <!-- CHARTS SECTION -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <!-- PERFORMANCE CHART -->
        <x-card title="Performa Kasir" subtitle="Penjualan dan jumlah transaksi per kasir" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
            <x-chart wire:model="performanceChart" />
        </x-card>

        <!-- DAILY TREND CHART -->
        <x-card title="Tren Penjualan Harian" subtitle="Grafik penjualan per hari" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary h-full">
            <x-chart wire:model="transactionChart" />
        </x-card>
    </div>

    <!-- TABLES SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- TOP CASHIERS TABLE -->
        <x-card title="Top 5 Kasir Terbaik" subtitle="Ranking kasir berdasarkan penjualan" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->topCashiers()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Nama Kasir</th>
                                <th class="text-center">Transaksi</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->topCashiers() as $index => $cashier)
                                <tr>
                                    <td class="text-center">
                                        @if ($index === 0)
                                            <div
                                                class="w-8 h-8 bg-yellow-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                                🏆
                                            </div>
                                        @elseif($index === 1)
                                            <div
                                                class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </div>
                                        @elseif($index === 2)
                                            <div
                                                class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </div>
                                        @else
                                            <div
                                                class="w-8 h-8 bg-primary text-primary-content rounded-full flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $cashier->name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <x-badge value="{{ number_format($cashier->total_transactions) }}"
                                            class="badge-info badge-sm" />
                                    </td>
                                    <td class="text-right font-medium">
                                        Rp {{ number_format($cashier->total_sales, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right text-sm text-base-content/70">
                                        Rp {{ number_format($cashier->avg_transaction, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.users" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada data kasir</h3>
                    <p class="text-base-content/60">Data akan muncul setelah ada transaksi</p>
                </div>
            @endif
        </x-card>

        <!-- RECENT TRANSACTIONS TABLE -->
        <x-card title="Transaksi Terbaru" subtitle="20 transaksi terakhir dalam periode" shadow
            class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary">
            @if ($this->cashierTransactions()->count() > 0)
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="table table-zebra table-sm">
                        <thead class="sticky top-0 bg-base-200">
                            <tr>
                                <th>Waktu</th>
                                <th>Kasir</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->cashierTransactions() as $transaction)
                                <tr>
                                    <td>
                                        <div class="text-sm font-medium">
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m H:i') }}
                                        </div>
                                        <div class="text-xs text-base-content/60">
                                            {{ $transaction->invoice_number }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-sm">{{ $transaction->cashier_name }}</div>
                                    </td>
                                    <td class="text-right">
                                        <div class="font-medium">
                                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="phosphor.receipt" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada transaksi</h3>
                    <p class="text-base-content/60">Transaksi akan muncul setelah kasir melakukan penjualan</p>
                </div>
            @endif
        </x-card>
    </div>

    <!-- DETAILED PERFORMANCE TABLE -->
    <x-card title="Detail Performa Kasir" subtitle="Analisis lengkap performa semua kasir" shadow
        class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-6">
        @if ($this->cashierPerformance()->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Nama Kasir</th>
                            <th class="text-center">Total Transaksi</th>
                            <th class="text-right">Total Penjualan</th>
                            <th class="text-right">Rata-rata Transaksi</th>
                            <th class="text-center">Performa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->cashierPerformance() as $cashier)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $cashier->name }}</div>
                                </td>
                                <td class="text-center">
                                    <x-badge value="{{ number_format($cashier->total_transactions) }}"
                                        class="badge-info" />
                                </td>
                                <td class="text-right font-medium">
                                    Rp {{ number_format($cashier->total_sales, 0, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    Rp {{ number_format($cashier->avg_transaction, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if ($cashier->total_sales >= 1000000)
                                        <x-badge value="Excellent" class="badge-success" />
                                    @elseif($cashier->total_sales >= 500000)
                                        <x-badge value="Good" class="badge-info" />
                                    @elseif($cashier->total_sales >= 100000)
                                        <x-badge value="Average" class="badge-warning" />
                                    @else
                                        <x-badge value="Need Improvement" class="badge-error" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-base-200 font-bold">
                        <tr>
                            <td>TOTAL</td>
                            <td class="text-center">{{ number_format($this->summaryStats()['total_transactions']) }}
                            </td>
                            <td class="text-right">Rp
                                {{ number_format($this->summaryStats()['total_sales'], 0, ',', '.') }}</td>
                            <td class="text-right">
                                Rp
                                {{ number_format($this->summaryStats()['total_transactions'] > 0 ? $this->summaryStats()['total_sales'] / $this->summaryStats()['total_transactions'] : 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <x-icon name="phosphor.chart-bar" class="w-12 h-12 mx-auto mb-3 text-base-content/40" />
                <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada data performa</h3>
                <p class="text-base-content/60">Data akan muncul setelah kasir melakukan transaksi</p>
            </div>
        @endif
    </x-card>
</div>
