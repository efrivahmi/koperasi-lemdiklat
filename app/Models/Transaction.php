<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Transaction extends Model
{
    use Auditable;

    protected $fillable = [
        'student_id',
        'type',
        'transaction_method',
        'amount',
        'ending_balance',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
