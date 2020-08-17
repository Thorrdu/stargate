<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->bigInteger('iron')->length(18)->unsigned()->nullable();
            $table->bigInteger('gold')->length(18)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(18)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(18)->unsigned()->nullable();
            $table->integer('base_time')->lenght(5)->unsigned()->nullable();
            $table->integer('fire_power')->lenght(15)->unsigned()->nullable();
            $table->integer('hull')->lenght(15)->unsigned()->nullable();
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
        Schema::dropIfExists('defences');
    }
}
