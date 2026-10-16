<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique(); // e.g. DM-8F3K2Q non-guessable
            $table->string('lookup_token', 64)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot of delivery info (preserved even if customer edits later)
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('area')->nullable();
            $table->string('district');
            $table->string('postal_code')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('unpaid'); // unpaid|paid|refunded
            $table->string('status')->default('pending'); // pending|confirmed|on_hold|shipped|delivered|cancelled
            $table->text('admin_note')->nullable();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamps();
            $table->index('status');
            $table->index('placed_at');
            $table->index('customer_phone');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name'); // snapshot
            $table->string('sku'); // snapshot
            $table->string('variant_name')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index('order_id');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('change_qty');
            $table->integer('balance_after');
            $table->string('reason'); // purchase|sale|adjustment|return|cancelled|import
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
