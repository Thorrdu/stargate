<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Hourly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('hr_combo')->length(3)->unsigned()->default(0)->after('last_daily');
            $table->integer('hr_max_combo')->length(3)->unsigned()->default(0)->after('hr_combo');
            $table->timestamp('last_hourly')->nullable()->after('hr_max_combo');
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
