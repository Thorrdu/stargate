<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trade_id')->unsigned()->nullable();
            $table->foreign('trade_id')->references('id')->on('trades');
            $table->enum('player',[1,2]);
            $table->integer('unit_id')->unsigned()->nullable();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->enum('resource', ['iron', 'gold', 'quartz ', 'naqahdah', 'E2PZ', 'military','premium'])->nullable();
            $table->decimal('quantity', 25, 2)->length(25)->unsigned();
            $table->integer('trade_value')->length(25)->unsigned();
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
        Schema::dropIfExists('traded_ressources');
    }
}
