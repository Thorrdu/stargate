<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TechnologyTechnologies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technology_technologies', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('technology_id')->unsigned();
            $table->foreign('technology_id')->references('id')->on('technologies');
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
        Schema::dropIfExists('technology_technologies');
    }
}
