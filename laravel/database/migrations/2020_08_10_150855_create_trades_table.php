<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_player_id')->unsigned()->nullable();
            $table->foreign('source_player_id')->references('id')->on('players');
            $table->integer('coordinate_source_id')->unsigned()->nullable();
            $table->foreign('coordinate_source_id','s_coordinate_id')->references('id')->on('coordinates');
            $table->integer('dest_player_id')->unsigned()->nullable();
            $table->foreign('dest_player_id')->references('id')->on('players');
            $table->integer('coordinate_destination_id')->unsigned()->nullable();
            $table->foreign('coordinate_destination_id','d_coordinate_id')->references('id')->on('coordinates');
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
        Schema::dropIfExists('trades');
    }
}
