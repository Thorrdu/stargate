<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtifactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artifacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('colony_id')->unsigned();
            $table->foreign('colony_id')->references('id')->on('colonies');
            $table->enum('bonus_category', ['Production', 'Time', 'Price', 'DefenceLure', 'ColonyMax']);
            $table->enum('bonus_type', ['Research', 'Building', 'Ship', 'Defence', 'Craft'])->nullable();
            $table->enum('bonus_resource', ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'e2pz'])->nullable();
            $table->decimal('bonus_coef', 25, 5)->length(25)->unsigned()->nullable();
            $table->timestamp('bonus_end')->nullable();
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
        Schema::dropIfExists('artifacts');
    }
}
