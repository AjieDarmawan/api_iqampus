<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\masterKampusController;


class master_kampus extends Model
{
    protected $table = 'master_kampus';

    public function Tagihan(){
        return $this->hasOne(Tagihan::class,'id_kampus','id');
    }
}
