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
            $table->integer('player_id_source')->unsigned()->nullable();
            $table->foreign('player_id_source')->references('id')->on('players');
            $table->integer('colony_source_id')->unsigned()->nullable();
            $table->foreign('colony_source_id')->references('id')->on('colonies');
            $table->integer('trade_value_player1')->length(18)->unsigned();
            $table->integer('player_id_dest')->unsigned()->nullable();
            $table->foreign('player_id_dest')->references('id')->on('players');
            $table->integer('colony_destination_id')->unsigned()->nullable();
            $table->foreign('colony_destination_id')->references('id')->on('colonies');
            $table->integer('trade_value_player2')->length(18)->unsigned();
            $table->boolean('active')->default(true);
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
