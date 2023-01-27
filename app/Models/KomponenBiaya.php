<?php

namespace App\Models;

use App\Models\Kampus;
use Illuminate\Database\Eloquent\Model;

class KomponenBiaya extends Model
{
    protected $table = 'komponen_biaya';
    public $timestamps = false;

    public function master_komponen(){
        return $this->hasOne(MasterKomponen::class,'id_komponen','id_komponen');
    }
    public function kampus(){
        return $this->hasOne(master_kampus::class,'id','id_kampus');
    }
    public function tahun_ajar(){
        return $this->hasOne(TahunAjar::class,'id','id_tahun_ajar');
    }
    public function jurusan(){
        return $this->hasOne(Jurusan::class,'id','id_jurusan');
    }
}
