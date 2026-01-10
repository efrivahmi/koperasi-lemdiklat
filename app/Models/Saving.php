<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Saving extends Model
{
    use Auditable;

    protected $fillable = [
        'saver_type',
        'saver_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'transaction_date',
        'processed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // Polymorphic relation: saver bisa Student atau Teacher
    public function saver()
    {
        return $this->morphTo();
    }

    // User yang memproses transaksi tabungan
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
