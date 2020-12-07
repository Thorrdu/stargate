<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColonyBuildingsQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colony_buildings_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('colony_id')->unsigned();
            $table->foreign('colony_id')->references('id')->on('colonies');
            $table->integer('building_id')->unsigned();
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->integer('level')->length(3)->default(1);
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
        Schema::table('colony_buildings_queue', function (Blueprint $table) {
            //
        });
    }
}
