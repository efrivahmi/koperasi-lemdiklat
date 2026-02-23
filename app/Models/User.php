<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'photo',
        'permissions',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Get the user's photo URL attribute.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function redeemedVouchers()
    {
        return $this->hasMany(Voucher::class, 'used_by');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Hanya Master yang selalu memiliki semua permissions tanpa terkecuali
        if ($this->role === 'master') {
            return true;
        }

        // Siswa dan Guru tidak punya permissions (gunakan role-based saja)
        if (in_array($this->role, ['siswa', 'guru'])) {
            return false;
        }

        // Check permission untuk admin dan kasir
        $permissions = $this->permissions ?? [];
        return isset($permissions[$permission]) && $permissions[$permission] === true;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Grant a permission to the user
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions[$permission] = true;
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Revoke a permission from the user
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions[$permission] = false;
        $this->permissions = $permissions;
        $this->save();
    }
}
