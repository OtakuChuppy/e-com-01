<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_zone', 20)->default('inside_dhaka')->after('district');
        });
        // Backfill: legacy free-text districts map to a zone.
        DB::table('orders')->whereRaw("LOWER(TRIM(district)) = 'dhaka'")->update(['delivery_zone' => 'inside_dhaka']);
        DB::table('orders')->whereRaw("LOWER(TRIM(district)) <> 'dhaka'")->update(['delivery_zone' => 'outside_dhaka']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_zone');
        });
    }
};
