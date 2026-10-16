<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $images = [
            'https://images.unsplash.com/photo-1556909212-d5b604d0c90d?w=600&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=600&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=600&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1585232004423-244e0e6904e3?w=600&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=600&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=75&auto=format&fit=crop',
        ];

        $items = [
            ['Steel Tiffin Carrier 3-Tier', 'kitchen-dining', 850, 990, 'Kitchen'],
            ['Ceramic Dinner Plate Set (6 pc)', 'kitchen-dining', 1450, null, 'Kitchen'],
            ['Non-stick Fry Pan 24cm', 'kitchen-dining', 1150, 1350, 'Kitchen'],
            ['Cotton Bedsheet King + 2 Pillows', 'home-living', 1250, 1490, 'Home'],
            ['Bamboo Storage Basket (Set 2)', 'home-living', 640, null, 'Home'],
            ['LED Table Lamp Warm White', 'home-living', 980, 1150, 'Home'],
            ['Neem Soap Bar (Pack of 3)', 'personal-care', 240, 290, 'Care'],
            ['Coconut Hair Oil 200ml', 'personal-care', 320, null, 'Care'],
            ['Bamboo Toothbrush (Pack 4)', 'personal-care', 280, 340, 'Care'],
            ['Premium Miniket Rice 5kg', 'grocery-staples', 720, null, null],
            ['Mustard Oil 1L', 'grocery-staples', 380, 420, null],
            ['Masoor Dal 1kg', 'grocery-staples', 165, null, null],
            ['Panjabi — Slim Fit White', 'apparel-basics', 1350, 1590, null],
            ['Cotton T-Shirt (Navy)', 'apparel-basics', 450, 550, null],
            ['Lungi — Premium Cotton', 'apparel-basics', 520, null, null],
            ['Notebook A5 Ruled (3 pc)', 'stationery', 260, null, null],
            ['Ball Pen Set (10 pc)', 'stationery', 180, 220, null],
            ['Jute Tote Bag', 'home-living', 350, null, 'Home'],
            ['Clay Water Jug 2L', 'kitchen-dining', 480, null, 'Kitchen'],
            ['Incense Sticks Sandal (3 box)', 'home-living', 210, null, 'Home'],
        ];

        foreach ($items as $i => [$title, $catSlug, $price, $compare, $brand]) {
            $cat = Category::where('slug', $catSlug)->first();
            $sku = 'DM-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $desc = 'Sample product for client preview — not real inventory.';
            $product = Product::firstOrCreate(['sku' => $sku], [
                'category_id' => $cat?->id, 'slug' => Str::slug($title).'-demo',
                'title' => $title.' (Demo)', 'short_description' => $desc, 'description' => $desc,
                'brand' => $brand, 'status' => 'published', 'base_price' => $price,
                'compare_at_price' => $compare, 'stock_quantity' => 25 + ($i * 3) % 40,
                'is_featured' => $i < 8, 'is_demo' => true, 'published_at' => now()->subDays(20 - $i),
            ]);
            if ($product->images()->count() === 0) {
                ProductImage::create(['product_id' => $product->id, 'path' => $images[$i % count($images)],
                    'alt_text' => $title, 'sort_order' => 0, 'is_primary' => true]);
            }
        }

        $panjabi = Product::where('sku', 'DM-0013')->first();
        if ($panjabi && $panjabi->variants()->count() === 0) {
            foreach ([['Size M', null, 10], ['Size L', 50, 15], ['Size XL', 100, 20]] as [$n, $extra, $stock]) {
                ProductVariant::create(['product_id' => $panjabi->id, 'name' => $n,
                    'sku' => $panjabi->sku.'-'.strtoupper(str_replace(' ', '', $n)),
                    'price_override' => $extra ? $panjabi->base_price + $extra : null,
                    'stock_quantity' => $stock, 'is_active' => true]);
            }
        }
    }
}
