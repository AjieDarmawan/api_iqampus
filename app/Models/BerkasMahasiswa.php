<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasMahasiswa extends Model
{
    protected $table = 'berkas_mahasiswa';

    public function mahasiswa(){
        return $this->hasMany(mahasiswa::class,'id_mahasiswa','id');
    }
}
