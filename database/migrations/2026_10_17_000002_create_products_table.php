<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('brand')->nullable();
            $table->string('status')->default('draft'); // draft|published|archived
            $table->decimal('base_price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->unsignedInteger('weight_grams')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
            // Fulltext index is MySQL-only; search uses LIKE on SQLite dev, FULLTEXT on MySQL prod.
            // Run on MySQL prod: ALTER TABLE products ADD FULLTEXT ft_title_desc (title, description);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
