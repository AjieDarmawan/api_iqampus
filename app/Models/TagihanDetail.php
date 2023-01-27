<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanDetail extends Model
{
    protected $table = 'tagihan_detail';
    public $timestamps = false;

    public function tagihan(){
        return $this->hasMany(Tagihan::class,'id_tagihan','id');
    }
}
