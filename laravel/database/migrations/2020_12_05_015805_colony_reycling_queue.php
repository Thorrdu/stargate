<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColonyReyclingQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colony_reycling_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('colony_id')->unsigned();
            $table->foreign('colony_id')->references('id')->on('colonies');
            $table->integer('ship_id')->unsigned();
            $table->foreign('ship_id')->references('id')->on('ships');
            $table->timestamp('ship_end')->nullable();
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
        //
    }
}
