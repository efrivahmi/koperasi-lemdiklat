<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Expense extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
