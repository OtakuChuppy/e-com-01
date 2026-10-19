<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // Admin product image upload/delete/set-primary. Shudhumatro image file grahon
    // kora hoye (mime validation), executable upload rodh kora hoyeche. Protiti
    // action audit log kora hoyeche.

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'max:6'],
            'images.*' => ['file', 'image', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:120'],
        ]);

        $isFirst = $product->images()->count() === 0;
        foreach ($request->file('images') as $file) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $file->store('products', 'public'),
                'alt_text' => $validated['alt_text'] ?? null,
                'sort_order' => $product->images()->count(),
                'is_primary' => $isFirst,
            ]);
            $isFirst = false;
        }

        $this->audit($request, 'product.images.upload', $product->id, ['count' => count($request->file('images'))]);

        return back()->with('success', 'Images uploaded.');
    }

    public function destroy(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        if (! str_starts_with($image->path, 'http')) {
            Storage::disk('public')->delete($image->path);
        }

        $wasPrimary = (bool) $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        $this->audit($request, 'product.images.delete', $product->id, ['image_id' => $image->id]);

        return back()->with('success', 'Image deleted.');
    }

    public function setPrimary(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        $this->audit($request, 'product.images.primary', $product->id, ['image_id' => $image->id]);

        return back()->with('success', 'Primary image updated.');
    }

    private function audit(Request $request, string $action, int $id, array $meta): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action,
            'auditable_type' => Product::class, 'auditable_id' => $id, 'metadata' => $meta, 'ip_address' => $request->ip()]);
    }
}
