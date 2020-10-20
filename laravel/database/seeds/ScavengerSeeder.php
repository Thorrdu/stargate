<?php

use App\Coordinate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Colony;

class ScavengerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('units')->insert([
            'id' => 5,
            'type' => 'Scavenger',
            'name' => "Mini Scavenger",
            'slug' => 'miniscavenger',
            'description' => "Recycle les champs de ruine.",
            'capacity' => 15000,
            'buyable' => true,
            'iron' => 5000,
            'gold' => 5000,
            'quartz' => 20000,
            'naqahdah' => 10000,
            'base_time' => 1300,
            'speed' => 2
        ]);
        //Chantier spatial 5
        DB::table('unit_buildings')->insert([
            'unit_id' => 5,
            'required_building_id' => 9,
            'level' => 4
        ]);
        //Impulsion
        DB::table('unit_technologies')->insert([
            'unit_id' => 5,
            'required_technology_id' => 15,
            'level' => 4
        ]);
        //Coque
        DB::table('unit_technologies')->insert([
            'unit_id' => 5,
            'required_technology_id' => 8,
            'level' => 5
        ]);

        DB::table('units')->insert([
            'id' => 6,
            'type' => 'Scavenger',
            'name' => "Mini Scavenger",
            'slug' => 'scavenger',
            'description' => "Recycle les champs de ruine.",
            'capacity' => 50000,
            'buyable' => true,
            'iron' => 10000,
            'gold' => 10000,
            'quartz' => 20000,
            'naqahdah' => 30000,
            'base_time' => 2600,
            'speed' => 4
        ]);
        //Chantier spatial 5
        DB::table('unit_buildings')->insert([
            'unit_id' => 6,
            'required_building_id' => 9,
            'level' => 7
        ]);
        //Antimatière
        DB::table('unit_technologies')->insert([
            'unit_id' => 6,
            'required_technology_id' => 16,
            'level' => 4
        ]);
        //Bouclier
        DB::table('unit_technologies')->insert([
            'unit_id' => 6,
            'required_technology_id' => 9,
            'level' => 7
        ]);
        //nanotech
        DB::table('unit_technologies')->insert([
            'unit_id' => 6,
            'required_technology_id' => 5,
            'level' => 6
        ]);

        DB::table('units')->insert([
            'id' => 7,
            'type' => 'Scavenger',
            'name' => "Grand Scavenger",
            'slug' => 'bigscavenger',
            'description' => "Recycle les champs de ruine.",
            'capacity' => 100000,
            'buyable' => true,
            'iron' => 15000,
            'gold' => 15000,
            'quartz' => 20000,
            'naqahdah' => 55000,
            'base_time' => 5200,
            'speed' => 5
        ]);
        //Chantier spatial 5
        DB::table('unit_buildings')->insert([
            'unit_id' => 7,
            'required_building_id' => 9,
            'level' => 15
        ]);
        //Hyper espace
        DB::table('unit_technologies')->insert([
            'unit_id' => 7,
            'required_technology_id' => 17,
            'level' => 7
        ]);
        //Bouclier
        DB::table('unit_technologies')->insert([
            'unit_id' => 7,
            'required_technology_id' => 9,
            'level' => 15
        ]);
        //nanotech
        DB::table('unit_technologies')->insert([
            'unit_id' => 7,
            'required_technology_id' => 5,
            'level' => 11
        ]);

        DB::table('units')->insert([
            'id' => 8,
            'type' => 'Scavenger',
            'name' => "Advanced Scavenger",
            'slug' => 'advancedscavenger',
            'description' => "Recycle les champs de ruine.",
            'capacity' => 500000,
            'buyable' => true,
            'iron' => 30000,
            'gold' => 30000,
            'quartz' => 40000,
            'naqahdah' => 110000,
            'base_time' => 15000,
            'speed' => 5
        ]);
        //Chantier spatial 5
        DB::table('unit_buildings')->insert([
            'unit_id' => 8,
            'required_building_id' => 9,
            'level' => 18
        ]);
        //Hyper espace
        DB::table('unit_technologies')->insert([
            'unit_id' => 8,
            'required_technology_id' => 17,
            'level' => 9
        ]);
        //Bouclier
        DB::table('unit_technologies')->insert([
            'unit_id' => 8,
            'required_technology_id' => 9,
            'level' => 17
        ]);
        //nanotech
        DB::table('unit_technologies')->insert([
            'unit_id' => 8,
            'required_technology_id' => 5,
            'level' => 13
        ]);



        DB::table('defences')->insert([
            'id' => 6,
            'name' => "Canon ECM",
            'slug' => 'ecmcannon',
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
            'defence_id' => 5,
            'required_technology_id' => 11,
            'level' => 13
        ]);
        //IA 8
        DB::table('defence_technologies')->insert([
            'defence_id' => 5,
            'required_technology_id' => 5,
            'level' => 8
        ]);
        //Energie 12
        DB::table('defence_technologies')->insert([
            'defence_id' => 5,
            'required_technology_id' => 4,
            'level' => 12
        ]);
    }
}
