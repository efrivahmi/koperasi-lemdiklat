<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Transaction extends Model
{
    use Auditable;

    protected $fillable = [
        'buyer_type',
        'buyer_id',
        'student_id', // Kept for backward compatibility
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

    // Polymorphic relation: buyer bisa Student atau Teacher
    public function buyer()
    {
        return $this->morphTo();
    }

    // Legacy relation: backward compatibility dengan student_id
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
