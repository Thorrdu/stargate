<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrimeColony extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('colonies', function (Blueprint $table) {
            $table->boolean('prime_colony')->default(false)->after('coordinate_id');
        });
        Schema::table('players', function (Blueprint $table) {
            $table->boolean('hide_coordinates')->default(false)->after('notification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('colonies', function (Blueprint $table) {
            //
        });
    }
}
