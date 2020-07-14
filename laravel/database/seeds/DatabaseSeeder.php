<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
/*
        Buildings
        */
        DB::table('buildings')->insert([
            'id' => 1,
            'name' => 'Centrale Thermique',
            'description' => 'Reacteur permettant d\'exploiter l\'énergie thermique souteraine présente sur votre colonie',
            'type' => 'Energy',
            'iron' => 52,
            'gold' => 30,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'special',
            'production_base' => 100,
            'production_coefficient' => 1.2,
            'energy_base' => NULL,
            'energy_coefficient' => NULL,
            'display_order' => 0,
            'upgrade_coefficient' => 1.6,
            'level_max' => 20,
            'time_base' => 146
        ]);


        //https://forum.origins-return.fr/index.php?/topic/243312-les-batiments/

        DB::table('buildings')->insert([
            'id' => 2,
            'name' => 'Mine de fer',
            'description' => 'Mine rudimentaire permettant d\'extraire du minerais de fer',
            'type' => 'Production',
            'iron' => 60,
            'gold' => 15,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'iron',
            'production_base' => 100,
            'production_coefficient' => 1.18,
            'energy_base' => 30,
            'energy_coefficient' => 1.18,
            'display_order' => 0,
            'upgrade_coefficient' => 1.45,
            'level_max' => 20,
            'time_base' => 162
        ]);

        DB::table('buildings')->insert([
            'id' => 3,
            'name' => 'Mine d\'or',
            'description' => 'Mine rudimentaire permettant d\'extraire de l\'or',
            'type' => 'Production',
            'iron' => 50,
            'gold' => 25,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'gold',
            'production_base' => 90,
            'production_coefficient' => 1.15,
            'energy_base' => 26,
            'energy_coefficient' => 1.18,
            'display_order' => 1,
            'upgrade_coefficient' => 1.45,
            'level_max' => 20,
            'time_base' => 155
        ]);

        DB::table('buildings')->insert([
            'id' => 4,
            'name' => 'Mine de quartz',
            'description' => 'Mine rudimentaire permettant d\'extraire de quartz',
            'type' => 'Production',
            'iron' => 500,
            'gold' => 160,
            'quartz' => 0,
            'naqahdah' => 0,
            'production_type' => 'quartz',
            'production_base' => 149,
            'production_coefficient' => 1.22,
            'energy_base' => 40,
            'energy_coefficient' => 1.18,
            'display_order' => 2,
            'upgrade_coefficient' => 1.45,
            'time_base' => 770
        ]);

        DB::table('buildings')->insert([
            'id' => 5,
            'name' => 'Extracteur de naqahdah',
            'description' => 'Extracteur de naqahdah',
            'type' => 'Production',
            'iron' => 500,
            'gold' => 300,
            'quartz' => 100,
            'naqahdah' => 0,
            'production_type' => 'naqahdah',
            'production_base' => 122,
            'production_coefficient' => 1.22,
            'energy_base' => 68,
            'energy_coefficient' => 1.18,
            'display_order' => 3,
            'upgrade_coefficient' => 1.4,
            'time_base' => 840
        ]);

        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 125641223544373248,
            'user_name' => "Thorrdu"
        ]);

        DB::table('colonies')->insert([
            'id' => 1,
            'player_id' => 1,
            'colony_type' => 1,
            'name' => 'P'.rand(1, 9).Str::random(1).'-'.rand(1, 9).rand(1, 9).rand(1, 9),
            'last_claim' => '2020-07-14 00:00:00'
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 1,
            'level' => 1
        ]);

        DB::table('building_colony')->insert([
            'colony_id' => 1,
            'building_id' => 2,
            'level' => 1
        ]);
    }
}
