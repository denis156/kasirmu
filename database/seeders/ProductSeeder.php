<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        
        $products = [
            // Makanan
            [
                'name' => 'Nasi Goreng',
                'description' => 'Nasi goreng spesial dengan telur dan ayam',
                'price' => 25000.00,
                'stock' => 50,
                'min_stock' => 10,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'makanan')->first()->id,
                'barcode' => '1234567890123',
                'is_active' => true,
            ],
            [
                'name' => 'Mie Ayam',
                'description' => 'Mie ayam dengan topping lengkap',
                'price' => 20000.00,
                'stock' => 30,
                'min_stock' => 5,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'makanan')->first()->id,
                'barcode' => '1234567890124',
                'is_active' => true,
            ],
            [
                'name' => 'Gado-gado',
                'description' => 'Gado-gado sayuran segar dengan bumbu kacang',
                'price' => 18000.00,
                'stock' => 25,
                'min_stock' => 5,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'makanan')->first()->id,
                'barcode' => '1234567890125',
                'is_active' => true,
            ],
            
            // Minuman
            [
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'price' => 5000.00,
                'stock' => 100,
                'min_stock' => 20,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'minuman')->first()->id,
                'barcode' => '1234567890126',
                'is_active' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'description' => 'Es jeruk peras segar',
                'price' => 8000.00,
                'stock' => 50,
                'min_stock' => 10,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'minuman')->first()->id,
                'barcode' => '1234567890127',
                'is_active' => true,
            ],
            [
                'name' => 'Kopi Hitam',
                'description' => 'Kopi hitam asli tanpa gula',
                'price' => 10000.00,
                'stock' => 40,
                'min_stock' => 8,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'minuman')->first()->id,
                'barcode' => '1234567890128',
                'is_active' => true,
            ],
            
            // Snack
            [
                'name' => 'Keripik Singkong',
                'description' => 'Keripik singkong renyah original',
                'price' => 12000.00,
                'stock' => 60,
                'min_stock' => 15,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'snack')->first()->id,
                'barcode' => '1234567890129',
                'is_active' => true,
            ],
            [
                'name' => 'Pisang Goreng',
                'description' => 'Pisang goreng crispy',
                'price' => 8000.00,
                'stock' => 35,
                'min_stock' => 10,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'snack')->first()->id,
                'barcode' => '1234567890130',
                'is_active' => true,
            ],
            
            // Es Krim
            [
                'name' => 'Es Krim Vanilla',
                'description' => 'Es krim vanilla premium',
                'price' => 15000.00,
                'stock' => 25,
                'min_stock' => 5,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'es-krim')->first()->id,
                'barcode' => '1234567890131',
                'is_active' => true,
            ],
            [
                'name' => 'Es Krim Coklat',
                'description' => 'Es krim coklat premium',
                'price' => 15000.00,
                'stock' => 25,
                'min_stock' => 5,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'es-krim')->first()->id,
                'barcode' => '1234567890132',
                'is_active' => true,
            ],
            
            // Roti & Kue
            [
                'name' => 'Roti Bakar Coklat',
                'description' => 'Roti bakar dengan selai coklat',
                'price' => 12000.00,
                'stock' => 40,
                'min_stock' => 10,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'roti-kue')->first()->id,
                'barcode' => '1234567890133',
                'is_active' => true,
            ],
            [
                'name' => 'Donat Glaze',
                'description' => 'Donat dengan glazing manis',
                'price' => 8000.00,
                'stock' => 30,
                'min_stock' => 8,
                'terjual' => 0,
                'category_id' => $categories->where('slug', 'roti-kue')->first()->id,
                'barcode' => '1234567890134',
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $productData['sku'] = Product::generateSku();
            Product::create($productData);
        }
    }
}