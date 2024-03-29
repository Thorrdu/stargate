<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_parts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->enum('type', ['Blueprint', 'Armament', 'Shield', 'Hull', 'Reactor']);
            $table->string('slug', 50);
            $table->longText('description')->nullable();
            $table->bigInteger('iron')->length(25)->unsigned()->nullable();
            $table->bigInteger('gold')->length(25)->unsigned()->nullable();
            $table->bigInteger('quartz')->length(25)->unsigned()->nullable();
            $table->bigInteger('naqahdah')->length(25)->unsigned()->nullable();
            $table->integer('capacity')->length(25)->unsigned()->default(0);
            $table->integer('fire_power')->lenght(15)->unsigned()->nullable();
            $table->integer('shield')->lenght(15)->unsigned()->nullable();
            $table->integer('hull')->lenght(15)->unsigned()->nullable();
            $table->integer('crew')->lenght(15)->unsigned()->nullable();
            $table->decimal('speed', 10, 5)->length(15)->unsigned()->nullable();
            $table->integer('used_capacity')->lenght(10)->unsigned()->nullable();
            $table->integer('base_time')->lenght(10)->unsigned()->nullable();
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
        Schema::dropIfExists('ship_parts');
    }
}
