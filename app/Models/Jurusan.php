<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'm_jurusan';
    public $timestamps = false;

    public function komponen_biaya(){
        return $this->hasOne(KomponenBiaya::class,'id_jurusan','id');
    }
}
