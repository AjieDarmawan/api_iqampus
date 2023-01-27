<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    public function user_role()
    {
        return $this->belongsTo(UserRole::class,'id_role','id');
    }
}
