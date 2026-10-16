<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'change_qty', 'balance_after', 'reason', 'reference', 'created_by'];
}
