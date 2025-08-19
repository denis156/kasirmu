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
                        <div class="space-y-6">
                            <!-- Informasi Bisnis -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center text-primary">
                                    <x-icon name="phosphor.storefront" class="w-5 h-5 mr-2" />
                                    Informasi Bisnis
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                </div>
                            </div>

                            <!-- Informasi Kontak -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center text-secondary">
                                    <x-icon name="phosphor.address-book" class="w-5 h-5 mr-2" />
                                    Informasi Kontak
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                </div>
                            </div>

                            <!-- Konfigurasi Pajak -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4 flex items-center text-accent">
                                    <x-icon name="phosphor.percent" class="w-5 h-5 mr-2" />
                                    Konfigurasi Pajak
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            </div>
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
                        <!-- Payment Gateway Toggle -->
                        <div class="bg-primary/5 border border-primary/20 rounded-xl p-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-primary mb-2">Status Payment Gateway</h3>
                                    <p class="text-sm text-base-content/70">Aktifkan untuk menerima pembayaran digital dari pelanggan</p>
                                </div>
                                <label class="cursor-pointer">
                                    <input type="checkbox" class="toggle toggle-primary toggle-lg"
                                        wire:model="paymentSettings.payment_gateway_enabled.value" />
                                </label>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Midtrans Settings -->
                            <div class="bg-info/5 border border-info/20 p-6 rounded-xl">
                                <h3 class="text-lg font-semibold mb-2 flex items-center text-info">
                                    <x-icon name="phosphor.credit-card" class="w-5 h-5 mr-2" />
                                    Midtrans Payment Gateway
                                </h3>
                                <p class="text-sm text-base-content/70 mb-4">Gateway pembayaran terpercaya untuk kartu kredit, e-wallet, dan bank transfer</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input
                                        label="Server Key"
                                        wire:model="paymentSettings.midtrans_server_key.value"
                                        type="password"
                                        placeholder="SB-Mid-server-xxx..."
                                        hint="Key rahasia untuk server authentication"
                                    />
                                    <x-input
                                        label="Client Key"
                                        wire:model="paymentSettings.midtrans_client_key.value"
                                        placeholder="SB-Mid-client-xxx..."
                                        hint="Key publik untuk frontend integration"
                                    />
                                </div>

                                <div class="bg-base-200/50 border border-base-content/10 rounded-lg p-4 mt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-base-content">Mode Production</span>
                                            <p class="text-xs text-base-content/60">Aktifkan untuk transaksi real dengan uang asli</p>
                                        </div>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" class="toggle toggle-warning"
                                                wire:model="paymentSettings.midtrans_is_production.value" />
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Xendit Settings -->
                            <div class="bg-success/5 border border-success/20 p-6 rounded-xl">
                                <h3 class="text-lg font-semibold mb-2 flex items-center text-success">
                                    <x-icon name="phosphor.wallet" class="w-5 h-5 mr-2" />
                                    Xendit Payment Gateway
                                </h3>
                                <p class="text-sm text-base-content/70 mb-4">Platform pembayaran untuk e-wallet, virtual account, dan transfer bank</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input
                                        label="Secret Key"
                                        wire:model="paymentSettings.xendit_secret_key.value"
                                        type="password"
                                        placeholder="xnd_development_xxx..."
                                        hint="Key rahasia untuk API authentication"
                                    />
                                    <x-input
                                        label="Public Key"
                                        wire:model="paymentSettings.xendit_public_key.value"
                                        placeholder="xnd_public_development_xxx..."
                                        hint="Key publik untuk client-side integration"
                                    />
                                </div>

                                <x-password
                                    label="Callback Token"
                                    wire:model="paymentSettings.xendit_callback_token.value"
                                    placeholder="Token untuk validasi webhook"
                                    hint="Token untuk memverifikasi callback dari Xendit"
                                />
                            </div>
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
