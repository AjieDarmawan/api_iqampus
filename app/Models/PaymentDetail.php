<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $table = 'payment_detail';
    public $timestamps = false;

    public function tagihan(){
        return $this->hasMany(Payment::class,'id_payment','id');
    }

     public function komponen_biaya(){
        return $this->hasMany(KomponenBiaya::class,'id_komponen_biaya','id');
    }



}
