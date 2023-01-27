<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Payment extends Model
{
    protected $table = 'payment';
    public $timestamps = false;

    public function kampus(){
        return $this->hasOne(master_kampus::class,'id','id_kampus');
    }
    public function auth(){
        return $this->hasOne(User::class,'id','id_auth');
    }
    public function tagihan_detail(){
        return $this->belongsToMany(PayementDetail::class,'id','id_payment');
    }
}
