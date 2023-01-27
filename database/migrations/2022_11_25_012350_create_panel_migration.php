<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePanelMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('panel_migration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_panel');
            $table->unsignedBigInteger('id_modul');
            $table->string('role');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_panel')->references('id')->on('panel')->onUpdate('cascade')->onDelete('cascade');;
            $table->foreign('id_modul')->references('id')->on('modul')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('panel_migration');
    }
}
