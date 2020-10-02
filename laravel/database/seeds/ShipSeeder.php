<?php

use App\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $players = Player::all();
        foreach($players as $player)
        {
            DB::table('ships')->insert([
                'name' => "F-302",
                'slug' => 'f302',
                'player_id' => $player->id,
                'required_shipyard' => 1,
                'required_blueprint' => 1,
                'iron' => 19300,
                'gold' => 18800,
                'quartz' => 1420,
                'naqahdah' => 5,
                'base_time' => 700,
                'capacity' => 630,
                'crew' => 2,
                'fire_power' => 150,
                'shield' => 800,
                'hull' => 1000,
                'speed' => 0.5,
            ]);

            DB::table('ships')->insert([
                'name' => "Tel'tak",
                'slug' => 'teltak',
                'player_id' => $player->id,
                'required_shipyard' => 4,
                'required_blueprint' => 5,
                'iron' => 36700,
                'gold' => 36200,
                'quartz' => 1600,
                'naqahdah' => 20,
                'base_time' => 1680,
                'crew' => 100,
                'capacity' => 16000,
                'fire_power' => 200,
                'shield' => 2200,
                'hull' => 3000,
                'speed' => 1,
            ]);

            DB::table('ships')->insert([
                'name' => "Al'kesh",
                'slug' => 'alkesh',
                'player_id' => $player->id,
                'required_shipyard' => 6,
                'required_blueprint' => 7,
                'iron' => 180900,
                'gold' => 180200,
                'quartz' => 8800,
                'naqahdah' => 50,
                'base_time' => 3300,
                'crew' => 500,
                'capacity' => 33180,
                'fire_power' => 2500,
                'shield' => 3000,
                'hull' => 4000,
                'speed' => 1.5,
            ]);

            DB::table('ships')->insert([
                'name' => "Prometheus",
                'slug' => 'prometheus',
                'player_id' => $player->id,
                'required_shipyard' => 8,
                'required_blueprint' => 9,
                'iron' => 1340000,
                'gold' => 1339000,
                'quartz' => 250000,
                'naqahdah' => 20000,
                'base_time' => 72000,
                'crew' => 1000,
                'capacity' => 63500,
                'fire_power' => 21000,
                'shield' => 40000,
                'hull' => 39000,
                'speed' => 3,
            ]);

        }
    }
}
