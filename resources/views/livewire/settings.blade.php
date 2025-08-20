<div>
    <!-- HEADER -->
    <x-header title="Pengaturan Aplikasi" subtitle="Kelola informasi bisnis dan konfigurasi sistem pembayaran"
        icon="phosphor.gear-six" icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator>
        <x-slot:actions>
            <x-button label="Test Koneksi" icon="phosphor.wifi-high" class="btn-sm btn-success btn-outline"
                wire:click="testMidtransConnection" spinner="testMidtransConnection" />
            <x-button label="Muat Ulang" icon="phosphor.arrow-clockwise" class="btn-sm btn-primary btn-outline"
                wire:click="loadSettings" />
        </x-slot:actions>
    </x-header>

    <!-- TABS CONTENT -->
    <div class="mt-6">
        <x-tabs wire:model="selectedTab" active-class="bg-primary text-primary-content" label-class="font-semibold">
            <x-tab name="business-tab" label="Bisnis & Konfigurasi" icon="phosphor.buildings">
                <x-card title="Informasi Bisnis & Konfigurasi"
                    subtitle="Kelola identitas bisnis, informasi pemilik, dan pengaturan dasar aplikasi" shadow
                    class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
                    <x-form wire:submit="saveBusinessSettings">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informasi Bisnis -->
                            @foreach ($businessSettings as $key => $setting)
                                @if (
                                    $setting['type'] === 'text' &&
                                        in_array($key, ['business_name', 'business_tagline', 'business_description', 'business_owner']))
                                    <div class="{{ in_array($key, ['business_description']) ? 'md:col-span-2' : '' }}">
                                        @if ($key === 'business_description')
                                            <x-textarea label="{{ $setting['label'] }}"
                                                wire:model="businessSettings.{{ $key }}.value"
                                                hint="{{ $setting['description'] }}"
                                                placeholder="Deskripsikan bisnis Anda secara singkat dan menarik"
                                                rows="3" />
                                        @else
                                            <x-input label="{{ $setting['label'] }}"
                                                wire:model="businessSettings.{{ $key }}.value"
                                                hint="{{ $setting['description'] }}"
                                                placeholder="{{ $key === 'business_owner' ? 'Nama pemilik bisnis' : 'Masukkan ' . strtolower($setting['label']) }}" />
                                        @endif
                                    </div>
                                @endif
                            @endforeach

                            <!-- Informasi Kontak -->
                            @foreach ($businessSettings as $key => $setting)
                                @if ($setting['type'] === 'text' && in_array($key, ['contact_email', 'contact_phone']))
                                    <x-input label="{{ $setting['label'] }}"
                                        wire:model="businessSettings.{{ $key }}.value"
                                        hint="{{ $setting['description'] }}"
                                        placeholder="{{ $key === 'contact_email' ? 'contoh@bisnis.com' : '+62 xxx-xxxx-xxxx' }}"
                                        type="{{ $key === 'contact_email' ? 'email' : 'tel' }}" />
                                @endif
                            @endforeach

                            <!-- Konfigurasi Pajak -->
                            @foreach ($businessSettings as $key => $setting)
                                @if ($setting['type'] === 'number' && $key === 'tax_rate')
                                    <x-input label="{{ $setting['label'] }}"
                                        wire:model="businessSettings.{{ $key }}.value" type="number"
                                        step="0.1" min="0" max="100"
                                        hint="{{ $setting['description'] }}" placeholder="11.0" suffix="%" />
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
                <x-card title="Konfigurasi Sistem Pembayaran"
                    subtitle="Kelola gateway pembayaran online untuk transaksi digital dan e-commerce" shadow
                    class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary mt-4">
                    <x-form wire:submit="savePaymentSettings">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Payment Gateway Toggles - 2 columns on md -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-toggle label="Aktifkan Payment Gateway"
                                    wire:model.live="paymentSettings.payment_gateway_enabled.value"
                                    hint="Aktifkan untuk menerima pembayaran digital dari pelanggan"
                                    class="toggle-lg toggle-success" />

                                <x-toggle label="Mode Production Midtrans"
                                    wire:model.live="paymentSettings.midtrans_is_production.value"
                                    hint="OFF = Sandbox (Testing), ON = Production (Live)"
                                    class="toggle-lg toggle-warning" />
                            </div>

                            <!-- Midtrans Settings - Full width -->
                            <x-input label="Midtrans Merchant ID"
                                wire:model="paymentSettings.midtrans_merchant_id.value" placeholder="G123456789"
                                hint="Merchant ID dari dashboard Midtrans" />
                            <x-password label="Midtrans Client Key"
                                wire:model="paymentSettings.midtrans_client_key.value"
                                placeholder="SB-Mid-client-xxx..." hint="Key publik untuk frontend integration" right />
                            <x-password label="Midtrans Server Key"
                                wire:model="paymentSettings.midtrans_server_key.value"
                                placeholder="SB-Mid-server-xxx..." hint="Key rahasia untuk server authentication"
                                right />


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

                        <!-- Webhook Information -->
                        @php
                            $paymentEnabled =
                                isset($paymentSettings['payment_gateway_enabled']['value']) &&
                                ($paymentSettings['payment_gateway_enabled']['value'] === '1' ||
                                    $paymentSettings['payment_gateway_enabled']['value'] === 1 ||
                                    $paymentSettings['payment_gateway_enabled']['value'] === true);
                        @endphp
                        @if ($paymentEnabled)
                            <div class="mt-6 p-4 bg-base-200 rounded-lg border border-base-content/10">
                                <h4 class="font-semibold text-base-content mb-2 flex items-center">
                                    <x-icon name="phosphor.webhooks-logo" class="w-5 h-5 mr-2" />
                                    Webhook Configuration
                                </h4>
                                <p class="text-sm text-base-content/70 mb-3">
                                    Copy URL berikut ke Payment Notification URL di dashboard Midtrans untuk
                                    sinkronisasi otomatis status pembayaran:
                                </p>
                                <x-input value="{{ url('/webhook/midtrans') }}" readonly
                                    class="font-mono text-sm" icon="phosphor.webhooks-logo"
                                    label="Webhook URL">
                                    <x-slot:append>
                                        <x-button icon="phosphor.copy" class="join-item btn-primary"
                                            label="Salin URL"
                                            onclick="navigator.clipboard.writeText('{{ url('/webhook/midtrans') }}');
                                            window.dispatchEvent(new CustomEvent('success', {detail: 'URL webhook berhasil disalin!'}));"
                                            tooltip="Copy webhook URL" />
                                    </x-slot:append>
                                </x-input>
                            </div>
                        @endif

                        <x-slot:actions>
                            <x-button label="Simpan Konfigurasi Pembayaran" type="submit"
                                icon="phosphor.floppy-disk" class="btn-primary btn-lg"
                                spinner="savePaymentSettings" />
                        </x-slot:actions>
                    </x-form>
                </x-card>
            </x-tab>
        </x-tabs>
    </div>
</div>
