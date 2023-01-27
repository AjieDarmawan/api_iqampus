<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'auth_role';

    public function user()
    {
        return $this->hasOne(User::class,'id','id_auth');
    }
    public function role()
    {
        return $this->hasOne(Role::class,'id','id_role');
    }
}
