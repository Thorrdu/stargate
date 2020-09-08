<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayerPremium extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('premium')->length(3)->unsigned()->default(0)->after('lang');
            $table->timestamp('premium_expiration')->nullable()->after('premium');
            $table->timestamp('vacation')->nullable()->after('premium');
            $table->timestamp('next_vacation')->nullable()->after('vacation');
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
