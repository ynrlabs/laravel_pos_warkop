<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'npwp',
        'logo_path',
        'receipt_footer',
        'operational_start',
        'operational_end',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'operational_start' => 'datetime:H:i:s',
        'operational_end' => 'datetime:H:i:s',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function printers()
    {
        return $this->hasMany(Printer::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
