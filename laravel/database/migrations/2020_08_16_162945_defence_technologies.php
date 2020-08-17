<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefenceTechnologies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defence_technologies', function (Blueprint $table) {
            $table->increments('id');     
            $table->integer('defence_id')->unsigned();
            $table->foreign('defence_id')->references('id')->on('defences');
            $table->integer('required_technology_id')->unsigned();
            $table->foreign('required_technology_id')->references('id')->on('technologies');
            $table->integer('level')->length(3)->default(1);
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
