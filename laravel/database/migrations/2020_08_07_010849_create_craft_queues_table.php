<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCraftQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('craft_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('colony_id')->unsigned();
            $table->foreign('colony_id')->references('id')->on('colonies');
            $table->integer('unit_id')->unsigned();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->timestamp('craft_end')->nullable();
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
        Schema::dropIfExists('craft_queues');
    }
}
