<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ]);

        $query = Product::with('category')->orderByDesc('updated_at');
        if (! empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $products = $query->paginate(15)->withQueryString();
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        return view('admin.products.index', compact('products', 'lowStock'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', ['product' => new Product(['status' => 'draft', 'stock_quantity' => 0]), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $product = Product::create($data + ['slug' => Str::slug($data['title']).'-'.strtolower(Str::random(5))]);
        $this->audit($request, 'product.create', $product->id, ['sku' => $product->sku]);

        return redirect()->route('admin.products.show', $product)->with('success', 'Product created as draft.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'images', 'variants']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product->id));
        $this->audit($request, 'product.update', $product->id, ['sku' => $product->sku]);

        return redirect()->route('admin.products.show', $product)->with('success', 'Product updated.');
    }

    public function setStatus(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:draft,published,archived']]);
        $product->update(['status' => $validated['status'], 'published_at' => $validated['status'] === 'published' ? now() : $product->published_at]);
        $this->audit($request, 'product.status', $product->id, ['to' => $validated['status']]);

        return back()->with('success', 'Product marked '.$validated['status'].'.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        if (OrderItem::where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Cannot delete: this product appears in past orders. Archive it instead to preserve history.');
        }
        $sku = $product->sku;
        $product->delete();
        $this->audit($request, 'product.delete', null, ['sku' => $sku]);

        return redirect()->route('admin.products.index')->with('success', "Product {$sku} deleted.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:60', 'unique:products,sku'.($ignoreId ? ",{$ignoreId}" : '')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'brand' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:draft,published,archived'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function audit(Request $request, string $action, ?int $id, array $meta): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action,
            'auditable_type' => Product::class, 'auditable_id' => $id, 'metadata' => $meta, 'ip_address' => $request->ip()]);
    }
}
