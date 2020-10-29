<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',150)->nullable();
            $table->string('slug', 150);
            $table->integer('player_id')->unsigned()->nullable();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('required_shipyard')->lenght(5)->unsigned()->default(1);
            $table->integer('required_blueprint')->lenght(5)->unsigned()->default(1);
            $table->bigInteger('iron')->length(25)->unsigned()->nullable();
            $table->bigInteger('gold')->length(25)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(25)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(25)->unsigned()->nullable();
            $table->bigInteger('capacity')->length(25)->unsigned()->nullable();
            $table->integer('crew')->lenght(15)->unsigned()->nullable();
            $table->integer('base_time')->lenght(10)->unsigned()->nullable();
            $table->integer('fire_power')->lenght(15)->unsigned()->nullable();
            $table->integer('shield')->lenght(15)->unsigned()->nullable();
            $table->integer('hull')->lenght(15)->unsigned()->nullable();
            $table->decimal('speed', 10, 5)->length(15)->unsigned()->nullable();

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
        Schema::dropIfExists('ships');
    }
}
