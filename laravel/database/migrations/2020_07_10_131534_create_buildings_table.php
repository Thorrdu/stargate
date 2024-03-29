<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('slug', 50);

            $table->longText('description');

            $table->enum('type', ['Energy', 'Production', 'Storage', 'Science', 'Military']);

            $table->bigInteger('iron')->length(25)->unsigned()->default(0);
            $table->bigInteger('gold')->length(25)->unsigned()->default(0);
            $table->bigInteger('quartz')->length(25)->unsigned()->default(0);
            $table->bigInteger('naqahdah')->length(25)->unsigned()->default(0);

            $table->enum('production_type', ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'space', 'e2pz',  'special']);
            $table->decimal('production_base', 7, 2)->nullable();
            $table->decimal('production_coefficient', 5, 2)->nullable();

            $table->integer('energy_base')->length(5)->nullable();
            $table->decimal('energy_coefficient', 5, 2)->nullable();

            $table->decimal('upgrade_coefficient', 5, 2);
            $table->integer('level_max')->nullable();
            
            $table->integer('time_base')->length(25)->default(1000);
            $table->decimal('time_coefficient', 5, 2)->default(1.45);

            $table->decimal('building_bonus', 5, 2)->nullable();
            $table->decimal('technology_bonus', 5, 2)->nullable();
            $table->decimal('crafting_bonus', 5, 2)->nullable();
            $table->decimal('defence_bonus', 5, 2)->nullable();
            $table->decimal('ship_bonus', 5, 2)->nullable();

            $table->integer('display_order')->length(3)->default(0);
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
        Schema::dropIfExists('buildings');
    }
}
