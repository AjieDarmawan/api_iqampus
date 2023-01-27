<?php

use App\Models\mahasiswa;
use Faker\Provider\id_ID\Person;
use Illuminate\Database\Seeder;
use Faker\Generator;
use Illuminate\Container\Container;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = $this->withFaker();
    }
    
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    function jurusan($i){
        if($i==0){
            return "ilkom";
        }
        return "teknik informatika";
    }
    public function run()
    {
        // $this->call('UsersTableSeeder');
        // mahasiswa::factory(100000000000)->create();
        for($i=0;$i<100000000000;$i++){
            $mhs = mahasiswa::insert([
                'nama_lengkap'=>$this->faker->name('male'),
                'jenis_kelamin'=>Person::GENDER_MALE,
                'tanggal_lahir'=>date('Y-m-d'),
                'tempat_lahir'=>"bogor",
                'agama'=>"islam",
                'nomor_wa'=>"0811111111",
                'asal_sekolah'=>"SMA ".rand(1,10),
                'status_tes'=>rand(0,1),
                'tanggal_lulus_tes'=>date('Y-m-d'),
                'status_mahasiswa'=>rand(0,1),
                'jurusan_diminati1'=>$this->jurusan(rand(0,1)),
                'jurusan_diminati2'=>null
            ]);
        }
    }
}
