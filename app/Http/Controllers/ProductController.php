<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $query = Product::published()->with(['category', 'images']);

        if (! empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $activeCategory = null;
        if (! empty($validated['category'])) {
            $activeCategory = Category::where('slug', $validated['category'])->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        if (! empty($validated['max_price'])) {
            $query->where('base_price', '<=', $validated['max_price']);
        }

        match ($validated['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'name' => $query->orderBy('title'),
            default => $query->orderByDesc('published_at'),
        };

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::active()->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories', 'activeCategory'));
    }

    public function show(string $slug): View
    {
        $product = Product::published()->with(['category', 'images', 'variants'])
            ->where('slug', $slug)->firstOrFail();

        $related = Product::published()->with(['images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)->get();

        return view('products.show', compact('product', 'related'));
    }
}
