<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'delivery_inside_dhaka'], ['value' => '60', 'group' => 'shipping']);
        Setting::updateOrCreate(['key' => 'delivery_outside_dhaka'], ['value' => '120', 'group' => 'shipping']);

        $cats = [
            ['Kitchen & Dining', 'kitchen-dining'], ['Home & Living', 'home-living'],
            ['Personal Care', 'personal-care'], ['Grocery Staples', 'grocery-staples'],
            ['Apparel Basics', 'apparel-basics'], ['Stationery', 'stationery'],
        ];
        foreach ($cats as $i => [$name, $slug]) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true, 'sort_order' => $i]);
        }
    }
}
