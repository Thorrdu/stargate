<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllianceRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /*
        Exemples
        1 -Recrue (sans droit)
        2 -Recruteur (peut recruter)
        3 -Officier (peut recruter/kick)
        4 -Co-Leader/Membre du conseil (Administre les membres de niveau inférieur à lui)
        5 -Leader (Administre les membres de niveaux inférieurs à lui) 
        */

        Schema::create('alliance_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->integer('right_level')->lenght(3)->unsigned()->default(1);
            $table->boolean('right_recruit')->default(false);
            $table->boolean('right_kick')->default(false);
            $table->boolean('right_promote')->default(false);
            $table->integer('alliance_id')->unsigned()->nullable();
            $table->foreign('alliance_id')->references('id')->on('alliances');
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
        Schema::dropIfExists('alliance_roles');
    }
}
