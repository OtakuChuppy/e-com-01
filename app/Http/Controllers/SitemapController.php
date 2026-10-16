<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::published()->orderByDesc('published_at')->take(500)->get(['slug', 'updated_at']);

        $xml = view('sitemap', compact('products'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $mode = Setting::get('robots_mode', app()->isProduction() ? 'live' : 'staging');
        $body = $mode === 'live'
            ? "User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body, 200)->header('Content-Type', 'text/plain');
    }
}
