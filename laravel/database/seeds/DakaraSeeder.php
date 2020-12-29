<?php

use App\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DakaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Effet: Détruit 10% des défenses et 1h de production de militaire par niveau de différence avec la super-arme adverse.
        DB::table('buildings')->insert([
            'id' => 21,
            'name' => 'Super-arme de Dakara',
            'slug' => Str::slug('dakara-super-weapon'),
            'description' => "En utilisant le réseau des portes des étoiles comme catalyseur, la super-arme de Dakara envoi un puissant rayon destructeur, capable de réduire toute matière à ses éléments les plus basiques et ce, peu importe où dans l'univers."
                            ."\nAvec la bonne configuration, cette arme peut vous permettre de réduire les défenses adverses en poussière."
                            ."\nIl va de soit qu'un tel système requiert une quantité astronomique de puissance."
                            ."\n\nDistance d'action: 2 systèmes ^ niveau du bâtiment."
                            ."\nConsultez `!help dakara` pour plus d'informations sur les effets de la super-arme.",
            'type' => 'Military',
            'iron' => 20000,
            'gold' => 20000,
            'quartz' => 1000,
            'naqahdah' => 0,
            'display_order' => 0,
            'upgrade_coefficient' => 1.9,
            //'level_max' => 20,
            'production_type' => 'special',
            'time_base' => 86400
        ]);
        //Energie 3
        DB::table('building_technologies')->insert([
            'building_id' => 21,
            'required_technology_id' => 4,
            'level' => 3
        ]);
        //Armement 4
        DB::table('building_technologies')->insert([
            'building_id' => 21,
            'required_technology_id' => 7,
            'level' => 4
        ]);
        //Vitesse subluminique 3
        DB::table('building_technologies')->insert([
            'building_id' => 21,
            'required_technology_id' => 15,
            'level' => 3
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
