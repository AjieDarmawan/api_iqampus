<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'm_dosen';
    protected $primaryKey = 'id_dosen';

    // public function berkas_mahasiswa(){
    //     return $this->belongsToMany(BerkasMahasiswa::class,'id','id_mahasiswa');
    // }
}
