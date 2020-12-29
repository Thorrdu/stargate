<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('defences')->insert([
            'id' => 1,
            'name' => "Tourelle mitrailleuse",
            'slug' => 'gatlingturret',
            'type' => 'Ground',
            'iron' => 500,
            'gold' => 0,
            'quartz' => 0,
            'naqahdah' => 0,
            'base_time' => 360,
            'fire_power' => 4,
            'hull' => 3,
        ]);
        //Centre de defense 1
        DB::table('defence_buildings')->insert([
            'defence_id' => 1,
            'required_building_id' => 15,
            'level' => 1
        ]);


        DB::table('defences')->insert([
            'id' => 2,
            'name' => "Tourelle laser",
            'slug' => 'laserturret',
            'type' => 'Ground',
            'iron' => 3000,
            'gold' => 1500,
            'quartz' => 0,
            'naqahdah' => 0,
            'base_time' => 1200,
            'fire_power' => 60,
            'hull' => 40,
        ]);
        //Centre de defense 6
        DB::table('defence_buildings')->insert([
            'defence_id' => 2,
            'required_building_id' => 15,
            'level' => 4
        ]);
        //Laser 3
        DB::table('defence_technologies')->insert([
            'defence_id' => 2,
            'required_technology_id' => 11,
            'level' => 3
        ]);



        DB::table('defences')->insert([
            'id' => 3,
            'name' => "Tourelle ECM",
            'slug' => 'ecmturret',
            'type' => 'Ground',
            'iron' => 5000,
            'gold' => 3500,
            'quartz' => 3000,
            'naqahdah' => 0,
            'base_time' => 3200,
            'fire_power' => 200,
            'hull' => 150,
        ]);
        //Centre de defense 6
        DB::table('defence_buildings')->insert([
            'defence_id' => 3,
            'required_building_id' => 15,
            'level' => 7
        ]);
        //Bouclier 5
        DB::table('defence_technologies')->insert([
            'defence_id' => 3,
            'required_technology_id' => 9,
            'level' => 5
        ]);
        //Energie 6
        DB::table('defence_technologies')->insert([
            'defence_id' => 3,
            'required_technology_id' => 4,
            'level' => 6
        ]);
        //Laser 6
        DB::table('defence_technologies')->insert([
            'defence_id' => 3,
            'required_technology_id' => 11,
            'level' => 6
        ]);




        DB::table('defences')->insert([
            'id' => 4,
            'name' => "Ion Satelite",
            'slug' => 'ionsatellite',
            'type' => 'Space',
            'iron' => 40000,
            'gold' => 35000,
            'quartz' => 10000,
            'naqahdah' => 0,
            'base_time' => 9000,
            'fire_power' => 1450,
            'hull' => 1100,
        ]);
        //Centre de defense 9
        DB::table('defence_buildings')->insert([
            'defence_id' => 4,
            'required_building_id' => 15,
            'level' => 9
        ]);
        //Ion 5
        DB::table('defence_technologies')->insert([
            'defence_id' => 4,
            'required_technology_id' => 12,
            'level' => 5
        ]);



        DB::table('defences')->insert([
            'id' => 5,
            'name' => "Plasma turret",
            'slug' => 'plasmaturret',
            'type' => 'Ground',
            'iron' => 200000,
            'gold' => 50000,
            'quartz' => 100000,
            'naqahdah' => 75000,
            'base_time' => 12000,
            'fire_power' => 15000,
            'hull' => 5000,
        ]);
        //Centre de defense 12
        DB::table('defence_buildings')->insert([
            'defence_id' => 5,
            'required_building_id' => 15,
            'level' => 12
        ]);
        //Plasma 7
        DB::table('defence_technologies')->insert([
            'defence_id' => 5,
            'required_technology_id' => 13,
            'level' => 7
        ]);


        DB::table('defences')->insert([
            'id' => 6,
            'name' => "Canon ECM",
            'slug' => 'ecmcannon',
            'type' => 'Ground',
            'iron' => 400000,
            'gold' => 500000,
            'quartz' => 2000000,
            'naqahdah' => 100000,
            'base_time' => 15000,
            'fire_power' => 25000,
            'hull' => 10000,
        ]);
        //Centre de defense 12
        DB::table('defence_buildings')->insert([
            'defence_id' => 6,
            'required_building_id' => 15,
            'level' => 12
        ]);
        //Naqahdah 7
        DB::table('defence_technologies')->insert([
            'defence_id' => 6,
            'required_technology_id' => 14,
            'level' => 7
        ]);

        DB::table('defences')->insert([
            'id' => 7,
            'name' => "Laser cannon",
            'slug' => 'lasercannon',
            'type' => 'Ground',
            'iron' => 5000000,
            'gold' => 5000000,
            'quartz' => 500000,
            'naqahdah' => 500000,
            'base_time' => 50000,
            'fire_power' => 285000,
            'hull' => 35000,
        ]);
        //Centre de defense 12
        DB::table('defence_buildings')->insert([
            'defence_id' => 7,
            'required_building_id' => 15,
            'level' => 14
        ]);
        //Laser 13
        DB::table('defence_technologies')->insert([
            'defence_id' => 7,
            'required_technology_id' => 11,
            'level' => 13
        ]);
        //IA 8
        DB::table('defence_technologies')->insert([
            'defence_id' => 7,
            'required_technology_id' => 5,
            'level' => 8
        ]);
        //Energie 12
        DB::table('defence_technologies')->insert([
            'defence_id' => 7,
            'required_technology_id' => 4,
            'level' => 12
        ]);


        DB::table('defences')->insert([
            'id' => 8,
            'name' => "Satellite Lantien",
            'slug' => 'lantean-satellite',
            'type' => 'Space',
            'iron' => 10000000,
            'gold' => 5000000,
            'quartz' => 2000000,
            'naqahdah' => 450000,
            'base_time' => 75000,
            'fire_power' => 750000,
            'hull' => 100000,
        ]);
        //Centre de defense 16
        DB::table('defence_buildings')->insert([
            'defence_id' => 8,
            'required_building_id' => 15,
            'level' => 16
        ]);
        //IA 10
        DB::table('defence_technologies')->insert([
            'defence_id' => 8,
            'required_technology_id' => 5,
            'level' => 10
        ]);
        //Energie 16
        DB::table('defence_technologies')->insert([
            'defence_id' => 8,
            'required_technology_id' => 4,
            'level' => 16
        ]);
        //Bouclier 16
        DB::table('defence_technologies')->insert([
            'defence_id' => 8,
            'required_technology_id' => 9,
            'level' => 16
        ]);

    }
}
