<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()->orderBy('sort_order')->take(6)->get();
        $featured = Product::published()->with(['category', 'images'])
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->take(8)->get();
        $latest = Product::published()->with(['category', 'images'])
            ->orderByDesc('published_at')
            ->take(8)->get();

        return view('home', compact('categories', 'featured', 'latest'));
    }
}
