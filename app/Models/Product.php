<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Product extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image_path',
        'stock',
        'harga_beli',
        'harga_jual',
        'barcode',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }
}
