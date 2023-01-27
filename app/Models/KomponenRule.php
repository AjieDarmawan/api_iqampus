<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenRule extends Model
{
    protected $table = 'komponen_rule';
    public $timestamps = false;

    public function from_komponen(){
        return $this->hasOne(MasterKomponen::class,'id_komponen','from_komponen');
    }
    public function to_komponen(){
        return $this->hasOne(MasterKomponen::class,'id_komponen','to_komponen');
    }
}
