<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class StockAdjustment extends Model
{
    use Auditable;

    protected $fillable = [
        'product_id',
        'quantity_before',
        'quantity_adjusted',
        'quantity_after',
        'type',
        'purpose',
        'notes',
        'client_name',
        'adjusted_by',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
