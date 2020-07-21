<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player_id')->length(18)->unsigned();
            $table->foreign('player_id','log_player_id')->references('id')->on('players');
            $table->string('command_type', 50);
            $table->string('command_raw', 255);
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
        Schema::dropIfExists('command_logs');
    }
}
