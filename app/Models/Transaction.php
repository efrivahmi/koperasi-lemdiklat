<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'ending_balance',
        'description',
        'reference_type',
        'reference_id',
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
