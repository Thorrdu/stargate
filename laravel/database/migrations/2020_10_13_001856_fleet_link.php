<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FleetLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gate_fights', function (Blueprint $table) {
            $table->integer('fleet_id')->unsigned()->nullable()->after('type');
            $table->foreign('fleet_id')->references('id')->on('fleets');
            $table->longText('report_fr')->nullable()->after('fleet_id');
            $table->longText('report_en')->nullable()->after('report_fr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
