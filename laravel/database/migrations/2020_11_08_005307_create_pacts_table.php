<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_1_id')->unsigned();
            $table->foreign('player_1_id')->references('id')->on('players');
            $table->integer('player_2_id')->unsigned();
            $table->foreign('player_2_id')->references('id')->on('players');
            $table->enum('type', ['Trade'])->default('Trade');
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
        Schema::dropIfExists('pacts');
    }
}
