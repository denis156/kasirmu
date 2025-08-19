<div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4">
        <!-- Products List Section -->
        <div class="lg:col-span-8">
            @include('livewire.transaction.products-list')
        </div>

        <!-- Cart & Calculator Section -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Cart Header -->
            <x-card class="rounded-box border border-base-content/10 bg-base-100 shadow-sm shadow-primary">
                <x-header title="Keranjang Belanja" subtitle="Daftar item yang dipilih" icon="phosphor.shopping-cart"
                    icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
                    <x-slot:actions>
                        @if(count($cart) > 0)
                            <x-button icon="phosphor.trash" class="btn-sm btn-error btn-outline"
                                wire:click="clearCart" label="Kosongkan" responsive />
                        @endif
                    </x-slot:actions>
                </x-header>

                <!-- Cart Items -->
                <div class="max-h-80 overflow-y-auto">
                    @if(count($cart) > 0)
                        <div class="space-y-3">
                            @foreach($cart as $index => $item)
                                <div class="flex items-center justify-between p-3 border rounded-lg bg-base-50">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm">{{ $item['name'] }}</h4>
                                        <p class="text-xs text-gray-500">{{ $item['sku'] }}</p>
                                        <p class="text-sm font-semibold text-primary">
                                            Rp {{ number_format($item['price'], 2, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center border rounded">
                                            <button
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                class="btn btn-xs btn-ghost">-</button>
                                            <span class="px-2 text-sm">{{ $item['quantity'] }}</span>
                                            <button
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                class="btn btn-xs btn-ghost">+</button>
                                        </div>
                                        <x-button icon="phosphor.x" class="btn-xs btn-error btn-outline"
                                            wire:click="removeFromCart({{ $index }})" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <x-icon name="phosphor.shopping-cart-simple" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p>Keranjang masih kosong</p>
                        </div>
                    @endif
                </div>

                <!-- Cart Summary -->
                @if(count($cart) > 0)
                    <div class="border-t pt-4 mt-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <x-input label="Pajak (%)" wire:model="taxRate" type="number"
                                step="0.01" min="0" max="100" class="input-sm" readonly />
                            <x-input label="Diskon (Rp)" wire:model.live="discountAmount"
                                prefix="Rp" locale="id-ID" money class="input-sm" />
                        </div>

                        <div class="bg-primary/10 p-3 rounded-lg">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span class="{{ $total < 0 ? 'text-error' : 'text-primary' }}">Rp {{ number_format($total, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <x-button label="Bayar" icon="phosphor.currency-circle-dollar"
                            class="btn-success w-full" wire:click="showPaymentModal" />
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Payment Modal -->
    <x-modal wire:model="paymentModal" class="modal-bottom sm:modal-middle backdrop-blur"
        title="Proses Pembayaran" persistent>
        <div class="space-y-4">
            <!-- Payment Summary -->
            <div class="bg-base-200 p-4 rounded-lg">
                <h3 class="font-semibold mb-2">Ringkasan Pembayaran</h3>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format(collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']), 2, ',', '.') }}</span>
                    </div>
                    @if($taxRate > 0)
                        <div class="flex justify-between">
                            <span>Pajak ({{ $taxRate }}%):</span>
                            <span>Rp {{ number_format(collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']) * ($taxRate / 100), 2, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($discountAmount > 0)
                        <div class="flex justify-between text-success">
                            <span>Diskon:</span>
                            <span>-Rp {{ number_format($discountAmount, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t pt-1 mt-2">
                        <div class="flex justify-between font-bold">
                            <span>Total:</span>
                            <span class="{{ $total < 0 ? 'text-error' : 'text-primary' }}">Rp {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="{{ $paymentMethod !== 'tunai' ? 'md:col-span-2' : '' }}">
                    <x-select label="Metode Pembayaran" wire:model.live="paymentMethod"
                        :options="$this->getPaymentMethodOptions()" icon="phosphor.credit-card"
                        option-value="id" option-label="name" />
                </div>
                @if($paymentMethod === 'tunai')
                    <x-input label="Jumlah Bayar" wire:model.live="paidAmount"
                        prefix="Rp" locale="id-ID" money required />
                @endif
            </div>

            @if($paymentMethod === 'tunai' && $paidAmount > 0)
                <div class="bg-info/10 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Kembalian:</span>
                        <span class="text-lg font-bold text-info">
                            Rp {{ number_format($changeAmount, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endif

            <x-textarea label="Catatan (Opsional)" wire:model="notes"
                placeholder="Catatan untuk transaksi ini..." rows="2" />
        </div>

        <x-slot:actions>
            <x-button label="Batal" icon="phosphor.x" class="btn-soft"
                @click="$wire.paymentModal = false" />
            <x-button label="Proses Pembayaran" icon="phosphor.check"
                class="btn-success" wire:click="processPayment"
                :disabled="$paymentMethod === 'tunai' ? $paidAmount < $total : false" />
        </x-slot:actions>
    </x-modal>
</div>
