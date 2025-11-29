<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Sale extends Model
{
    use Auditable;

    protected $fillable = [
        'student_id',
        'user_id',
        'payment_method',
        'transaction_method',
        'total',
        'cash_amount',
        'change_amount',
        'status',
        'created_by',
        'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
