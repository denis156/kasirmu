<div>
    <!-- HEADER -->
    <x-header title="Pengaturan Aplikasi" subtitle="Kelola informasi bisnis dan konfigurasi sistem pembayaran" icon="phosphor.gear-six"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Muat Ulang" icon="phosphor.arrow-clockwise" class="btn-sm btn-primary btn-outline"
                wire:click="loadSettings" />
        </x-slot:actions>
    </x-header>

    <!-- TABS CONTENT -->
    <div class="mt-6">
        <x-tabs wire:model="selectedTab" active-class="bg-primary text-primary-content" label-class="font-semibold">
            <x-tab name="business-tab" label="Bisnis & Konfigurasi" icon="phosphor.buildings">
                <x-card title="Informasi Bisnis & Konfigurasi" subtitle="Kelola identitas bisnis, informasi pemilik, dan pengaturan dasar aplikasi" shadow
                    class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
                    <x-form wire:submit="saveBusinessSettings">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informasi Bisnis -->
                            @foreach ($businessSettings as $key => $setting)
                                @if ($setting['type'] === 'text' && in_array($key, ['business_name', 'business_tagline', 'business_description', 'business_owner']))
                                    <div class="{{ in_array($key, ['business_description']) ? 'md:col-span-2' : '' }}">
                                        @if ($key === 'business_description')
                                            <x-textarea
                                                label="{{ $setting['label'] }}"
                                                wire:model="businessSettings.{{ $key }}.value"
                                                hint="{{ $setting['description'] }}"
                                                placeholder="Deskripsikan bisnis Anda secara singkat dan menarik"
                                                rows="3"
                                            />
                                        @else
                                            <x-input
                                                label="{{ $setting['label'] }}"
                                                wire:model="businessSettings.{{ $key }}.value"
                                                hint="{{ $setting['description'] }}"
                                                placeholder="{{ $key === 'business_owner' ? 'Nama pemilik bisnis' : 'Masukkan ' . strtolower($setting['label']) }}"
                                            />
                                        @endif
                                    </div>
                                @endif
                            @endforeach

                            <!-- Informasi Kontak -->
                            @foreach ($businessSettings as $key => $setting)
                                @if ($setting['type'] === 'text' && in_array($key, ['contact_email', 'contact_phone']))
                                    <x-input
                                        label="{{ $setting['label'] }}"
                                        wire:model="businessSettings.{{ $key }}.value"
                                        hint="{{ $setting['description'] }}"
                                        placeholder="{{ $key === 'contact_email' ? 'contoh@bisnis.com' : '+62 xxx-xxxx-xxxx' }}"
                                        type="{{ $key === 'contact_email' ? 'email' : 'tel' }}"
                                    />
                                @endif
                            @endforeach

                            <!-- Konfigurasi Pajak -->
                            @foreach ($businessSettings as $key => $setting)
                                @if ($setting['type'] === 'number' && $key === 'tax_rate')
                                    <x-input
                                        label="{{ $setting['label'] }}"
                                        wire:model="businessSettings.{{ $key }}.value"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        hint="{{ $setting['description'] }}"
                                        placeholder="11.0"
                                        suffix="%"
                                    />
                                @endif
                            @endforeach
                        </div>

                        <x-slot:actions>
                            <x-button label="Simpan Semua Pengaturan" type="submit" icon="phosphor.floppy-disk"
                                class="btn-primary btn-lg" spinner="saveBusinessSettings" />
                        </x-slot:actions>
                    </x-form>
                </x-card>
            </x-tab>

            <x-tab name="payment-tab" label="Sistem Pembayaran" icon="phosphor.credit-card">
                <x-card title="Konfigurasi Sistem Pembayaran" subtitle="Kelola gateway pembayaran online untuk transaksi digital dan e-commerce" shadow
                    class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
                    <x-form wire:submit="savePaymentSettings">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Gateway Toggle -->
                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-base-content">Status Payment Gateway</label>
                                        <p class="text-xs text-base-content/70 mt-1">Aktifkan untuk menerima pembayaran digital dari pelanggan</p>
                                    </div>
                                    <x-toggle class="toggle-primary toggle-md" wire:model="paymentSettings.payment_gateway_enabled.value" />
                                </div>
                            </div>

                            <!-- Midtrans Settings -->
                            <x-input
                                label="Midtrans Merchant ID"
                                wire:model="paymentSettings.midtrans_merchant_id.value"
                                placeholder="G123456789"
                                hint="Merchant ID dari dashboard Midtrans"
                            />
                            <x-password
                                label="Midtrans Client Key"
                                wire:model="paymentSettings.midtrans_client_key.value"
                                placeholder="SB-Mid-client-xxx..."
                                hint="Key publik untuk frontend integration"
                                right
                            />
                            <x-password
                                label="Midtrans Server Key"
                                wire:model="paymentSettings.midtrans_server_key.value"
                                placeholder="SB-Mid-server-xxx..."
                                hint="Key rahasia untuk server authentication"
                                right
                            />
                            <x-select
                                label="Mode Midtrans"
                                wire:model="paymentSettings.midtrans_is_production.value"
                                :options="$this->getMidtransModeOptions()"
                                option-value="id"
                                option-label="name"
                                hint="Pilih mode operasi Midtrans"
                                icon="phosphor.gear-six"
                            />

                            <!-- TODO: Xendit Settings (commented out) -->
                            {{--
                            <x-password
                                label="Xendit Secret Key"
                                wire:model="paymentSettings.xendit_secret_key.value"
                                placeholder="xnd_development_xxx..."
                                hint="Key rahasia untuk API authentication"
                                right
                            />
                            <x-password
                                label="Xendit Public Key"
                                wire:model="paymentSettings.xendit_public_key.value"
                                placeholder="xnd_public_development_xxx..."
                                hint="Key publik untuk client-side integration"
                                right
                            />
                            <x-password
                                label="Xendit Callback Token"
                                wire:model="paymentSettings.xendit_callback_token.value"
                                placeholder="Token untuk validasi webhook"
                                hint="Token untuk memverifikasi callback dari Xendit"
                                right
                            />
                            --}}
                        </div>

                        <x-slot:actions>
                            <x-button label="Simpan Konfigurasi Pembayaran" type="submit" icon="phosphor.floppy-disk"
                                class="btn-primary btn-lg" spinner="savePaymentSettings" />
                        </x-slot:actions>
                    </x-form>
                </x-card>
            </x-tab>
        </x-tabs>
    </div>
</div>
