<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhipPartTechnologies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_part_technologies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ship_part_id')->unsigned();
            $table->foreign('ship_part_id')->references('id')->on('ship_parts');
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
        Schema::dropIfExists('ship_part_technologies');
    }
}
