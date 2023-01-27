<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    public $timestamps = false;

    public function kampus(){
        return $this->hasOne(master_kampus::class,'id','id_kampus');
    }
    public function auth(){
        return $this->hasOne(User::class,'id','id_auth');
    }
    public function tagihan_detail(){
        return $this->belongsToMany(TagihanDetail::class,'id','id_tagihan');
    }
}
