<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admins extends Model
{
    use HasFactory;

    protected $table = 'admins';

    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'admin_name',
        'login_id',
        'hashed_password',
        'admin_role',
    ];

    protected $hidden = [
        'hashed_password',
    ];

    public function adminRestaurantRelationships(): HasMany
    {
        return $this->hasMany(AdminRestaurantRelationships::class, 'admin_id', 'admin_id');
    }
}
