<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'slug' => 'makanan',
                'description' => 'Kategori produk makanan',
                'icon' => 'phosphor.hamburger',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Minuman',
                'slug' => 'minuman',
                'description' => 'Kategori produk minuman',
                'icon' => 'phosphor.coffee',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Snack',
                'slug' => 'snack',
                'description' => 'Kategori produk snack dan cemilan',
                'icon' => 'phosphor.cookie',
                'color' => '#45B7D1',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Es Krim',
                'slug' => 'es-krim',
                'description' => 'Kategori produk es krim dan dessert',
                'icon' => 'phosphor.ice-cream',
                'color' => '#F7DC6F',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Roti & Kue',
                'slug' => 'roti-kue',
                'description' => 'Kategori produk roti dan kue',
                'icon' => 'phosphor.bread',
                'color' => '#BB8FCE',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}