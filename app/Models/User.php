<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'outlet_id', 'employee_id', 'name', 'email', 'phone', 
    'password', 'pin', 'avatar_path', 'role', 'is_active',
    'last_login_at', 'last_login_ip', 'two_factor_secret', 
    'two_factor_enabled', 'preferences'
])]
#[Hidden(['password', 'pin', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
            'pin' => 'hashed',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }
    
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}