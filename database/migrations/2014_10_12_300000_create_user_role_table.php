<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_auth');
            $table->unsignedBigInteger('id_role');
            $table->timestamps();

            $table->foreign('id_auth')->references('id')->on('auth')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_role')->references('id')->on('role')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_role');
    }
}
