<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StargateBury extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('colonies', function (Blueprint $table) {
            $table->boolean('stargate_buried')->default(false)->after('last_claim');
            $table->boolean('stargate_burying')->default(false)->after('stargate_buried');
            $table->timestamp('stargate_action_date')->nullable()->after('stargate_burying');
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
