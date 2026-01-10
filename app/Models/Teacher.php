<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Teacher extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'name',
        'jabatan',
        'mata_pelajaran',
        'jenjang',
        'alamat_lengkap',
        'balance',
        'rfid_uid',
        'foto',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // Relasi ke User (1 teacher = 1 user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relation: Teacher sebagai buyer di Sales
    public function sales()
    {
        return $this->morphMany(Sale::class, 'buyer');
    }

    // Polymorphic relation: Teacher sebagai buyer di Transactions
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'buyer');
    }

    // Polymorphic relation: Teacher sebagai saver di Savings
    public function savings()
    {
        return $this->morphMany(Saving::class, 'saver');
    }

    // Vouchers yang dimiliki guru
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
