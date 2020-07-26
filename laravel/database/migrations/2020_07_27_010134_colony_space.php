<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColonySpace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('colonies', function (Blueprint $table) {
            $table->integer('space_used')->length(3)->default(0)->after('energy_max');
            $table->integer('space_max')->length(3)->unsigned()->default(0)->after('energy_used');
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
