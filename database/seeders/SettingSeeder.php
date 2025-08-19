<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Business Information (Public)
            [
                'key' => 'business_name',
                'label' => 'Nama Bisnis',
                'value' => 'KasirMu Store',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nama bisnis yang akan ditampilkan di landing page dan aplikasi',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'business_tagline',
                'label' => 'Tagline Bisnis',
                'value' => 'Solusi POS Modern untuk Bisnis Masa Depan',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Tagline atau slogan bisnis',
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'business_description',
                'label' => 'Deskripsi Bisnis',
                'value' => 'Sistem Point of Sale terdepan yang membantu ribuan bisnis Indonesia berkembang dengan teknologi modern dan interface yang intuitif.',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Deskripsi lengkap tentang bisnis',
                'is_public' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'contact_email',
                'label' => 'Email Kontak',
                'value' => 'info@kasirmu.com',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Email untuk kontak bisnis',
                'is_public' => true,
                'sort_order' => 4
            ],
            [
                'key' => 'contact_phone',
                'label' => 'Nomor Telepon',
                'value' => '+62 812-3456-7890',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nomor telepon untuk kontak',
                'is_public' => true,
                'sort_order' => 5
            ],
            [
                'key' => 'business_owner',
                'label' => 'Nama Pemilik',
                'value' => 'ArteliaDev',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nama pemilik bisnis',
                'is_public' => true,
                'sort_order' => 6
            ],

            // Statistics (Public)
            [
                'key' => 'total_active_products',
                'label' => 'Total Produk Aktif',
                'value' => '0',
                'type' => 'number',
                'group' => 'statistics',
                'description' => 'Jumlah produk aktif di sistem',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'total_verified_users',
                'label' => 'Total Pengguna Terverifikasi',
                'value' => '0',
                'type' => 'number',
                'group' => 'statistics',
                'description' => 'Jumlah pengguna yang telah terverifikasi',
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'system_uptime',
                'label' => 'System Uptime',
                'value' => '99.9',
                'type' => 'number',
                'group' => 'statistics',
                'description' => 'Persentase uptime sistem',
                'is_public' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'total_transactions',
                'label' => 'Total Transaksi',
                'value' => '0',
                'type' => 'number',
                'group' => 'statistics',
                'description' => 'Total transaksi yang telah diproses',
                'is_public' => true,
                'sort_order' => 4
            ],

            // Features (Public)
            [
                'key' => 'featured_capabilities',
                'label' => 'Kemampuan Unggulan',
                'value' => json_encode([
                    'Modern POS Interface',
                    'Real-time Analytics',
                    'Multi-platform Support',
                    'Advanced Inventory',
                    'Customer Management',
                    'Reporting Dashboard'
                ]),
                'type' => 'json',
                'group' => 'features',
                'description' => 'Daftar kemampuan unggulan sistem',
                'is_public' => true,
                'sort_order' => 1
            ],

            // Social Media (Public)
            [
                'key' => 'social_github',
                'label' => 'GitHub URL',
                'value' => 'https://github.com/denis156',
                'type' => 'url',
                'group' => 'social',
                'description' => 'URL GitHub',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'social_instagram',
                'label' => 'Instagram URL',
                'value' => 'https://www.instagram.com/artelia_development',
                'type' => 'url',
                'group' => 'social',
                'description' => 'URL Instagram',
                'is_public' => true,
                'sort_order' => 2
            ],
            
            // Payment Gateway Settings (Private)
            [
                'key' => 'payment_gateway_enabled',
                'label' => 'Aktifkan Payment Gateway',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Aktifkan fitur payment gateway',
                'is_public' => false,
                'sort_order' => 1
            ],
            [
                'key' => 'midtrans_server_key',
                'label' => 'Midtrans Server Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Server Key untuk Midtrans Payment Gateway',
                'is_public' => false,
                'sort_order' => 2
            ],
            [
                'key' => 'midtrans_client_key',
                'label' => 'Midtrans Client Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Client Key untuk Midtrans Payment Gateway',
                'is_public' => false,
                'sort_order' => 3
            ],
            [
                'key' => 'midtrans_is_production',
                'label' => 'Midtrans Production Mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Aktifkan mode production untuk Midtrans',
                'is_public' => false,
                'sort_order' => 4
            ],
            [
                'key' => 'xendit_secret_key',
                'label' => 'Xendit Secret Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Secret Key untuk Xendit Payment Gateway',
                'is_public' => false,
                'sort_order' => 5
            ],
            [
                'key' => 'xendit_public_key',
                'label' => 'Xendit Public Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Public Key untuk Xendit Payment Gateway',
                'is_public' => false,
                'sort_order' => 6
            ],
            [
                'key' => 'xendit_callback_token',
                'label' => 'Xendit Callback Token',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Callback Token untuk validasi webhook Xendit',
                'is_public' => false,
                'sort_order' => 7
            ],
            
            // System Settings (Private)
            [
                'key' => 'maintenance_mode',
                'label' => 'Mode Maintenance',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Aktifkan mode maintenance',
                'is_public' => false,
                'sort_order' => 1
            ],
            [
                'key' => 'default_currency',
                'label' => 'Mata Uang Default',
                'value' => 'IDR',
                'type' => 'text',
                'group' => 'system',
                'description' => 'Mata uang default untuk transaksi',
                'is_public' => false,
                'sort_order' => 2
            ],
            [
                'key' => 'tax_rate',
                'label' => 'Tarif Pajak Default',
                'value' => '11',
                'type' => 'number',
                'group' => 'system',
                'description' => 'Tarif pajak default dalam persen (PPN 11%)',
                'is_public' => false,
                'sort_order' => 3
            ],
            [
                'key' => 'auto_backup_enabled',
                'label' => 'Auto Backup Database',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Aktifkan backup database otomatis',
                'is_public' => false,
                'sort_order' => 4
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
