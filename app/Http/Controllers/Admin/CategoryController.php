<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    // Admin category management. Category-te parent-child hierarchy thake. Product
    // thaka category delete kora jay na — prothome product-gulo move ba archive korte hobe.
    // Protiti CRUD action audit log kora hoyeche.

    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $query = Category::withCount('products')->orderBy('sort_order')->orderBy('name');
        if (! empty($request->q)) {
            $q = $request->q;
            $query->where('name', 'like', "%{$q}%");
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $category = Category::create($data);
        $this->audit($request, 'category.create', $category->id, ['name' => $category->name]);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Category created.');
    }

    public function show(Category $category): View
    {
        $category->load(['products' => function ($q) {
            $q->orderByDesc('updated_at');
        }, 'parent', 'children']);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request, $category->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $category->update($data);
        $this->audit($request, 'category.update', $category->id, ['name' => $category->name]);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Category updated.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete: this category still has products. Move or archive them first.');
        }
        foreach ($category->children as $child) {
            $child->update(['parent_id' => null]);
        }
        $name = $category->name;
        $category->delete();
        $this->audit($request, 'category.delete', null, ['name' => $name]);

        return redirect()->route('admin.categories.index')->with('success', "Category {$name} deleted.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:categories,slug'.($ignoreId ? ",{$ignoreId}" : '')],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function audit(Request $request, string $action, ?int $id, array $meta): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action,
            'auditable_type' => Category::class, 'auditable_id' => $id, 'metadata' => $meta, 'ip_address' => $request->ip()]);
    }
}
