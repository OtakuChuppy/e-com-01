<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CsvImportController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.products.csv_import', [
            'categories' => $categories,
            'columns' => $this->columnHints(),
        ]);
    }

    public function downloadTemplate(): Response
    {
        $filename = 'product-import-template.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache',
        ];

        $columns = $this->columnHints();
        $lines = [$this->csvRow(array_keys($columns))];

        $lines[] = $this->csvRow([
            'sku' => 'IMP-0001',
            'title' => 'Sample Product imported via CSV',
            'category_slug' => 'kitchen-dining',
            'description' => 'Imported description',
            'short_description' => 'Short intro',
            'brand' => 'Importer',
            'status' => 'published',
            'base_price' => '990.00',
            'compare_at_price' => '1290.00',
            'cost_price' => '600.00',
            'stock_quantity' => '25',
            'low_stock_threshold' => '5',
            'weight_grams' => '1200',
            'is_featured' => '1',
            'is_demo' => '0',
            'meta_title' => 'Imported product SEO',
            'meta_description' => 'SEO meta for imported product',
            'canonical_url' => null,
            'published_at' => null,
        ]);

        return response(implode("\n", $lines)."\n", 200, $headers);
    }
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'category_slug' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $raw = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", $raw));

        if (empty($rows) || empty($rows[0])) {
            return back()->with('error', 'Uploaded file is empty.');
        }

        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $data = array_slice($rows, 1);
        $data = array_filter($data, fn ($r) => ! empty($r) && ! (count($r) === 1 && trim($r[0]) === ''));

        if (empty($data)) {
            return back()->with('error', 'No data rows found after the header.');
        }

        $required = ['sku', 'title', 'base_price', 'stock_quantity'];
        $missing = array_values(array_diff($required, $header));
        if (! empty($missing)) {
            return back()->with('error', 'Missing required column(s): '.implode(', ', $missing));
        }

        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
        $firstError = null;
        $categoryCache = [];

        foreach ($data as $index => $row) {
            $record = [];
            foreach ($header as $i => $col) {
                $val = $i < count($row) ? trim((string) $row[$i]) : '';
                $record[$col] = $val;
            }

            if (empty($record['sku'])) {
                $summary['skipped']++;
                continue;
            }

            try {
                DB::transaction(function () use ($record, $categoryCache, &$summary, $index, &$firstError) {
                    $sku = $record['sku'];
                    $title = $record['title'] ?? '';

                    if (empty($title)) {
                        $summary['errors']++;
                        $firstError = $firstError ?: 'Row ' . ($index + 2) . ': SKU ' . $sku . ' has no title.';
                        return;
                    }

                    $categoryId = null;
                    if (! empty($record['category_slug'])) {
                        $categoryCacheKey = $record['category_slug'];
                        if (! isset($categoryCache[$categoryCacheKey])) {
                            $categoryCache[$categoryCacheKey] = Category::where('slug', $categoryCacheKey)->first()->id ?? null;
                        }
                        $categoryId = $categoryCache[$categoryCacheKey];
                    }

                    $basePrice = $this->parseDecimal($record['base_price'] ?? null);
                    if ($basePrice === null) {
                        $summary['errors']++;
                        $firstError = $firstError ?: 'Row ' . ($index + 2) . ': SKU ' . $sku . ' has an invalid base_price.';
                        return;
                    }

                    $stock = (int) ($record['stock_quantity'] ?? 0);
                    if ($stock < 0) {
                        $summary['errors']++;
                        $firstError = $firstError ?: 'Row ' . ($index + 2) . ': SKU ' . $sku . ' has a negative stock_quantity.';
                        return;
                    }

                    $product = Product::updateOrCreate(
                        ['sku' => $sku],
                        [
                            'category_id' => $categoryId,
                            'title' => $title,
                            'description' => $record['description'] ?? null,
                            'short_description' => $record['short_description'] ?? null,
                            'brand' => $record['brand'] ?? null,
                            'status' => $record['status'] ?? 'draft',
                            'base_price' => $basePrice,
                            'compare_at_price' => $this->parseDecimal($record['compare_at_price'] ?? null),
                            'cost_price' => $this->parseDecimal($record['cost_price'] ?? null),
                            'stock_quantity' => $stock,
                            'low_stock_threshold' => (int) ($record['low_stock_threshold'] ?? 5),
                            'weight_grams' => $this->parseUnsignedInteger($record['weight_grams'] ?? null),
                            'is_featured' => $this->parseBool($record['is_featured'] ?? '0'),
                            'is_demo' => $this->parseBool($record['is_demo'] ?? '0'),
                            'meta_title' => $record['meta_title'] ?? null,
                            'meta_description' => $record['meta_description'] ?? null,
                            'canonical_url' => $record['canonical_url'] ?? null,
                            'published_at' => $this->parseDate($record['published_at'] ?? null),
                        ]
                    );

                    if ($product->wasRecentlyCreated) {
                        $summary['created']++;
                    } else {
                        $summary['updated']++;
                    }

                    if (! empty($record['variant_sku'])) {
                        $this->createVariant(
                            $product,
                            $record['variant_sku'],
                            $record['variant_name'] ?? null,
                            $this->parseDecimal($record['variant_price_override'] ?? null),
                            (int) ($record['variant_stock_quantity'] ?? $stock),
                            $this->parseBool($record['variant_is_active'] ?? '1')
                        );
                    }
                });
            } catch (\Throwable $e) {
                $summary['errors']++;
                $firstError = $firstError ?: 'Row ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        $messages = [];
        if ($summary['created']) {
            $messages[] = 'Created ' . $summary['created'] . ' new product(s).';
        }
        if ($summary['updated']) {
            $messages[] = 'Updated ' . $summary['updated'] . ' existing product(s).';
        }
        if ($summary['skipped']) {
            $messages[] = 'Skipped ' . $summary['skipped'] . ' row(s) without a SKU.';
        }
        if ($summary['errors']) {
            $messages[] = $summary['errors'] . ' row(s) had errors: ' . $firstError;
        }

        foreach ($messages as $msg) {
            session()->flash('csv_import_' . $msg, $msg);
        }

        if ($summary['errors'] && ! $summary['created'] && ! $summary['updated']) {
            return back()->with('error', implode(' ', $messages));
        }

        return back()->with('success', implode(' ', $messages));
    }

    private function columnHints(): array
    {
        return [
            'sku' => 'Unique product identifier (required)',
            'title' => 'Product display name (required)',
            'category_slug' => 'Optional category slug for grouping',
            'description' => 'Full product description',
            'short_description' => 'Short product summary',
            'brand' => 'Product brand name',
            'status' => 'draft, published, or archived (default: draft)',
            'base_price' => 'Base price in cents (required)',
            'compare_at_price' => 'Optional compare-at price',
            'cost_price' => 'Optional cost price',
            'stock_quantity' => 'Current inventory count (required)',
            'low_stock_threshold' => 'Low stock alert threshold (default: 5)',
            'weight_grams' => 'Product weight in grams',
            'is_featured' => '1 for featured, 0 otherwise (default: 0)',
            'is_demo' => '1 for demo product, 0 otherwise (default: 0)',
            'meta_title' => 'SEO meta title',
            'meta_description' => 'SEO meta description',
            'canonical_url' => 'Canonical URL for SEO',
            'published_at' => 'Publish date (YYYY-MM-DD)',
            'variant_sku' => 'Optional variant SKU',
            'variant_name' => 'Optional variant name',
            'variant_price_override' => 'Optional variant price override',
            'variant_stock_quantity' => 'Optional variant stock quantity',
            'variant_is_active' => '1 for active variant, 0 otherwise (default: 1)',
        ];
    }

    private function createVariant(Product $product, string $sku, ?string $name, ?float $price, int $stock, bool $isActive): void
    {
        ProductVariant::updateOrCreate(
            ['sku' => $sku],
            [
                'product_id' => $product->id,
                'name' => $name,
                'price_override' => $price,
                'stock_quantity' => $stock,
                'is_active' => $isActive,
            ]
        );
    }

    private function parseDecimal(?string $value): ?float
    {
        if (empty($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        $parsed = (float) $cleaned;

        return $parsed === 0.0 && $cleaned !== '0' && $cleaned !== '0.0' ? null : $parsed;
    }

    private function parseUnsignedInteger(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $int = (int) $value;
        return $int < 0 ? null : $int;
    }

    private function parseBool(?string $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function parseDate(?string $value): ?\Illuminate\Support\Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function csvRow(array $fields): string
    {
        $line = [];
        foreach ($fields as $field) {
            $line[] = '"'.str_replace('"', '""', (string) $field).'"';
        }

        return implode(',', $line);
    }
}