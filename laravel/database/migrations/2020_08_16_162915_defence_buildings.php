<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefenceBuildings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defence_buildings', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('defence_id')->unsigned();
            $table->foreign('defence_id')->references('id')->on('defences');
            $table->integer('required_building_id')->unsigned();
            $table->foreign('required_building_id')->references('id')->on('buildings');
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
        //
    }
}
