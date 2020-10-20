<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RuinField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coordinates', function (Blueprint $table) {
            $table->bigInteger('iron')->length(25)->unsigned()->default(0)->after('planet');
            $table->bigInteger('gold')->length(25)->unsigned()->default(0)->after('iron');
            $table->bigInteger('quartz')->length(25)->unsigned()->default(0)->after('gold');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->decimal('speed', 10, 5)->length(15)->unsigned()->nullable();
        });

        Schema::table('gate_fights', function (Blueprint $table) {
            $table->bigInteger('source_lost_value')->length(25)->unsigned()->default(0)->after('naqahdah');
            $table->bigInteger('destination_lost_value')->length(25)->unsigned()->default(0)->after('source_lost_value');
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
