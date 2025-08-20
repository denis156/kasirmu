<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Pengaturan Sistem')]
class Settings extends Component
{
    use Toast;

    // Form data for each group
    public array $businessSettings = [];
    public array $paymentSettings = [];

    // Active tab
    public string $selectedTab = 'business-tab';

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $allSettings = Setting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');

        // Load each group dengan fallback ke default values
        $this->businessSettings = $this->formatSettingsForGroup($allSettings->get('business', collect()), $this->getDefaultBusinessSettings());
        $this->paymentSettings = $this->formatSettingsForGroup($allSettings->get('payment', collect()), $this->getDefaultPaymentSettings());
        
        // Merge system settings yang diperlukan ke business
        $systemSettings = $allSettings->get('system', collect());
        foreach ($systemSettings as $setting) {
            if ($setting->key === 'tax_rate') {
                $this->businessSettings[$setting->key] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'label' => $setting->label,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'is_public' => $setting->is_public
                ];
            }
        }
        
        // Jika tidak ada tax_rate dari database, gunakan default
        if (!isset($this->businessSettings['tax_rate'])) {
            $this->businessSettings['tax_rate'] = [
                'id' => null,
                'key' => 'tax_rate',
                'label' => 'Tarif Pajak Default',
                'value' => '11',
                'type' => 'number',
                'description' => 'Tarif pajak default dalam persen (PPN 11%)',
                'is_public' => false
            ];
        }
    }

    private function formatSettingsForGroup($settings, $defaults = [])
    {
        $formatted = [];
        
        // Mulai dengan default values
        if (!empty($defaults)) {
            $formatted = $defaults;
        }
        
        // Override dengan data dari database jika ada
        foreach ($settings as $setting) {
            $formatted[$setting->key] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'label' => $setting->label,
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_public' => $setting->is_public
            ];
        }
        
        return $formatted;
    }

    private function getDefaultBusinessSettings()
    {
        return [
            'business_name' => [
                'id' => null,
                'key' => 'business_name',
                'label' => 'Nama Bisnis',
                'value' => '',
                'type' => 'text',
                'description' => 'Nama bisnis yang akan ditampilkan di landing page dan aplikasi',
                'is_public' => true
            ],
            'business_tagline' => [
                'id' => null,
                'key' => 'business_tagline',
                'label' => 'Tagline Bisnis',
                'value' => '',
                'type' => 'text',
                'description' => 'Tagline atau slogan bisnis',
                'is_public' => true
            ],
            'business_description' => [
                'id' => null,
                'key' => 'business_description',
                'label' => 'Deskripsi Bisnis',
                'value' => '',
                'type' => 'text',
                'description' => 'Deskripsi lengkap tentang bisnis',
                'is_public' => true
            ],
            'business_owner' => [
                'id' => null,
                'key' => 'business_owner',
                'label' => 'Nama Pemilik',
                'value' => '',
                'type' => 'text',
                'description' => 'Nama pemilik bisnis',
                'is_public' => true
            ],
            'contact_email' => [
                'id' => null,
                'key' => 'contact_email',
                'label' => 'Email Kontak',
                'value' => '',
                'type' => 'text',
                'description' => 'Email untuk kontak bisnis',
                'is_public' => true
            ],
            'contact_phone' => [
                'id' => null,
                'key' => 'contact_phone',
                'label' => 'Nomor Telepon',
                'value' => '',
                'type' => 'text',
                'description' => 'Nomor telepon untuk kontak',
                'is_public' => true
            ]
        ];
    }

    private function getDefaultPaymentSettings()
    {
        return [
            'payment_gateway_enabled' => [
                'id' => null,
                'key' => 'payment_gateway_enabled',
                'label' => 'Aktifkan Payment Gateway',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Aktifkan fitur payment gateway',
                'is_public' => false
            ],
            'midtrans_merchant_id' => [
                'id' => null,
                'key' => 'midtrans_merchant_id',
                'label' => 'Midtrans Merchant ID',
                'value' => '',
                'type' => 'text',
                'description' => 'Merchant ID untuk Midtrans Payment Gateway',
                'is_public' => false
            ],
            'midtrans_server_key' => [
                'id' => null,
                'key' => 'midtrans_server_key',
                'label' => 'Midtrans Server Key',
                'value' => '',
                'type' => 'text',
                'description' => 'Server Key untuk Midtrans Payment Gateway',
                'is_public' => false
            ],
            'midtrans_client_key' => [
                'id' => null,
                'key' => 'midtrans_client_key',
                'label' => 'Midtrans Client Key',
                'value' => '',
                'type' => 'text',
                'description' => 'Client Key untuk Midtrans Payment Gateway',
                'is_public' => false
            ],
            'midtrans_is_production' => [
                'id' => null,
                'key' => 'midtrans_is_production',
                'label' => 'Midtrans Production Mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Aktifkan mode production untuk Midtrans',
                'is_public' => false
            ],
            // TODO: Xendit Payment Gateway Settings - untuk implementasi masa depan
            /*
            'xendit_secret_key' => [
                'id' => null,
                'key' => 'xendit_secret_key',
                'label' => 'Xendit Secret Key',
                'value' => '',
                'type' => 'text',
                'description' => 'Secret Key untuk Xendit Payment Gateway',
                'is_public' => false
            ],
            'xendit_public_key' => [
                'id' => null,
                'key' => 'xendit_public_key',
                'label' => 'Xendit Public Key',
                'value' => '',
                'type' => 'text',
                'description' => 'Public Key untuk Xendit Payment Gateway',
                'is_public' => false
            ],
            'xendit_callback_token' => [
                'id' => null,
                'key' => 'xendit_callback_token',
                'label' => 'Xendit Callback Token',
                'value' => '',
                'type' => 'text',
                'description' => 'Callback Token untuk validasi webhook Xendit',
                'is_public' => false
            ]
            */
        ];
    }

    public function saveBusinessSettings()
    {
        $this->saveSettingsGroup($this->businessSettings, 'Pengaturan Bisnis');
    }

    public function savePaymentSettings()
    {
        $this->saveSettingsGroup($this->paymentSettings, 'Pengaturan Payment Gateway');
    }

    private function saveSettingsGroup($settings, $groupName)
    {
        try {
            foreach ($settings as $key => $settingData) {
                // Jika ID tidak ada atau null (data belum ada di database), buat record baru
                if (!isset($settingData['id']) || $settingData['id'] === null) {
                    Setting::create([
                        'key' => $settingData['key'],
                        'label' => $settingData['label'],
                        'value' => $settingData['value'],
                        'type' => $settingData['type'],
                        'group' => $this->getGroupFromKey($settingData['key']),
                        'description' => $settingData['description'],
                        'is_public' => $settingData['is_public'],
                        'sort_order' => $this->getSortOrderFromKey($settingData['key'])
                    ]);
                } else {
                    // Jika sudah ada, update value saja menggunakan model untuk trigger event
                    $setting = Setting::where('key', $key)->first();
                    if ($setting) {
                        $setting->update([
                            'value' => $settingData['value']
                        ]);
                    }
                }
            }

            $this->success("{$groupName} berhasil disimpan!", position: 'toast-top toast-end');

            // Reload settings to ensure UI is synced
            $this->loadSettings();
        } catch (\Exception $e) {
            $this->error("Gagal menyimpan {$groupName}. " . $e->getMessage(), position: 'toast-top toast-end');
        }
    }

    private function getGroupFromKey($key)
    {
        // Tentukan group berdasarkan key
        $businessKeys = ['business_name', 'business_tagline', 'business_description', 'business_owner', 'contact_email', 'contact_phone'];
        $paymentKeys = ['payment_gateway_enabled', 'midtrans_merchant_id', 'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production'];
        // TODO: Tambahkan Xendit keys ketika diimplementasikan: 'xendit_secret_key', 'xendit_public_key', 'xendit_callback_token'
        $systemKeys = ['tax_rate'];
        
        if (in_array($key, $businessKeys)) {
            return 'business';
        } elseif (in_array($key, $paymentKeys)) {
            return 'payment';
        } elseif (in_array($key, $systemKeys)) {
            return 'system';
        }
        
        return 'other';
    }

    private function getSortOrderFromKey($key)
    {
        // Tentukan sort order berdasarkan key
        $sortOrders = [
            'business_name' => 1,
            'business_tagline' => 2, 
            'business_description' => 3,
            'business_owner' => 6,
            'contact_email' => 4,
            'contact_phone' => 5,
            'tax_rate' => 3,
            'payment_gateway_enabled' => 1,
            'midtrans_merchant_id' => 2,
            'midtrans_server_key' => 3,
            'midtrans_client_key' => 4,
            'midtrans_is_production' => 5
            // TODO: Tambahkan Xendit sort order ketika diimplementasikan:
            // 'xendit_secret_key' => 6,
            // 'xendit_public_key' => 7,
            // 'xendit_callback_token' => 8
        ];
        
        return $sortOrders[$key] ?? 99;
    }

    // Method ini tidak diperlukan lagi karena x-tabs handle otomatis

    // Helper method to get tab data (tidak digunakan lagi, tapi tetap ada untuk kompatibilitas)
    public function getTabData()
    {
        return [
            'business' => [
                'title' => 'Bisnis & Konfigurasi',
                'icon' => 'phosphor.buildings',
                'description' => 'Informasi bisnis, pemilik, dan pengaturan dasar'
            ],
            'payment' => [
                'title' => 'Payment Gateway',
                'icon' => 'phosphor.credit-card',
                'description' => 'Konfigurasi sistem pembayaran online'
            ]
        ];
    }

    // Tidak diperlukan lagi karena menggunakan toggle
    // public function getMidtransModeOptions(): array
    // public function getPaymentGatewayOptions(): array

    public function updatedPaymentSettings($value, $key)
    {
        // Trigger saat ada perubahan di paymentSettings
        if ($key === 'midtrans_is_production.value') {
            $this->dispatch('midtransModeChanged', $value);
        }
        
        if ($key === 'payment_gateway_enabled.value') {
            $this->dispatch('paymentGatewayToggled', $value);
        }
    }


    public function testMidtransConnection()
    {
        try {
            $midtrans = app('midtrans');
            $serverKey = $midtrans->getServerKey();
            $clientKey = $midtrans->getClientKey();
            $isProduction = $midtrans->isProduction();

            if (empty($serverKey) || empty($clientKey)) {
                $this->error('Server Key atau Client Key belum diisi!');
                return;
            }

            $mode = $isProduction ? 'Production' : 'Sandbox';
            $this->success("Koneksi Midtrans berhasil! Mode: {$mode}");

        } catch (\Exception $e) {
            $this->error('Gagal terhubung ke Midtrans: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
