<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExplorationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('explorations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id')->unsigned()->nullable();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('colony_source_id')->unsigned()->nullable();
            $table->foreign('colony_source_id','s_colony_id')->references('id')->on('colonies');
            $table->integer('coordinate_destination_id')->unsigned()->nullable();
            $table->foreign('coordinate_destination_id','d_coordinate_id')->references('id')->on('coordinates');

            $table->timestamp('exploration_end')->nullable();
            $table->boolean('exploration_result')->nullable();
            $table->enum('exploration_outcome', ['Resource', 'Unit', 'Artifact ', 'Tip'])->nullable();

            $table->enum('outcome_resource', ['iron', 'gold', 'quartz ', 'naqahdah', 'E2PZ'])->nullable();
            $table->integer('unit_id')->unsigned()->nullable();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->integer('outcome_quantity')->length(25)->unsigned()->nullable();

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
        Schema::dropIfExists('explorations');
    }
}
