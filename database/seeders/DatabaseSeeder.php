<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
        ]);

        // === UNITS ===
        $units = [
            'pcs', 'box', 'botol', 'liter', 'pack',
            'lembar', 'meter', 'set', 'buah', 'lusin'
        ];

        $unitIds = [];
        foreach ($units as $unitName) {
            $unitIds[$unitName] = Unit::create(['name' => $unitName])->id;
        }

        // === CATEGORIES ===
        $categories = [
            'Elektronik', 'Minuman', 'Pakaian', 'Peralatan Rumah',
            'Kesehatan', 'Alat Tulis', 'Mainan', 'Otomotif', 'Perabot', 'Aksesoris'
        ];

        $categoryIds = [];
        foreach ($categories as $categoryName) {
            $categoryIds[$categoryName] = Category::create(['name' => $categoryName])->id;
        }

        // === PRODUCTS ===
        $products = [
            ['name' => 'Laptop ASUS ROG',         'price' => 15000000, 'unit' => 'pcs',   'category' => 'Elektronik'],
            ['name' => 'Smartphone Xiaomi',       'price' => 3000000,  'unit' => 'pcs',   'category' => 'Elektronik'],
            ['name' => 'Kopi Bubuk Arabika',      'price' => 50000,    'unit' => 'box',   'category' => 'Minuman'],
            ['name' => 'Teh Celup Sariwangi',     'price' => 15000,    'unit' => 'box',   'category' => 'Minuman'],
            ['name' => 'Air Mineral Botol',       'price' => 4000,     'unit' => 'botol', 'category' => 'Minuman'],
            ['name' => 'Kemeja Batik Pria',       'price' => 120000,   'unit' => 'lembar','category' => 'Pakaian'],
            ['name' => 'Celana Jeans Wanita',     'price' => 175000,   'unit' => 'meter', 'category' => 'Pakaian'],
            ['name' => 'Kipas Angin Maspion',     'price' => 200000,   'unit' => 'pcs',   'category' => 'Peralatan Rumah'],
            ['name' => 'Sampo Lifebuoy',          'price' => 25000,    'unit' => 'botol', 'category' => 'Kesehatan'],
            ['name' => 'Sabun Mandi Lux',         'price' => 12000,    'unit' => 'pack',  'category' => 'Kesehatan'],
            ['name' => 'Pulpen Pilot Biru',       'price' => 7000,     'unit' => 'pcs',   'category' => 'Alat Tulis'],
            ['name' => 'Pensil 2B Faber',         'price' => 3000,     'unit' => 'lusin', 'category' => 'Alat Tulis'],
            ['name' => 'Mainan Robot Anak',       'price' => 85000,    'unit' => 'set',   'category' => 'Mainan'],
            ['name' => 'Obat Paracetamol',        'price' => 10000,    'unit' => 'pack',  'category' => 'Kesehatan'],
            ['name' => 'Minyak Kayu Putih',       'price' => 18000,    'unit' => 'botol', 'category' => 'Kesehatan'],
            ['name' => 'Oli Motor Shell',         'price' => 40000,    'unit' => 'liter', 'category' => 'Otomotif'],
            ['name' => 'Busi Motor NGK',          'price' => 15000,    'unit' => 'pcs',   'category' => 'Otomotif'],
            ['name' => 'Kursi Plastik',           'price' => 60000,    'unit' => 'buah',  'category' => 'Perabot'],
            ['name' => 'Rak Sepatu Kayu',         'price' => 200000,   'unit' => 'set',   'category' => 'Perabot'],
            ['name' => 'Headset Logitech',        'price' => 250000,   'unit' => 'pcs',   'category' => 'Elektronik'],
        ];

        foreach ($products as $p) {
            Product::create([
                'name' => $p['name'],
                'price' => $p['price'],
                'stock' => rand(10, 100),
                'unit_id' => $unitIds[$p['unit']],
                'category_id' => $categoryIds[$p['category']],
                'sku' => strtoupper(substr($p['category'], 0, 3)).'-'.strtoupper(substr($p['name'], 0, 2)).'-'.str_pad(rand(10,99) + 1, 4, '0', STR_PAD_LEFT)
            ]);
        }
    }
}
