<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFleetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fleets', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('player_source_id')->unsigned()->nullable();
            $table->foreign('player_source_id','p_source_id')->references('id')->on('players');
            $table->integer('colony_source_id')->unsigned()->nullable();
            $table->foreign('colony_source_id','c_source_id')->references('id')->on('colonies');

            $table->integer('player_destination_id')->unsigned()->nullable();
            $table->foreign('player_destination_id','p_dest_id')->references('id')->on('players');
            $table->integer('colony_destination_id')->unsigned()->nullable();
            $table->foreign('colony_destination_id','c_dest_id')->references('id')->on('colonies');

            $table->enum('mission', ['base', 'transport', 'spy', 'colonize', 'attack','scavenge'])->nullable();
            $table->timestamp('departure_date')->nullable();
            $table->timestamp('arrival_date')->nullable();
            $table->boolean('returning')->default(false);
            $table->boolean('ended')->default(false);
            $table->boolean('boosted')->default(false);

            $table->bigInteger('crew')->lenght(25)->unsigned()->nullable();
            $table->bigInteger('capacity')->lenght(25)->unsigned()->nullable();
            $table->bigInteger('iron')->length(25)->unsigned()->nullable();
            $table->bigInteger('gold')->length(25)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(25)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(25)->unsigned()->nullable();
            $table->bigInteger('military')->length(25)->unsigned()->nullable();
            $table->decimal('E2PZ', 25, 2)->length(25)->unsigned()->nullable();

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
        Schema::dropIfExists('fleets');
    }
}
