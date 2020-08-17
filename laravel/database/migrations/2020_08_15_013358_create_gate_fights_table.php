<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGateFightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gate_fights', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id_source')->unsigned()->nullable();
            $table->foreign('player_id_source')->references('id')->on('players');
            $table->integer('colony_id_source')->unsigned()->nullable();
            $table->foreign('colony_id_source')->references('id')->on('colonies');
            $table->integer('player_id_dest')->unsigned()->nullable();
            $table->foreign('player_id_dest')->references('id')->on('players');
            $table->integer('colony_id_dest')->unsigned()->nullable();
            $table->foreign('colony_id_dest')->references('id')->on('colonies');
            $table->integer('military_source')->length(18)->unsigned();
            $table->integer('military_dest')->length(18)->unsigned();
            $table->integer('player_id_winner')->unsigned()->nullable();
            $table->foreign('player_id_winner')->references('id')->on('players');
            $table->integer('military_outcome')->length(18)->unsigned();
            $table->integer('military_stolen')->length(18)->unsigned();
            $table->integer('iron')->length(18)->unsigned()->nullable();
            $table->integer('gold')->length(18)->unsigned()->nullable();
            $table->integer('quartz')->length(18)->unsigned()->nullable();
            $table->integer('naqahdah')->length(18)->unsigned()->nullable();
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
        Schema::dropIfExists('gate_fights');
    }
}
