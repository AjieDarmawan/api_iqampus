<?php

namespace Database\Factories;

use App\Model;
use Faker\Provider\id_ID\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class MahasiswaFactory extends Factory
{
    protected $model = Model::class;

    function jurusan($i){
        if($i==0){
            return "ilkom";
        }
        return "teknik informatika";
    }
    public function definition(): array
    {
    	return [
            'nama_lengkap'=>$this->faker->name('male'),
            'jenis_kelamin'=>Person::GENDER_MALE,
            'tanggal_lahir'=>date('Y-m-d'),
            'tempat_lahir'=>$this->faker->cityName,
            'agama'=>"islam",
            'nomor_wa'=>$this->faker->mobileNumber,
            'asal_sekolah'=>"SMA ".rand(1,10),
            'status_tes'=>rand(0,1),
            'tanggal_lulus_tes'=>date('Y-m-d'),
            'status_mahasiswa'=>rand(0,1),
            'jurusan_diminati1'=>$this->jurusan(rand(0,1)),
            'jurusan_diminati2'=>null
    	];
    }
}
