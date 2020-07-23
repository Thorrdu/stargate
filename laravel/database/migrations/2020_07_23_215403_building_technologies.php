<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuildingTechnologies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_technologies', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('building_id')->unsigned();
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->integer('required_technology_id')->unsigned();
            $table->foreign('required_technology_id')->references('id')->on('technologies');
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
        Schema::dropIfExists('building_technologies');
    }
}
