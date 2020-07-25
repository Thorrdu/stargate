<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayersPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('points_total')->length(10)->unsigned()->default(0);
            $table->integer('points_building')->length(10)->unsigned()->default(0);
            $table->integer('points_research')->length(10)->unsigned()->default(0);
            $table->integer('points_military')->length(10)->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
