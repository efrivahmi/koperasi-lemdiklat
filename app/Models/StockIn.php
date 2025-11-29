<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class StockIn extends Model
{
    use Auditable;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'supplier',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
