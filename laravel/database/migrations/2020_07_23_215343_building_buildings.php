<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuildingBuildings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_buildings', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('building_id')->unsigned();
            $table->foreign('building_id')->references('id')->on('buildings');
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
        Schema::dropIfExists('building_buildings');
    }
}
