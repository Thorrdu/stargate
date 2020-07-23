<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Players extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Ajoute liaison de la technologie active au joueur
        Schema::table('players', function (Blueprint $table) {
            $table->integer('active_technology_id')->unsigned()->nullable()->after('votes');
            $table->foreign('active_technology_id','p_active_technology_id')->references('id')->on('technologies');
            $table->timestamp('active_technology_end')->nullable()->after('active_technology_id');
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
