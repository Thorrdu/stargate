<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TradeBan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->boolean('warned')->default(false)->after('trade_value_player2');
            $table->boolean('extended')->default(false)->after('warned');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->boolean('trade_ban')->default(false)->after('notification');
            $table->timestamp('trade_extend')->nullable()->after('trade_ban');
            $table->bigInteger('points_craft')->length(25)->unsigned()->default(0)->after('old_points_research');
            $table->bigInteger('old_points_craft')->length(25)->unsigned()->default(0)->after('points_craft');
        });

        Schema::table('alliances', function (Blueprint $table) {
            $table->bigInteger('points_craft')->length(25)->unsigned()->default(0)->after('old_points_research');
            $table->bigInteger('old_points_craft')->length(25)->unsigned()->default(0)->after('points_craft');
        });

        Schema::table('gate_fights', function (Blueprint $table) {
            $table->enum('type', ['Fleet', 'Gate'])->after('id')->default('Gate');
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
