<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjar extends Model
{
    protected $table = 'tahun_ajar';
    public $timestamps = false;

    public function komponen_biaya(){
        return $this->hasOne(KomponenBiaya::class,'id_tahun_ajar','id');
    }
}
