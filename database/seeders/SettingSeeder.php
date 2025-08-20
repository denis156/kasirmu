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
            // Business Information - Tab "Bisnis & Konfigurasi"
            [
                'key' => 'business_name',
                'label' => 'Nama Bisnis',
                'value' => 'KasirMu By Artelia',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nama bisnis/toko Anda',
                'is_public' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'business_tagline',
                'label' => 'Tagline Bisnis',
                'value' => 'Solusi Kasir Digital Terpercaya',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Tagline atau slogan bisnis',
                'is_public' => true,
                'sort_order' => 2
            ],
            [
                'key' => 'business_description',
                'label' => 'Deskripsi Bisnis',
                'value' => 'Sistem Point of Sale (POS) modern yang memudahkan pengelolaan transaksi, inventory, dan laporan penjualan untuk bisnis Anda.',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Deskripsi lengkap tentang bisnis',
                'is_public' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'business_owner',
                'label' => 'Pemilik Bisnis',
                'value' => 'Artelia',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nama pemilik bisnis',
                'is_public' => false,
                'sort_order' => 4
            ],
            [
                'key' => 'contact_email',
                'label' => 'Email Kontak',
                'value' => 'info@kasirmu.artelia.id',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Email untuk komunikasi bisnis',
                'is_public' => true,
                'sort_order' => 5
            ],
            [
                'key' => 'contact_phone',
                'label' => 'Telepon Kontak',
                'value' => '08123456789',
                'type' => 'text',
                'group' => 'business',
                'description' => 'Nomor telepon untuk komunikasi bisnis',
                'is_public' => true,
                'sort_order' => 6
            ],
            [
                'key' => 'tax_rate',
                'label' => 'Tarif Pajak (%)',
                'value' => '11',
                'type' => 'number',
                'group' => 'business',
                'description' => 'Tarif pajak dalam persen (contoh: 11 untuk PPN 11%)',
                'is_public' => false,
                'sort_order' => 7
            ],
            
            // Payment Gateway Settings - Tab "Sistem Pembayaran"
            [
                'key' => 'payment_gateway_enabled',
                'label' => 'Aktifkan Payment Gateway',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Aktifkan untuk menerima pembayaran digital dari pelanggan',
                'is_public' => false,
                'sort_order' => 1
            ],
            [
                'key' => 'midtrans_is_production',
                'label' => 'Mode Production Midtrans',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'OFF = Sandbox (Testing), ON = Production (Live)',
                'is_public' => false,
                'sort_order' => 2
            ],
            [
                'key' => 'midtrans_merchant_id',
                'label' => 'Midtrans Merchant ID',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Merchant ID dari dashboard Midtrans',
                'is_public' => false,
                'sort_order' => 3
            ],
            [
                'key' => 'midtrans_client_key',
                'label' => 'Midtrans Client Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Key publik untuk frontend integration',
                'is_public' => false,
                'sort_order' => 4
            ],
            [
                'key' => 'midtrans_server_key',
                'label' => 'Midtrans Server Key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment',
                'description' => 'Key rahasia untuk server authentication',
                'is_public' => false,
                'sort_order' => 5
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
