<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlliancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alliances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 125);
            $table->string('tag', 6);
            $table->longText('internal_description')->nullable();
            $table->longText('external_description')->nullable();
            $table->integer('leader_id')->unsigned()->nullable();
            $table->foreign('leader_id')->references('id')->on('players');
            $table->integer('founder_id')->unsigned()->nullable();
            $table->foreign('founder_id')->references('id')->on('players');
            $table->bigInteger('points_total')->length(25)->unsigned()->default(0);
            $table->bigInteger('old_points_total')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_building')->length(25)->unsigned()->default(0);
            $table->bigInteger('old_points_building')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_research')->length(25)->unsigned()->default(0);
            $table->bigInteger('old_points_research')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_military')->length(25)->unsigned()->default(0);
            $table->bigInteger('old_points_military')->length(25)->unsigned()->default(0);
            $table->bigInteger('points_defence')->length(25)->unsigned()->default(0);
            $table->bigInteger('old_points_defence')->length(25)->unsigned()->default(0);
            $table->timestamp('last_top_update')->nullable();
            $table->integer('player_limit')->lenght(3)->unsigned()->default(config('stargate.alliance.baseMembers'));
            $table->boolean('recruitement_status')->default(false);
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
        Schema::dropIfExists('alliances');
    }
}
