<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKampus extends Model
{
    protected $table = 'kampus';

    public function user()
    {
        return $this->belongsTo(User::class,'id_kampus','id');
    }
    // public function getFotoKampusAttribute($value)
    // {
    //     return asset("images/$value");
    // }
}
