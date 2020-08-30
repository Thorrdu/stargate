<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayerAlliance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {

            $table->string('untagged_user_name', 50)->default("not loaded")->after('user_name');

            $table->integer('alliance_id')->unsigned()->nullable()->after('lang');
            $table->foreign('alliance_id')->references('id')->on('alliances');
            $table->integer('role_id')->unsigned()->nullable()->after('alliance_id');
            $table->foreign('role_id')->references('id')->on('alliance_roles');

            $table->bigInteger('old_points_total')->length(25)->unsigned()->default(0)->after('points_total');
            $table->bigInteger('old_points_building')->length(25)->unsigned()->default(0)->after('points_building');
            $table->bigInteger('old_points_research')->length(25)->unsigned()->default(0)->after('points_research');
            $table->bigInteger('old_points_military')->length(25)->unsigned()->default(0)->after('points_military');
            $table->bigInteger('old_points_defence')->length(25)->unsigned()->default(0)->after('points_defence');

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
