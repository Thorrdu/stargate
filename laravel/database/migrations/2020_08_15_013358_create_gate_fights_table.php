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
            $table->bigInteger('military_source')->length(25)->unsigned()->nullable();
            $table->bigInteger('military_dest')->length(25)->unsigned()->nullable();
            $table->bigInteger('attacker_lost_value')->length(25)->unsigned()->default(0);
            $table->bigInteger('defender_lost_value')->length(25)->unsigned()->default(0);
            $table->integer('player_id_winner')->unsigned()->nullable();
            $table->foreign('player_id_winner')->references('id')->on('players');
            $table->bigInteger('military_outcome')->length(25)->unsigned()->nullable();
            $table->bigInteger('military_stolen')->length(25)->unsigned()->nullable();
            $table->bigInteger('iron')->length(25)->unsigned()->nullable();
            $table->bigInteger('gold')->length(25)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(25)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(25)->unsigned()->nullable();
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
        Schema::dropIfExists('gate_fights');
    }
}
