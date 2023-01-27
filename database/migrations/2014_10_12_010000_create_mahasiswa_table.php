<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mahasiswa')) {
            Schema::create('mahasiswa', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nama_lengkap');
                $table->string('jenis_kelamin')->default('');
                $table->date('tanggal_lahir')->nullable();
                $table->string('tempat_lahir')->default('');
                $table->string('agama')->default('');
                $table->string('nomor_wa')->default('');
                $table->string('alamat')->default('');
                $table->string('asal_sekolah')->default('');
                $table->string('status_tes')->default('');
                $table->date('tanggal_lulus_tes')->nullable();
                $table->string('status_mahasiswa')->default('');            
                                
                $table->string('jurusan_diminati1');
                $table->string('jurusan_diminati2')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}
