<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['Military', 'Spy', 'Probe', 'Transport']); //Prépare le futur
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->longText('description');
            $table->integer('capacity')->length(18)->unsigned()->default(0);
            $table->boolean('buyable')->default(false);
            $table->bigInteger('iron')->length(18)->unsigned()->nullable();
            $table->bigInteger('gold')->length(18)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(18)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(18)->unsigned()->nullable();
            $table->integer('base_time')->lenght(5)->unsigned()->nullable();
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
        Schema::dropIfExists('units');
    }
}
