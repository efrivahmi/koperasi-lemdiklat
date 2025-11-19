<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Student extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'name',
        'kelas',
        'jenjang',
        'alamat_lengkap',
        'balance',
        'rfid_uid',
        'foto',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
