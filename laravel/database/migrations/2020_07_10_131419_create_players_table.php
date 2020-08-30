<?php

use Brick\Math\BigInteger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->length(18)->unsigned();
            $table->unique('user_id');
            $table->string('user_name', 50);
            $table->enum('lang', ['fr', 'en']);
            $table->boolean('notification')->default(false);
            $table->boolean('ban')->default(false);
            $table->integer('votes')->default(0);
            $table->bigInteger('points_total')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_building')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_research')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_military')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_defence')->length(25)->unsigned()->default(0);
            $table->timestamp('last_top_update')->nullable();
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
        Schema::dropIfExists('players');
    }
}
