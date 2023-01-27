<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKomponen extends Model
{
    protected $table = 'm_komponen';
    public $timestamps = false;

    public function komponen_biaya(){
        return $this->belongsTo(KomponenBiaya::class,'id_komponen','id_komponen');
    }
    public function KomponenRule1(){
        return $this->belongsTo(KomponenRule::class,'from_komponen','id_komponen');
    }
    public function KomponenRule2(){
        return $this->belongsTo(KomponenRule::class,'to_komponen','id_komponen');
    }
}
