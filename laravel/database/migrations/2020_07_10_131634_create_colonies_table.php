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
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id','c_player_id')->references('id')->on('players');

            $table->string('name', 50);
            $table->string('coordinates', 25)->nullable();

            $table->decimal('iron', 15, 4)->unsigned()->default(600);
            $table->decimal('gold', 15, 4)->length(18)->unsigned()->default(400);
            $table->decimal('quartz', 15, 4)->length(18)->unsigned()->default(0);
            $table->decimal('naqahdah', 15, 4)->length(18)->unsigned()->default(0);

            $table->integer('storage_iron')->length(18)->unsigned()->default(100000);
            $table->integer('storage_gold')->length(18)->unsigned()->default(100000);
            $table->integer('storage_quartz')->length(18)->unsigned()->default(100000);
            $table->integer('storage_naqahdah')->length(18)->unsigned()->default(100000);

            /*
            Production et energie actuelle pour éviter recalcul permanent
            */
            $table->integer('production_iron')->length(10)->unsigned()->default(20);
            $table->integer('production_gold')->length(10)->unsigned()->default(10);
            $table->integer('production_quartz')->length(10)->unsigned()->default(5);
            $table->integer('production_naqahdah')->length(10)->unsigned()->default(2);
            $table->integer('production_military')->length(10)->unsigned()->default(2);
            $table->integer('production_e2pz')->length(10)->unsigned()->default(2);
            $table->integer('energy_used')->length(10)->default(0);
            $table->integer('energy_max')->length(10)->unsigned()->default(0);

            $table->integer('soldiers')->length(18)->unsigned()->default(100000);
            $table->decimal('E2PZ', 10, 4)->length(18)->default(0);

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
