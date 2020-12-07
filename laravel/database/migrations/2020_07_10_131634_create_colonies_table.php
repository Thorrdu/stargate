<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColoniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colonies', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('colony_type')->length(2);
            $table->integer('player_id')->unsigned()->nullable();
            $table->foreign('player_id','c_player_id')->references('id')->on('players');
            $table->integer('coordinate_id')->unsigned();
            $table->foreign('coordinate_id')->references('id')->on('coordinates');

            $table->string('name', 50);

            $table->decimal('iron', 20, 5)->unsigned()->default(600);
            $table->decimal('gold', 20, 5)->length(25)->unsigned()->default(400);
            $table->decimal('quartz', 20, 5)->length(25)->unsigned()->default(0);
            $table->decimal('naqahdah', 20, 5)->length(25)->unsigned()->default(0);

            $table->bigInteger('storage_iron')->length(25)->unsigned()->default(100000);
            $table->bigInteger('storage_gold')->length(25)->unsigned()->default(100000);
            $table->bigInteger('storage_quartz')->length(25)->unsigned()->default(100000);
            $table->bigInteger('storage_naqahdah')->length(25)->unsigned()->default(100000);

            /*
            Production et energie actuelle pour éviter recalcul permanent
            */
            $table->bigInteger('production_iron')->length(25)->unsigned()->default(20);
            $table->bigInteger('production_gold')->length(25)->unsigned()->default(10);
            $table->bigInteger('production_quartz')->length(25)->unsigned()->default(5);
            $table->bigInteger('production_naqahdah')->length(25)->unsigned()->default(2);
            $table->bigInteger('production_military')->length(25)->unsigned()->default(0);
            $table->bigInteger('production_e2pz')->length(25)->unsigned()->default(0);

            $table->bigInteger('consumption_naqahdah')->length(25)->unsigned()->nullable();

            $table->bigInteger('energy_used')->length(25)->default(0);
            $table->bigInteger('energy_max')->length(25)->unsigned()->default(0);

            $table->decimal('military', 25, 5)->length(25)->unsigned()->default(0);
            $table->decimal('E2PZ', 25, 5)->length(25)->default(0);

            $table->integer('active_building_id')->unsigned()->nullable();
            $table->foreign('active_building_id','c_active_building_id')->references('id')->on('buildings');
            $table->timestamp('active_building_end')->nullable();

            $table->timestamp('last_claim')->nullable();

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
        Schema::dropIfExists('colonies');
    }
}
