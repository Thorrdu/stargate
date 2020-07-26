<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColonyUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colony_units', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('colony_id')->unsigned();
            $table->foreign('colony_id')->references('id')->on('colonies');
            $table->integer('unit_id')->unsigned();
            $table->foreign('unit_id')->references('id')->on('technologies');
            $table->bigInteger('number')->length(18)->default(0);
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
